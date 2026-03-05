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

        <div class="cc-login-card" id="login-card">
            {{-- Cat walking on top edge --}}
            <div class="cc-cat-track" id="cat-track">
                <div class="cc-cat" id="the-cat">
                    <div class="cc-cat-body"></div>
                    <div class="cc-cat-legs"></div>
                    <div class="cc-cat-blink"></div>
                    <div class="cc-cat-zzz" id="cat-zzz"></div>
                </div>
                {{-- Fly element for chase behavior --}}
                <div class="cc-fly" id="the-fly"></div>
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
        .fi-simple-layout { background: transparent !important; }
        .fi-simple-main-ctn { max-width: none !important; padding: 0 !important; }
        .fi-simple-main {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: none !important;
        }
        body.fi-body { background: #0a0a1a !important; min-height: 100vh; }
        .fi-simple-header { display: none !important; }

        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Press+Start+2P&display=swap');

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
            position: fixed; inset: 0;
            background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,240,255,0.015) 2px, rgba(0,240,255,0.015) 4px);
            pointer-events: none; z-index: 100;
        }

        /* -- Particles -- */
        .cc-particles { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .cc-particle {
            position: absolute; bottom: -4px; width: 2px; height: 2px;
            background: var(--cc-primary); opacity: 0.3;
            animation: particleFloat linear infinite;
        }
        @keyframes particleFloat {
            0% { transform: translateY(0); opacity: 0; }
            10% { opacity: 0.3; } 90% { opacity: 0.3; }
            100% { transform: translateY(-100vh); opacity: 0; }
        }

        /* -- Card -- */
        .cc-login-card {
            position: relative; width: 100%; max-width: 420px;
            background: var(--cc-surface);
            border: 1px solid rgba(0,240,255,0.2);
            padding: 2.5rem 2rem 1.5rem; z-index: 10;
            box-shadow: 0 0 60px rgba(0,240,255,0.06) inset, 0 0 4px rgba(0,0,0,0.8) inset, 0 0 15px rgba(0,240,255,0.08);
        }
        .cc-card-border-top {
            position: absolute; top: -1px; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, var(--cc-primary) 20%, var(--cc-secondary) 50%, var(--cc-primary) 80%, transparent);
            box-shadow: 0 0 10px var(--cc-primary), 0 0 30px rgba(0,240,255,0.3);
        }

        /* -- Cat track: clips at card edges -- */
        .cc-cat-track {
            position: absolute; top: -48px; left: 0; right: 0;
            height: 48px; overflow: hidden; pointer-events: none;
        }

        /* -- Cat -- */
        .cc-cat {
            position: absolute; bottom: 0; left: 50px;
            width: 80px; height: 48px;
            image-rendering: pixelated;
            cursor: pointer; pointer-events: auto;
            transition: none;
        }
        .cc-cat.facing-left { transform: scaleX(-1); }
        .cc-cat.falling {
            transition: transform 1.2s cubic-bezier(0.55, 0, 1, 0.45), bottom 1.2s cubic-bezier(0.55, 0, 1, 0.45);
        }

        /* Pixel art body */
        .cc-cat-body {
            width: 4px; height: 4px; position: absolute; top: 0; left: 0;
            background: transparent;
            box-shadow:
                20px 0px 0 var(--cc-text), 24px -4px 0 var(--cc-text), 28px 0px 0 var(--cc-text),
                52px 0px 0 var(--cc-text), 56px -4px 0 var(--cc-text), 60px 0px 0 var(--cc-text),
                24px 4px 0 var(--cc-text), 28px 4px 0 var(--cc-text), 32px 4px 0 var(--cc-text),
                36px 4px 0 var(--cc-text), 40px 4px 0 var(--cc-text), 44px 4px 0 var(--cc-text),
                48px 4px 0 var(--cc-text), 52px 4px 0 var(--cc-text), 56px 4px 0 var(--cc-text),
                20px 8px 0 var(--cc-text), 24px 8px 0 var(--cc-text), 28px 8px 0 var(--cc-text),
                32px 8px 0 var(--cc-text), 36px 8px 0 var(--cc-text), 40px 8px 0 var(--cc-text),
                44px 8px 0 var(--cc-text), 48px 8px 0 var(--cc-text), 52px 8px 0 var(--cc-text),
                56px 8px 0 var(--cc-text), 60px 8px 0 var(--cc-text),
                20px 12px 0 var(--cc-text), 24px 12px 0 var(--cc-text),
                28px 12px 0 var(--cc-dark), 32px 12px 0 var(--cc-primary),
                36px 12px 0 var(--cc-text), 40px 12px 0 var(--cc-text), 44px 12px 0 var(--cc-text),
                48px 12px 0 var(--cc-dark), 52px 12px 0 var(--cc-primary),
                56px 12px 0 var(--cc-text), 60px 12px 0 var(--cc-text),
                20px 16px 0 var(--cc-text), 24px 16px 0 var(--cc-text), 28px 16px 0 var(--cc-text),
                32px 16px 0 var(--cc-text), 36px 16px 0 var(--cc-text), 40px 16px 0 var(--cc-secondary),
                44px 16px 0 var(--cc-text), 48px 16px 0 var(--cc-text), 52px 16px 0 var(--cc-text),
                56px 16px 0 var(--cc-text), 60px 16px 0 var(--cc-text),
                8px 20px 0 var(--cc-muted), 12px 20px 0 var(--cc-muted),
                68px 20px 0 var(--cc-muted), 72px 20px 0 var(--cc-muted),
                20px 20px 0 var(--cc-text), 24px 20px 0 var(--cc-text), 28px 20px 0 var(--cc-text),
                32px 20px 0 var(--cc-text), 36px 20px 0 var(--cc-text), 40px 20px 0 var(--cc-text),
                44px 20px 0 var(--cc-text), 48px 20px 0 var(--cc-text), 52px 20px 0 var(--cc-text),
                56px 20px 0 var(--cc-text), 60px 20px 0 var(--cc-text),
                24px 24px 0 var(--cc-text), 28px 24px 0 var(--cc-text), 32px 24px 0 var(--cc-text),
                36px 24px 0 var(--cc-text), 40px 24px 0 var(--cc-text), 44px 24px 0 var(--cc-text),
                48px 24px 0 var(--cc-text), 52px 24px 0 var(--cc-text), 56px 24px 0 var(--cc-text),
                20px 28px 0 var(--cc-text), 24px 28px 0 var(--cc-text), 28px 28px 0 var(--cc-text),
                32px 28px 0 var(--cc-text), 36px 28px 0 var(--cc-text), 40px 28px 0 var(--cc-text),
                44px 28px 0 var(--cc-text), 48px 28px 0 var(--cc-text), 52px 28px 0 var(--cc-text),
                56px 28px 0 var(--cc-text), 60px 28px 0 var(--cc-text),
                20px 32px 0 var(--cc-text), 24px 32px 0 var(--cc-text), 28px 32px 0 var(--cc-text),
                32px 32px 0 var(--cc-text), 36px 32px 0 var(--cc-text), 40px 32px 0 var(--cc-text),
                44px 32px 0 var(--cc-text), 48px 32px 0 var(--cc-text), 52px 32px 0 var(--cc-text),
                56px 32px 0 var(--cc-text), 60px 32px 0 var(--cc-text),
                64px 28px 0 var(--cc-text), 68px 28px 0 var(--cc-text),
                72px 24px 0 var(--cc-text), 76px 20px 0 var(--cc-text), 80px 20px 0 var(--cc-text);
        }

        /* Legs */
        .cc-cat-legs {
            width: 4px; height: 4px; position: absolute; top: 0; left: 0;
            background: transparent;
            box-shadow:
                16px 36px 0 var(--cc-text), 20px 40px 0 var(--cc-text),
                56px 36px 0 var(--cc-text), 60px 40px 0 var(--cc-text);
        }
        .cc-cat.walking .cc-cat-legs { animation: legWalk 0.4s steps(1) infinite; }
        .cc-cat.sitting .cc-cat-legs {
            box-shadow:
                20px 36px 0 var(--cc-text), 24px 36px 0 var(--cc-text),
                56px 36px 0 var(--cc-text), 60px 36px 0 var(--cc-text);
        }

        @keyframes legWalk {
            0%, 100% {
                box-shadow: 16px 36px 0 var(--cc-text), 20px 40px 0 var(--cc-text),
                            56px 36px 0 var(--cc-text), 60px 40px 0 var(--cc-text);
            }
            50% {
                box-shadow: 20px 36px 0 var(--cc-text), 16px 40px 0 var(--cc-text),
                            60px 36px 0 var(--cc-text), 56px 40px 0 var(--cc-text);
            }
        }

        /* Eye blink */
        .cc-cat-blink {
            width: 4px; height: 4px; position: absolute; top: 0; left: 0;
            background: transparent;
            box-shadow: 28px 12px 0 var(--cc-text), 32px 12px 0 var(--cc-text),
                        48px 12px 0 var(--cc-text), 52px 12px 0 var(--cc-text);
            opacity: 0; z-index: 1;
            animation: eyeBlink 4s ease-in-out infinite;
        }
        .cc-cat.sleeping .cc-cat-blink { opacity: 1; animation: none; }

        @keyframes eyeBlink {
            0%, 90%, 100% { opacity: 0; }
            95%, 97% { opacity: 1; }
        }

        /* Zzz for sleeping */
        .cc-cat-zzz {
            position: absolute; top: -16px; right: -4px;
            font-family: 'Press Start 2P', monospace;
            font-size: 0.35rem; color: var(--cc-primary);
            opacity: 0; pointer-events: none;
        }
        .cc-cat.sleeping .cc-cat-zzz { animation: zzzFloat 2s ease-in-out infinite; }

        @keyframes zzzFloat {
            0% { opacity: 0; transform: translateY(0); }
            20% { opacity: 1; }
            100% { opacity: 0; transform: translateY(-16px) translateX(8px); }
        }

        /* Fly */
        .cc-fly {
            position: absolute; width: 4px; height: 4px;
            background: var(--cc-tertiary);
            border-radius: 50%;
            opacity: 0; pointer-events: none;
            box-shadow: 0 0 6px var(--cc-tertiary), 0 0 12px rgba(57,255,20,0.3);
        }
        .cc-fly.active { opacity: 1; }

        /* -- Header -- */
        .cc-login-header { text-align: center; margin-bottom: 2rem; }
        .cc-logo-text {
            font-family: 'Press Start 2P', monospace;
            font-size: 1.1rem; color: var(--cc-text); letter-spacing: 2px;
        }
        .cc-logo-accent {
            color: var(--cc-primary);
            text-shadow: 0 0 10px rgba(0,240,255,0.5), 0 0 30px rgba(0,240,255,0.2);
        }
        .cc-subtitle {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem; color: var(--cc-muted);
            margin-top: 0.5rem; letter-spacing: 2px;
        }

        /* -- Form styling -- */
        .cc-form-area { position: relative; }
        .cc-form-area .fi-fo-field-wrp label {
            font-family: 'JetBrains Mono', monospace !important;
            color: var(--cc-muted) !important; font-size: 0.7rem !important;
            letter-spacing: 1px !important; text-transform: uppercase !important;
        }
        .cc-form-area .fi-input {
            background: rgba(0,240,255,0.04) !important;
            border: 1px solid rgba(0,240,255,0.15) !important;
            color: var(--cc-text) !important;
            font-family: 'JetBrains Mono', monospace !important;
            transition: all 0.3s !important;
        }
        .cc-form-area .fi-input:focus {
            border-color: var(--cc-primary) !important;
            box-shadow: 0 0 0 1px var(--cc-primary), 0 0 15px rgba(0,240,255,0.1) !important;
            outline: none !important;
        }
        .cc-form-area .fi-input::placeholder { color: rgba(120,120,160,0.5) !important; }
        .cc-form-area .fi-btn-primary {
            background: transparent !important;
            border: 1px solid var(--cc-primary) !important;
            color: var(--cc-primary) !important;
            font-family: 'Press Start 2P', monospace !important;
            font-size: 0.65rem !important; letter-spacing: 1px !important;
            padding: 0.75rem 1.5rem !important;
            transition: all 0.3s !important; border-radius: 0 !important;
        }
        .cc-form-area .fi-btn-primary:hover {
            background: var(--cc-primary) !important;
            color: var(--cc-dark) !important;
            box-shadow: 0 0 10px rgba(0,240,255,0.5), 0 0 30px rgba(0,240,255,0.2) !important;
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
        .cc-form-area .fi-link:hover { color: var(--cc-primary) !important; }

        /* -- Footer -- */
        .cc-login-footer {
            text-align: center; margin-top: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem; color: var(--cc-muted);
        }
        .cc-blink-cursor { color: var(--cc-primary); animation: cursorBlink 1s steps(1) infinite; }
        @keyframes cursorBlink { 0%, 50% { opacity: 1; } 51%, 100% { opacity: 0; } }

        /* -- Speech bubble -- */
        .cc-speech {
            position: absolute; top: -28px; left: 50%;
            transform: translateX(-50%);
            background: var(--cc-surface); border: 1px solid var(--cc-primary);
            padding: 3px 8px;
            font-family: 'Press Start 2P', monospace;
            font-size: 0.4rem; color: var(--cc-primary);
            white-space: nowrap; opacity: 0;
            transition: opacity 0.3s; pointer-events: none; z-index: 50;
        }
        .cc-speech.show { opacity: 1; }
        .cc-speech::after {
            content: ''; position: absolute; bottom: -5px; left: 50%;
            transform: translateX(-50%);
            border-left: 4px solid transparent; border-right: 4px solid transparent;
            border-top: 4px solid var(--cc-primary);
        }

        .cc-form-area .fi-fo-field-wrp,
        .cc-form-area .fi-form-component-ctn {
            --tw-ring-color: rgba(0,240,255,0.3) !important;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cat = document.getElementById('the-cat');
        const track = document.getElementById('cat-track');
        const fly = document.getElementById('the-fly');
        const zzz = document.getElementById('cat-zzz');
        if (!cat || !track) return;

        const CAT_W = 80;
        const SPEED = 60; // px per second
        let trackW = track.offsetWidth;
        let x = Math.random() * (trackW - CAT_W);
        let state = 'idle';
        let facingLeft = false;
        let animFrame = null;
        let stateTimeout = null;

        window.addEventListener('resize', () => { trackW = track.offsetWidth; });

        function setX(val) {
            x = Math.max(0, Math.min(val, trackW - CAT_W));
            cat.style.left = x + 'px';
        }

        function setFacing(left) {
            facingLeft = left;
            cat.classList.toggle('facing-left', left);
        }

        function setState(s) {
            cat.classList.remove('walking', 'sitting', 'sleeping', 'falling');
            state = s;
            if (s === 'walking') cat.classList.add('walking');
            if (s === 'sit' || s === 'chase') cat.classList.add('sitting');
            if (s === 'sleep') { cat.classList.add('sitting', 'sleeping'); }
            if (s === 'fall') cat.classList.add('falling');
        }

        // -- BEHAVIORS --

        function walkTo(targetX, cb) {
            setState('walking');
            setFacing(targetX < x);
            const startX = x;
            const dist = Math.abs(targetX - startX);
            const duration = (dist / SPEED) * 1000;
            const startTime = performance.now();

            function step(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                setX(startX + (targetX - startX) * progress);
                if (progress < 1) {
                    animFrame = requestAnimationFrame(step);
                } else {
                    animFrame = null;
                    cb && cb();
                }
            }
            animFrame = requestAnimationFrame(step);
        }

        function doWalk() {
            const target = Math.random() * (trackW - CAT_W);
            walkTo(target, () => nextBehavior());
        }

        function doSit() {
            setState('sit');
            stateTimeout = setTimeout(() => nextBehavior(), 2000 + Math.random() * 3000);
        }

        function doSleep() {
            setState('sleep');
            zzz.textContent = 'z Z z';
            stateTimeout = setTimeout(() => {
                zzz.textContent = '';
                nextBehavior();
            }, 4000 + Math.random() * 4000);
        }

        function doChase() {
            // Fly appears and bounces around, cat follows
            fly.classList.add('active');
            setState('walking');

            let flyX = Math.random() * (trackW - 20);
            let flyY = 10 + Math.random() * 30;
            fly.style.left = flyX + 'px';
            fly.style.top = flyY + 'px';

            let bounces = 0;
            const maxBounces = 3 + Math.floor(Math.random() * 3);

            function chaseBounce() {
                flyX = Math.random() * (trackW - 20);
                flyY = 5 + Math.random() * 35;
                fly.style.transition = 'left 0.3s ease-out, top 0.3s ease-out';
                fly.style.left = flyX + 'px';
                fly.style.top = flyY + 'px';

                walkTo(Math.max(0, Math.min(flyX - 30, trackW - CAT_W)), () => {
                    bounces++;
                    if (bounces < maxBounces) {
                        stateTimeout = setTimeout(chaseBounce, 200 + Math.random() * 400);
                    } else {
                        // Cat catches fly
                        fly.style.left = (x + 40) + 'px';
                        fly.style.top = '30px';
                        setState('sit');
                        stateTimeout = setTimeout(() => {
                            fly.classList.remove('active');
                            fly.style.transition = '';
                            showSpeech('*munch*');
                            stateTimeout = setTimeout(() => nextBehavior(), 1500);
                        }, 500);
                    }
                });
            }
            chaseBounce();
        }

        function doFall() {
            // Walk to edge, look down, fall off
            const edge = Math.random() > 0.5 ? trackW - CAT_W : 0;
            walkTo(edge, () => {
                setState('sit');
                setFacing(edge === 0);
                // Pause at edge, looking down
                stateTimeout = setTimeout(() => {
                    // Fall!
                    track.style.overflow = 'visible';
                    setState('fall');
                    cat.style.transition = 'bottom 1.2s cubic-bezier(0.55, 0, 1, 0.45), transform 1.2s ease-in';
                    cat.style.bottom = '-300px';
                    cat.style.transform = (facingLeft ? 'scaleX(-1) ' : '') + 'rotate(' + (edge === 0 ? '-' : '') + '90deg)';

                    stateTimeout = setTimeout(() => {
                        // Reset: cat reappears from other side
                        cat.style.transition = 'none';
                        track.style.overflow = 'hidden';
                        cat.style.bottom = '0';
                        cat.style.transform = '';
                        setX(edge === 0 ? trackW - CAT_W : 0);
                        setFacing(false);
                        setState('idle');
                        showSpeech('Ow!');
                        stateTimeout = setTimeout(() => nextBehavior(), 1500);
                    }, 1500);
                }, 800);
            });
        }

        function nextBehavior() {
            if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
            if (stateTimeout) { clearTimeout(stateTimeout); stateTimeout = null; }

            const r = Math.random();
            if (r < 0.35)      doWalk();
            else if (r < 0.55) doSit();
            else if (r < 0.70) doSleep();
            else if (r < 0.88) doChase();
            else               doFall();
        }

        // -- SPEECH --
        const meows = ['Meow!', '*purr*', 'Login pls', 'Feed me.', 'Ship it!', '> sudo...', ':3', 'Nya~'];
        let speechEl = null;

        function showSpeech(text) {
            if (speechEl) speechEl.remove();
            speechEl = document.createElement('div');
            speechEl.className = 'cc-speech show';
            speechEl.textContent = text;
            cat.appendChild(speechEl);
            setTimeout(() => { if (speechEl) { speechEl.remove(); speechEl = null; } }, 2000);
        }

        cat.addEventListener('click', function(e) {
            e.stopPropagation();
            showSpeech(meows[Math.floor(Math.random() * meows.length)]);
        });

        // Start
        setX(x);
        nextBehavior();
    });
    </script>
</div>
