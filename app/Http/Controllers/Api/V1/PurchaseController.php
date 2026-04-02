<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\ReceiptVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        private ReceiptVerificationService $receiptService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $purchases = Purchase::where('user_id', $request->user()->id)
            ->orderByDesc('purchased_at')
            ->get();

        return response()->json([
            'data' => $purchases->map(fn ($p) => [
                'product_id' => $p->product_id,
                'store' => $p->store,
                'status' => $p->status,
                'purchased_at' => $p->purchased_at,
            ]),
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'product_id' => 'required|string|max:100',
            'store' => 'required|in:apple,google,web',
            'transaction_id' => 'required|string|max:255|unique:purchases,transaction_id',
            'receipt_data' => 'nullable|string',
        ]);

        // Verify receipt with store
        $result = $this->receiptService->verify(
            $validated['store'],
            $validated['product_id'],
            $validated['transaction_id'],
            $validated['receipt_data'] ?? null,
        );

        $status = $result['verified'] ? 'verified' : 'pending';

        // For web purchases (no receipt), mark as pending for manual review
        if ($validated['store'] === 'web') {
            $status = 'pending';
        }

        $purchase = Purchase::create([
            'user_id' => $request->user()->id,
            'game_id' => $validated['game_id'],
            'product_id' => $validated['product_id'],
            'store' => $validated['store'],
            'transaction_id' => $validated['transaction_id'],
            'receipt_data' => $validated['receipt_data'] ?? null,
            'status' => $status,
            'purchased_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'product_id' => $purchase->product_id,
                'status' => $purchase->status,
                'purchased_at' => $purchase->purchased_at,
            ],
        ], 201);
    }
}
