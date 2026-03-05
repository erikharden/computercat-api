<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Feature toggles --}}
        <x-filament::section>
            <x-slot name="heading">Features</x-slot>
            <x-slot name="description">Select which features to include in the generated manual.</x-slot>
            {{ $this->form }}
        </x-filament::section>

        {{-- Setup Guide --}}
        <x-filament::section>
            <x-slot name="heading">Setup Guide</x-slot>
            <x-slot name="description">Overview of {{ $record->name }}'s current configuration.</x-slot>
            <div class="prose prose-sm dark:prose-invert max-w-none cc-markdown">
                {!! \Illuminate\Support\Str::markdown($this->getSetupGuide()) !!}
            </div>
        </x-filament::section>

        {{-- AI Agent Manual --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between w-full">
                    <span>AI Agent Manual</span>
                    <button
                        type="button"
                        onclick="copyManual()"
                        class="cc-copy-btn"
                    >
                        <span id="copy-label">Copy Manual</span>
                    </button>
                </div>
            </x-slot>
            <x-slot name="description">Give this to your AI coding assistant. It contains everything needed to integrate with the API.</x-slot>

            {{-- Rendered preview --}}
            <div class="prose prose-sm dark:prose-invert max-w-none cc-markdown">
                {!! \Illuminate\Support\Str::markdown($this->getManual()) !!}
            </div>

            {{-- Hidden raw markdown for copying --}}
            <textarea id="manual-raw" class="sr-only" aria-hidden="true">{{ $this->getManual() }}</textarea>
        </x-filament::section>
    </div>

    <style>
        .cc-copy-btn {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            padding: 0.4rem 1rem;
            background: transparent;
            border: 1px solid var(--cc-primary, #00f0ff);
            color: var(--cc-primary, #00f0ff);
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.5px;
        }
        .cc-copy-btn:hover {
            background: var(--cc-primary, #00f0ff);
            color: var(--cc-bg, #0a0a1a);
            box-shadow: 0 0 10px rgba(0,240,255,0.3);
        }
        .cc-copy-btn.copied {
            border-color: var(--cc-tertiary, #39ff14);
            color: var(--cc-tertiary, #39ff14);
        }
        .cc-markdown code {
            font-family: 'JetBrains Mono', monospace !important;
            font-size: 0.8rem !important;
            background: rgba(0,240,255,0.06) !important;
            border: 1px solid rgba(0,240,255,0.1) !important;
            padding: 0.1rem 0.3rem !important;
            border-radius: 0 !important;
        }
        .cc-markdown pre {
            background: rgba(0,240,255,0.04) !important;
            border: 1px solid rgba(0,240,255,0.1) !important;
            border-radius: 0 !important;
        }
        .cc-markdown pre code {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
        }
        .cc-markdown table {
            font-size: 0.8rem;
        }
        .cc-markdown th {
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            color: var(--cc-muted, #7878a0);
        }
    </style>

    <script>
        function copyManual() {
            const raw = document.getElementById('manual-raw').value;
            navigator.clipboard.writeText(raw).then(() => {
                const btn = document.querySelector('.cc-copy-btn');
                const label = document.getElementById('copy-label');
                btn.classList.add('copied');
                label.textContent = 'Copied!';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    label.textContent = 'Copy Manual';
                }, 2000);
            });
        }
    </script>
</x-filament-panels::page>
