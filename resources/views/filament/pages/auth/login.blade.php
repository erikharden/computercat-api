<div>
    <div class="cc-login-wrapper">
        {{-- Scanline overlay --}}
        <div class="cc-scanlines"></div>

        {{-- Floating particles --}}
        <div class="cc-particles">
            @for ($i = 0; $i < 20; $i++)
                <div class="cc-particle" style="
                    left: {{ rand(0, 100) }}%;
                    animation-delay: {{ rand(0, 50) / 10 }}s;
                    animation-duration: {{ rand(30, 60) / 10 }}s;
                "></div>
            @endfor
        </div>

        <div class="cc-login-card">
            {{-- Cat walking on top edge --}}
            <div class="cc-cat-track">
                <div class="cc-walking-cat" id="walking-cat">
                    <div class="cc-cat-body"></div>
                    <div class="cc-cat-legs"></div>
                    <div class="cc-cat-blink"></div>
                </div>
            </div>

            {{-- Top border glow --}}
            <div class="cc-card-border-top"></div>

            {{-- Header --}}
            <div class="cc-login-header">
                <div class="cc-logo-text">COMPUTER<span class="cc-logo-accent">CAT</span></div>
                <div class="cc-subtitle">// admin_panel</div>
            </div>

            {{-- Filament login form --}}
            <div class="cc-form-area">
                <x-filament-panels::form wire:submit="authenticate">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>
            </div>

            {{-- Footer --}}
            <div class="cc-login-footer">
                <span class="cc-blink-cursor">&gt;</span> ready_
            </div>
        </div>
    </div>

    <style>
        /* -- Reset Filament defaults -- */
        .fi-simple-layout {
            background: transparent !important;
        }
        .fi-simple-main-ctn {
            max-width: none !important;
            padding: 0 !important;
        }
        .fi-simple-main {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: none !important;
        }
        body.fi-body {
            background: #0a0a1a !important;
            min-height: 100vh;
        }
        .fi-simple-header { display: none !important; }

        /* -- Fonts -- */
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Press+Start+2P&display=swap');

        /* -- Variables -- */
        .cc-login-wrapper {
            --cc-bg: #0a0a1a;
            --cc-surface: #12122a;
            --cc-primary: #00f0ff;
            --cc-secondary: #ff00aa;
            --cc-tertiary: #39ff14;
            --cc-text: #e0e0ff;
            --cc-muted: #7878a0;
            --cc-dark: #0a0a1a;

            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'JetBrains Mono', monospace;
            overflow: hidden;
        }

        /* -- Scanlines -- */
        .cc-scanlines {
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0, 240, 255, 0.015) 2px,
                rgba(0, 240, 255, 0.015) 4px
            );
            pointer-events: none;
            z-index: 100;
        }

        /* -- Particles -- */
        .cc-particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .cc-particle {
            position: absolute;
            bottom: -4px;
            width: 2px;
            height: 2px;
            background: var(--cc-primary);
            opacity: 0.3;
            animation: particleFloat linear infinite;
        }
        @keyframes particleFloat {
            0% { transform: translateY(0); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-100vh); opacity: 0; }
        }

        /* -- Card -- */
        .cc-login-card {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: var(--cc-surface);
            border: 1px solid rgba(0, 240, 255, 0.2);
            padding: 2.5rem 2rem 1.5rem;
            z-index: 10;
            box-shadow:
                0 0 60px rgba(0, 240, 255, 0.06) inset,
                0 0 4px rgba(0, 0, 0, 0.8) inset,
                0 0 15px rgba(0, 240, 255, 0.08);
        }

        .cc-card-border-top {
            position: absolute;
            top: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg,
                transparent,
                var(--cc-primary) 20%,
                var(--cc-secondary) 50%,
                var(--cc-primary) 80%,
                transparent
            );
            box-shadow: 0 0 10px var(--cc-primary), 0 0 30px rgba(0, 240, 255, 0.3);
        }

        /* -- Cat walking track -- */
        .cc-cat-track {
            position: absolute;
            top: -48px;
            left: 0;
            right: 0;
            height: 48px;
            overflow: visible;
            pointer-events: none;
        }

        .cc-walking-cat {
            position: absolute;
            bottom: 0;
            left: -80px;
            width: 80px;
            height: 48px;
            animation: catWalk 8s linear infinite;
            image-rendering: pixelated;
            cursor: pointer;
            pointer-events: auto;
        }

        @keyframes catWalk {
            0% { left: -80px; }
            45% { left: calc(100% + 10px); transform: scaleX(1); }
            45.01% { transform: scaleX(-1); }
            90% { left: -80px; transform: scaleX(-1); }
            90.01% { transform: scaleX(1); }
            100% { left: -80px; transform: scaleX(1); }
        }

        /* Walking cat pixel art */
        .cc-cat-body {
            width: 4px;
            height: 4px;
            position: absolute;
            top: 0;
            left: 0;
            background: transparent;
            box-shadow:
                /* Ears */
                20px 0px 0 var(--cc-text), 24px -4px 0 var(--cc-text), 28px 0px 0 var(--cc-text),
                52px 0px 0 var(--cc-text), 56px -4px 0 var(--cc-text), 60px 0px 0 var(--cc-text),
                /* Head top */
                24px 4px 0 var(--cc-text), 28px 4px 0 var(--cc-text), 32px 4px 0 var(--cc-text),
                36px 4px 0 var(--cc-text), 40px 4px 0 var(--cc-text), 44px 4px 0 var(--cc-text),
                48px 4px 0 var(--cc-text), 52px 4px 0 var(--cc-text), 56px 4px 0 var(--cc-text),
                /* Head row 2 */
                20px 8px 0 var(--cc-text), 24px 8px 0 var(--cc-text), 28px 8px 0 var(--cc-text),
                32px 8px 0 var(--cc-text), 36px 8px 0 var(--cc-text), 40px 8px 0 var(--cc-text),
                44px 8px 0 var(--cc-text), 48px 8px 0 var(--cc-text), 52px 8px 0 var(--cc-text),
                56px 8px 0 var(--cc-text), 60px 8px 0 var(--cc-text),
                /* Eyes row */
                20px 12px 0 var(--cc-text), 24px 12px 0 var(--cc-text),
                28px 12px 0 var(--cc-dark), 32px 12px 0 var(--cc-primary),
                36px 12px 0 var(--cc-text), 40px 12px 0 var(--cc-text),
                44px 12px 0 var(--cc-text),
                48px 12px 0 var(--cc-dark), 52px 12px 0 var(--cc-primary),
                56px 12px 0 var(--cc-text), 60px 12px 0 var(--cc-text),
                /* Nose/mouth */
                20px 16px 0 var(--cc-text), 24px 16px 0 var(--cc-text), 28px 16px 0 var(--cc-text),
                32px 16px 0 var(--cc-text), 36px 16px 0 var(--cc-text), 40px 16px 0 var(--cc-secondary),
                44px 16px 0 var(--cc-text), 48px 16px 0 var(--cc-text), 52px 16px 0 var(--cc-text),
                56px 16px 0 var(--cc-text), 60px 16px 0 var(--cc-text),
                /* Whiskers */
                8px 20px 0 var(--cc-muted), 12px 20px 0 var(--cc-muted),
                68px 20px 0 var(--cc-muted), 72px 20px 0 var(--cc-muted),
                /* Lower face */
                20px 20px 0 var(--cc-text), 24px 20px 0 var(--cc-text), 28px 20px 0 var(--cc-text),
                32px 20px 0 var(--cc-text), 36px 20px 0 var(--cc-text), 40px 20px 0 var(--cc-text),
                44px 20px 0 var(--cc-text), 48px 20px 0 var(--cc-text), 52px 20px 0 var(--cc-text),
                56px 20px 0 var(--cc-text), 60px 20px 0 var(--cc-text),
                /* Body */
                24px 24px 0 var(--cc-text), 28px 24px 0 var(--cc-text), 32px 24px 0 var(--cc-text),
                36px 24px 0 var(--cc-text), 40px 24px 0 var(--cc-text), 44px 24px 0 var(--cc-text),
                48px 24px 0 var(--cc-text), 52px 24px 0 var(--cc-text), 56px 24px 0 var(--cc-text),
                20px 28px 0 var(--cc-text), 24px 28px 0 var(--cc-text), 28px 28px 0 var(--cc-text),
                32px 28px 0 var(--cc-text), 36px 28px 0 var(--cc-text), 40px 28px 0 var(--cc-text),
                44px 28px 0 var(--cc-text), 48px 28px 0 var(--cc-text), 52px 28px 0 var(--cc-text),
                56px 28px 0 var(--cc-text), 60px 28px 0 var(--cc-text),
                /* Lower body */
                20px 32px 0 var(--cc-text), 24px 32px 0 var(--cc-text), 28px 32px 0 var(--cc-text),
                32px 32px 0 var(--cc-text), 36px 32px 0 var(--cc-text), 40px 32px 0 var(--cc-text),
                44px 32px 0 var(--cc-text), 48px 32px 0 var(--cc-text), 52px 32px 0 var(--cc-text),
                56px 32px 0 var(--cc-text), 60px 32px 0 var(--cc-text),
                /* Tail */
                64px 28px 0 var(--cc-text), 68px 28px 0 var(--cc-text),
                72px 24px 0 var(--cc-text), 76px 20px 0 var(--cc-text), 80px 20px 0 var(--cc-text);
        }

        /* Walking legs - frame 1 */
        .cc-cat-legs {
            width: 4px;
            height: 4px;
            position: absolute;
            top: 0;
            left: 0;
            background: transparent;
            box-shadow:
                16px 36px 0 var(--cc-text), 20px 40px 0 var(--cc-text),
                56px 36px 0 var(--cc-text), 60px 40px 0 var(--cc-text);
            animation: legWalk 0.4s steps(1) infinite;
        }

        @keyframes legWalk {
            0%, 100% {
                box-shadow:
                    16px 36px 0 var(--cc-text), 20px 40px 0 var(--cc-text),
                    56px 36px 0 var(--cc-text), 60px 40px 0 var(--cc-text);
            }
            50% {
                box-shadow:
                    20px 36px 0 var(--cc-text), 16px 40px 0 var(--cc-text),
                    60px 36px 0 var(--cc-text), 56px 40px 0 var(--cc-text);
            }
        }

        /* Eye blink */
        .cc-cat-blink {
            width: 4px;
            height: 4px;
            position: absolute;
            top: 0;
            left: 0;
            background: transparent;
            box-shadow:
                28px 12px 0 var(--cc-text), 32px 12px 0 var(--cc-text),
                48px 12px 0 var(--cc-text), 52px 12px 0 var(--cc-text);
            opacity: 0;
            z-index: 1;
            animation: eyeBlink 4s ease-in-out infinite;
        }

        @keyframes eyeBlink {
            0%, 90%, 100% { opacity: 0; }
            95%, 97% { opacity: 1; }
        }

        /* -- Header -- */
        .cc-login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .cc-logo-text {
            font-family: 'Press Start 2P', monospace;
            font-size: 1.1rem;
            color: var(--cc-text);
            letter-spacing: 2px;
        }
        .cc-logo-accent {
            color: var(--cc-primary);
            text-shadow: 0 0 10px rgba(0, 240, 255, 0.5), 0 0 30px rgba(0, 240, 255, 0.2);
        }
        .cc-subtitle {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--cc-muted);
            margin-top: 0.5rem;
            letter-spacing: 2px;
        }

        /* -- Form styling -- */
        .cc-form-area {
            position: relative;
        }

        .cc-form-area .fi-fo-field-wrp label {
            font-family: 'JetBrains Mono', monospace !important;
            color: var(--cc-muted) !important;
            font-size: 0.7rem !important;
            letter-spacing: 1px !important;
            text-transform: uppercase !important;
        }

        .cc-form-area .fi-input {
            background: rgba(0, 240, 255, 0.04) !important;
            border: 1px solid rgba(0, 240, 255, 0.15) !important;
            color: var(--cc-text) !important;
            font-family: 'JetBrains Mono', monospace !important;
            transition: all 0.3s !important;
        }

        .cc-form-area .fi-input:focus {
            border-color: var(--cc-primary) !important;
            box-shadow: 0 0 0 1px var(--cc-primary), 0 0 15px rgba(0, 240, 255, 0.1) !important;
            outline: none !important;
        }

        .cc-form-area .fi-input::placeholder {
            color: rgba(120, 120, 160, 0.5) !important;
        }

        .cc-form-area .fi-btn-primary {
            background: transparent !important;
            border: 1px solid var(--cc-primary) !important;
            color: var(--cc-primary) !important;
            font-family: 'Press Start 2P', monospace !important;
            font-size: 0.65rem !important;
            letter-spacing: 1px !important;
            padding: 0.75rem 1.5rem !important;
            transition: all 0.3s !important;
            border-radius: 0 !important;
        }

        .cc-form-area .fi-btn-primary:hover {
            background: var(--cc-primary) !important;
            color: var(--cc-dark) !important;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.5), 0 0 30px rgba(0, 240, 255, 0.2) !important;
        }

        .cc-form-area .fi-checkbox-input:checked {
            background-color: var(--cc-primary) !important;
            border-color: var(--cc-primary) !important;
        }

        .cc-form-area .fi-link {
            color: var(--cc-muted) !important;
            font-family: 'JetBrains Mono', monospace !important;
            font-size: 0.75rem !important;
        }

        .cc-form-area .fi-link:hover {
            color: var(--cc-primary) !important;
        }

        /* -- Footer -- */
        .cc-login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: var(--cc-muted);
        }

        .cc-blink-cursor {
            color: var(--cc-primary);
            animation: cursorBlink 1s steps(1) infinite;
        }

        @keyframes cursorBlink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        /* -- Speech bubble on cat click -- */
        .cc-speech {
            position: absolute;
            top: -28px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--cc-surface);
            border: 1px solid var(--cc-primary);
            padding: 3px 8px;
            font-family: 'Press Start 2P', monospace;
            font-size: 0.4rem;
            color: var(--cc-primary);
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            z-index: 50;
        }
        .cc-speech.show { opacity: 1; }
        .cc-speech::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 4px solid var(--cc-primary);
        }

        /* -- Dark mode overrides for Filament internals -- */
        .cc-form-area .fi-fo-field-wrp,
        .cc-form-area .fi-form-component-ctn {
            --tw-ring-color: rgba(0, 240, 255, 0.3) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cat = document.getElementById('walking-cat');
            if (!cat) return;

            const meows = ['Meow!', '*purr*', 'Login pls', 'Feed me.', 'Ship it!', '> sudo...'];
            let speech = null;

            cat.addEventListener('click', function(e) {
                e.stopPropagation();
                if (speech) speech.remove();

                speech = document.createElement('div');
                speech.className = 'cc-speech show';
                speech.textContent = meows[Math.floor(Math.random() * meows.length)];
                cat.appendChild(speech);

                setTimeout(() => {
                    if (speech) { speech.remove(); speech = null; }
                }, 2000);
            });
        });
    </script>
</div>
