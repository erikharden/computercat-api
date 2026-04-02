<?php

namespace App\Services;

use App\Models\LeaderboardType;

class AntiCheatService
{
    /**
     * Minimum plausible solve times (ms) per grid size for Tocco.
     * A human physically cannot tap cells faster than this.
     */
    private const MIN_SOLVE_TIME_MS = [
        6 => 8_000,
        8 => 15_000,
        10 => 30_000,
        12 => 60_000,
        14 => 90_000,
    ];

    /**
     * Minimum moves required per grid size (roughly gridSize² / 4).
     * A valid solution requires filling at least this many empty cells.
     */
    private const MIN_MOVES = [
        6 => 9,
        8 => 16,
        10 => 25,
        12 => 36,
        14 => 49,
    ];

    public function check(LeaderboardType $type, int $score, ?array $metadata): array
    {
        // Basic plausibility checks
        if ($score < 0) {
            return ['passed' => false, 'reason' => 'Score cannot be negative.'];
        }

        // For time-based leaderboards (asc sort = lower is better)
        if ($type->sort_direction === 'asc') {
            // Get game-specific anti-cheat config from game settings
            $game = $type->game;
            $config = $game?->settings['anti_cheat'] ?? [];
            $scoreUnit = $config['score_unit'] ?? 'raw'; // 'ms', 'seconds', or 'raw'

            // Validate proof-of-play if provided
            $proof = $metadata['proof'] ?? null;
            if ($proof && $scoreUnit === 'ms') {
                $result = $this->validateProof($score, $proof);
                if (! $result['passed']) {
                    return $result;
                }
            }

            // Unit-aware bounds
            $minScore = $config['min_score'] ?? 5;
            $maxScore = $config['max_score'] ?? 86400;

            if ($score < $minScore) {
                return ['passed' => false, 'reason' => 'Score is implausibly low.'];
            }

            if ($score > $maxScore) {
                return ['passed' => false, 'reason' => 'Score exceeds maximum allowed value.'];
            }
        }

        return ['passed' => true, 'reason' => null];
    }

    /**
     * Validate proof-of-play submitted by the client.
     *
     * The proof contains:
     * - pid: puzzle ID
     * - mc: move count
     * - gh: grid hash (compact representation of solved grid)
     * - t: elapsed time in ms
     * - sig: signature over the above fields
     */
    private function validateProof(int $score, array $proof): array
    {
        $pid = $proof['pid'] ?? null;
        $mc = $proof['mc'] ?? null;
        $gh = $proof['gh'] ?? null;
        $t = $proof['t'] ?? null;
        $sig = $proof['sig'] ?? null;

        if (! $pid || ! is_int($mc) || ! $gh || ! is_int($t) || ! $sig) {
            return ['passed' => false, 'reason' => 'Invalid proof format.'];
        }

        // Verify signature matches the proof fields (same algorithm as client)
        $expectedSig = $this->computeProofSignature($pid, $mc, $gh, $t);
        if ($sig !== $expectedSig) {
            return ['passed' => false, 'reason' => 'Proof signature mismatch.'];
        }

        // Time in proof must match submitted score
        if (abs($t - $score) > 1000) {
            return ['passed' => false, 'reason' => 'Proof time does not match score.'];
        }

        // Determine grid size from puzzle ID (format: "daily/YYYY-MM-DD" or "packId/puzzleId")
        $gridSize = $this->inferGridSize($pid);

        if ($gridSize) {
            // Check minimum solve time for this grid size
            $minTime = self::MIN_SOLVE_TIME_MS[$gridSize] ?? 5_000;
            if ($t < $minTime) {
                return ['passed' => false, 'reason' => 'Solve time too fast for grid size.'];
            }

            // Check minimum move count
            $minMoves = self::MIN_MOVES[$gridSize] ?? 9;
            if ($mc < $minMoves) {
                return ['passed' => false, 'reason' => 'Too few moves for grid size.'];
            }
        }

        return ['passed' => true, 'reason' => null];
    }

    /**
     * Reproduce the client's FNV-1a-based proof signature.
     */
    private function computeProofSignature(string $pid, int $mc, string $gh, int $t): string
    {
        $payload = "{$pid}|{$mc}|{$gh}|{$t}";
        $h = 0x811c9dc5; // FNV offset basis (32-bit)

        for ($i = 0; $i < strlen($payload); $i++) {
            $h ^= ord($payload[$i]);
            // Math.imul equivalent for 32-bit: multiply then mask to 32 bits
            $h = $this->imul32($h, 0x01000193);
        }

        // Convert to unsigned 32-bit then base-36
        $h = $h & 0xFFFFFFFF;
        if ($h < 0) {
            $h += 4294967296;
        }

        return base_convert((string) $h, 10, 36);
    }

    /**
     * Emulate JavaScript's Math.imul (32-bit integer multiplication).
     */
    private function imul32(int $a, int $b): int
    {
        // Use GMP or manual 32-bit multiplication
        $result = ($a * $b) & 0xFFFFFFFF;

        // PHP handles large ints differently — convert to signed 32-bit
        if ($result >= 0x80000000) {
            $result -= 0x100000000;
        }

        return $result;
    }

    /**
     * Infer grid size from puzzle ID.
     * Daily puzzles use game settings, pack puzzles encode size in pack ID.
     */
    private function inferGridSize(string $pid): ?int
    {
        // Pack puzzle: "6x6-easy/puzzle_001" → 6
        if (preg_match('/^(\d+)x\d+/', $pid, $m)) {
            $size = (int) $m[1];
            if (in_array($size, [6, 8, 10, 12, 14])) {
                return $size;
            }
        }

        // Daily puzzles default to 6x6 (Tocco's daily format)
        if (str_starts_with($pid, 'daily/')) {
            return 6;
        }

        return null;
    }
}
