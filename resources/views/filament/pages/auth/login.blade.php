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

            {{-- Cat bottom track (appears when cat falls) --}}
            <div class="cc-cat-track cc-cat-track-bottom" id="cat-track-bottom"></div>
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

        /* CSS vars on :root so they work when cat is moved to body */
        :root {
            --cc-bg: #0a0a1a;
            --cc-surface: #12122a;
            --cc-primary: #00f0ff;
            --cc-secondary: #ff00aa;
            --cc-tertiary: #39ff14;
            --cc-text: #e0e0ff;
            --cc-muted: #7878a0;
            --cc-dark: #0a0a1a;
        }

        .cc-login-wrapper {
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
        .cc-cat-track-bottom {
            top: auto; bottom: -48px;
        }

        /* -- Cat -- */
        .cc-cat {
            position: absolute; bottom: 0; left: 50px;
            width: 80px; height: 48px;
            image-rendering: pixelated;
            cursor: grab; pointer-events: auto;
            transition: none;
            user-select: none; -webkit-user-select: none;
            touch-action: none;
        }
        .cc-cat.dragging, .cc-cat.free-fall {
            position: fixed;
            z-index: 9999;
            cursor: grabbing;
            overflow: visible !important;
            clip: auto !important;
            clip-path: none !important;
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
        .cc-cat.sitting .cc-cat-legs, .cc-cat.grooming .cc-cat-legs {
            box-shadow:
                20px 36px 0 var(--cc-text), 24px 36px 0 var(--cc-text),
                56px 36px 0 var(--cc-text), 60px 36px 0 var(--cc-text);
        }
        .cc-cat.pouncing .cc-cat-legs {
            box-shadow:
                16px 36px 0 var(--cc-text), 20px 36px 0 var(--cc-text),
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

        /* Grooming paw animation */
        .cc-cat.grooming .cc-cat-legs {
            animation: groomPaw 0.8s steps(1) infinite;
        }
        @keyframes groomPaw {
            0%, 100% {
                box-shadow:
                    20px 36px 0 var(--cc-text), 24px 36px 0 var(--cc-text),
                    56px 36px 0 var(--cc-text), 60px 36px 0 var(--cc-text);
            }
            50% {
                box-shadow:
                    24px 28px 0 var(--cc-text), 28px 24px 0 var(--cc-text),
                    56px 36px 0 var(--cc-text), 60px 36px 0 var(--cc-text);
            }
        }

        /* Spinning for tail chase */
        .cc-cat.spinning {
            animation: tailSpin 0.6s linear infinite;
            transform-origin: 40px 24px;
        }
        @keyframes tailSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Pounce animation */
        .cc-cat.pouncing {
            animation: pounceAnim 0.4s ease-out;
        }
        @keyframes pounceAnim {
            0% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            100% { transform: translateY(0); }
        }
        .cc-cat.pouncing.facing-left {
            animation: pounceAnimFlip 0.4s ease-out;
        }
        @keyframes pounceAnimFlip {
            0% { transform: scaleX(-1) translateY(0); }
            40% { transform: scaleX(-1) translateY(-20px); }
            100% { transform: scaleX(-1) translateY(0); }
        }

        /* Peek over edge */
        .cc-cat.peeking {
            transition: bottom 0.4s ease-in-out;
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
        .cc-fly.active { opacity: 1; animation: flyBuzz 0.15s ease-in-out infinite alternate; }
        @keyframes flyBuzz {
            0% { transform: translate(-1px, -1px); }
            100% { transform: translate(1px, 1px); }
        }

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
            cat.classList.remove('walking', 'sitting', 'sleeping', 'falling', 'grooming', 'spinning', 'pouncing', 'peeking');
            if (s !== 'peek' && s !== 'fall') { cat.style.bottom = '0'; }
            state = s;
            if (s === 'walking') cat.classList.add('walking');
            if (s === 'sit' || s === 'chase') cat.classList.add('sitting');
            if (s === 'sleep') { cat.classList.add('sitting', 'sleeping'); }
            if (s === 'fall') cat.classList.add('falling');
            if (s === 'groom') cat.classList.add('sitting', 'grooming');
            if (s === 'spin') cat.classList.add('spinning');
            if (s === 'pounce') cat.classList.add('pouncing');
            if (s === 'peek') cat.classList.add('sitting', 'peeking');
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

        function doGroom() {
            setState('groom');
            showSpeech('*lick*');
            stateTimeout = setTimeout(() => nextBehavior(), 3000 + Math.random() * 2000);
        }

        function doTailChase() {
            setState('spin');
            showSpeech('?!');
            const spins = 3 + Math.floor(Math.random() * 4);
            stateTimeout = setTimeout(() => {
                setState('sit');
                showSpeech('*dizzy*');
                stateTimeout = setTimeout(() => nextBehavior(), 1500);
            }, spins * 600);
        }

        function doPounce() {
            // Wiggle butt, then pounce forward
            setState('sit');
            const pounceDir = Math.random() > 0.5;
            setFacing(pounceDir);

            // Butt wiggle phase
            let wiggles = 0;
            const wiggleInterval = setInterval(() => {
                cat.style.transform = (facingLeft ? 'scaleX(-1) ' : '') + 'translateX(' + (wiggles % 2 === 0 ? '2' : '-2') + 'px)';
                wiggles++;
                if (wiggles >= 6) {
                    clearInterval(wiggleInterval);
                    cat.style.transform = facingLeft ? 'scaleX(-1)' : '';
                    // Pounce!
                    setState('pounce');
                    const jumpDist = 60 + Math.random() * 80;
                    const targetX = facingLeft ? x - jumpDist : x + jumpDist;
                    setTimeout(() => {
                        setX(targetX);
                        setTimeout(() => {
                            setState('sit');
                            const caught = Math.random() > 0.5;
                            showSpeech(caught ? '*gotcha!*' : '*missed*');
                            stateTimeout = setTimeout(() => nextBehavior(), 1500);
                        }, 400);
                    }, 50);
                }
            }, 100);
        }

        function doPeek() {
            // Walk to edge and peek down
            const edge = Math.random() > 0.5 ? trackW - CAT_W : 0;
            walkTo(edge, () => {
                setState('peek');
                setFacing(edge === 0);
                // Slowly lower down to peek
                cat.style.bottom = '-16px';
                showSpeech('...');
                stateTimeout = setTimeout(() => {
                    // Look around
                    showSpeech('hmm');
                    stateTimeout = setTimeout(() => {
                        // Come back up
                        cat.style.bottom = '0';
                        stateTimeout = setTimeout(() => nextBehavior(), 800);
                    }, 1500);
                }, 1500);
            });
        }

        const bottomTrack = document.getElementById('cat-track-bottom');
        let isOnBottom = false;

        function moveToBottomTrack() {
            if (!bottomTrack) return;
            isOnBottom = true;
            bottomTrack.appendChild(cat);
            bottomTrack.appendChild(fly);
            cat.style.transition = 'none';
            cat.style.bottom = '0';
            cat.style.transform = '';
            setX(Math.random() * (trackW - CAT_W));
            setFacing(false);
        }

        function moveToTopTrack() {
            isOnBottom = false;
            track.appendChild(cat);
            track.appendChild(fly);
            cat.style.transition = 'none';
            cat.style.bottom = '0';
            cat.style.transform = '';
        }

        function doFall() {
            // Walk to edge, look down, then fall using physics
            const edge = Math.random() > 0.5 ? trackW - CAT_W : 0;
            walkTo(edge, () => {
                setState('sit');
                setFacing(edge === 0);
                showSpeech('...');
                // Pause at edge, then fall with physics
                stateTimeout = setTimeout(() => {
                    // Get cat's current screen position
                    const catRect = cat.getBoundingClientRect();

                    // Move cat to fixed position on body
                    cat.classList.remove('walking', 'sitting', 'sleeping', 'falling', 'grooming', 'spinning', 'pouncing', 'peeking');
                    cat.classList.add('free-fall');
                    cat.style.position = 'fixed';
                    cat.style.left = catRect.left + 'px';
                    cat.style.top = catRect.top + 'px';
                    cat.style.bottom = 'auto';
                    cat.style.transition = 'none';
                    document.body.appendChild(cat);

                    showSpeech('AAAH!');

                    // Use physics engine to fall
                    velX = (facingLeft ? -1 : 1) * (30 + Math.random() * 60);
                    velY = 0;
                    let px = catRect.left;
                    let py = catRect.top;
                    let lastTime = performance.now();

                    function fallStep(now) {
                        const dt = Math.min((now - lastTime) / 1000, 0.05);
                        lastTime = now;

                        velY += GRAVITY * dt;
                        velX *= (1 - (1 - FRICTION) * dt * 10);
                        px += velX * dt;
                        py += velY * dt;

                        const vw = window.innerWidth;
                        const vh = window.innerHeight;

                        // Floor
                        if (py > vh - 48) {
                            py = vh - 48;
                            velY = -velY * BOUNCE;
                            velX *= FRICTION;
                            if (Math.abs(velY) < 60) velY = 0;
                        }
                        // Walls
                        if (px < 0) { px = 0; velX = -velX * BOUNCE; }
                        if (px > vw - CAT_W) { px = vw - CAT_W; velX = -velX * BOUNCE; }

                        cat.style.left = px + 'px';
                        cat.style.top = py + 'px';

                        // Spin while falling fast
                        const speed = Math.sqrt(velX * velX + velY * velY);
                        if (speed > 200) {
                            const angle = Math.atan2(velY, velX) * (180 / Math.PI);
                            cat.style.transform = 'rotate(' + angle + 'deg)';
                        } else {
                            cat.style.transform = '';
                        }

                        // Settled on floor?
                        if (Math.abs(velX) < MIN_VEL && Math.abs(velY) < MIN_VEL && py >= vh - 50) {
                            cat.style.transform = '';
                            showSpeech('Ow!');
                            // Do some stuff on the floor, then return
                            setTimeout(() => {
                                showSpeech('hmm...');
                                setTimeout(() => returnToTrack(), 2000);
                            }, 1500);
                            return;
                        }

                        physicsFrame = requestAnimationFrame(fallStep);
                    }

                    physicsFrame = requestAnimationFrame(fallStep);
                }, 800);
            });
        }

        function doBottomBehavior(count) {
            // Do a few random things at the bottom, then climb back up
            if (count >= 2 + Math.floor(Math.random() * 3)) {
                // Time to climb back up
                doClimbBack();
                return;
            }

            const r = Math.random();
            if (r < 0.3) {
                // Walk around at bottom
                const target = Math.random() * (trackW - CAT_W);
                walkTo(target, () => {
                    stateTimeout = setTimeout(() => doBottomBehavior(count + 1), 500);
                });
            } else if (r < 0.5) {
                // Sit and look up
                setState('sit');
                showSpeech('how do I get up?');
                stateTimeout = setTimeout(() => doBottomBehavior(count + 1), 2000);
            } else if (r < 0.7) {
                // Groom
                setState('groom');
                showSpeech('*lick*');
                stateTimeout = setTimeout(() => doBottomBehavior(count + 1), 2500);
            } else if (r < 0.85) {
                // Tail chase at bottom
                setState('spin');
                showSpeech('?!');
                stateTimeout = setTimeout(() => {
                    setState('sit');
                    showSpeech('*dizzy*');
                    stateTimeout = setTimeout(() => doBottomBehavior(count + 1), 1000);
                }, 2400);
            } else {
                // Sleep briefly
                setState('sleep');
                zzz.textContent = 'z Z z';
                stateTimeout = setTimeout(() => {
                    zzz.textContent = '';
                    doBottomBehavior(count + 1);
                }, 3000);
            }
        }

        function doClimbBack() {
            // Walk to edge, "jump" back to top
            const edge = Math.random() > 0.5 ? 0 : trackW - CAT_W;
            walkTo(edge, () => {
                setState('sit');
                showSpeech('*jump!*');
                stateTimeout = setTimeout(() => {
                    if (bottomTrack) bottomTrack.style.overflow = 'visible';
                    cat.style.transition = 'bottom 0.6s ease-out';
                    cat.style.bottom = '400px';

                    stateTimeout = setTimeout(() => {
                        if (bottomTrack) bottomTrack.style.overflow = 'hidden';
                        moveToTopTrack();
                        setX(edge);
                        setState('sit');
                        showSpeech('Made it!');
                        stateTimeout = setTimeout(() => nextBehavior(), 1500);
                    }, 700);
                }, 500);
            });
        }

        function nextBehavior() {
            if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
            if (stateTimeout) { clearTimeout(stateTimeout); stateTimeout = null; }

            const r = Math.random();
            if (r < 0.22)      doWalk();
            else if (r < 0.35) doSit();
            else if (r < 0.45) doSleep();
            else if (r < 0.58) doChase();
            else if (r < 0.68) doGroom();
            else if (r < 0.78) doPounce();
            else if (r < 0.86) doTailChase();
            else if (r < 0.94) doPeek();
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

        // -- DRAG & PHYSICS --
        let isDragging = false;
        let dragStartX = 0, dragStartY = 0;
        let dragOffsetX = 0, dragOffsetY = 0;
        let dragMoved = false;
        let velX = 0, velY = 0;
        let lastDragX = 0, lastDragY = 0;
        let lastDragTime = 0;
        let physicsFrame = null;

        const GRAVITY = 1200; // px/s^2
        const BOUNCE = 0.5;
        const FRICTION = 0.85;
        const MIN_VEL = 20;

        function startDrag(clientX, clientY) {
            // Stop all current behavior
            if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
            if (stateTimeout) { clearTimeout(stateTimeout); stateTimeout = null; }
            if (physicsFrame) { cancelAnimationFrame(physicsFrame); physicsFrame = null; }

            isDragging = true;
            dragMoved = false;

            const catRect = cat.getBoundingClientRect();
            dragOffsetX = clientX - catRect.left;
            dragOffsetY = clientY - catRect.top;
            dragStartX = clientX;
            dragStartY = clientY;

            // Move cat to fixed position on the page
            cat.classList.remove('walking', 'sitting', 'sleeping', 'falling', 'grooming', 'spinning', 'pouncing', 'peeking', 'facing-left');
            cat.classList.add('dragging');
            cat.style.position = 'fixed';
            cat.style.left = (clientX - dragOffsetX) + 'px';
            cat.style.top = (clientY - dragOffsetY) + 'px';
            cat.style.bottom = 'auto';
            cat.style.transition = 'none';
            cat.style.transform = '';
            document.body.appendChild(cat);

            velX = 0; velY = 0;
            lastDragX = clientX;
            lastDragY = clientY;
            lastDragTime = performance.now();

            showSpeech('Mrrp!');
        }

        function moveDrag(clientX, clientY) {
            if (!isDragging) return;
            const dx = clientX - dragStartX;
            const dy = clientY - dragStartY;
            if (Math.abs(dx) > 3 || Math.abs(dy) > 3) dragMoved = true;

            cat.style.left = (clientX - dragOffsetX) + 'px';
            cat.style.top = (clientY - dragOffsetY) + 'px';

            // Track velocity
            const now = performance.now();
            const dt = (now - lastDragTime) / 1000;
            if (dt > 0.005) {
                velX = (clientX - lastDragX) / dt;
                velY = (clientY - lastDragY) / dt;
                lastDragX = clientX;
                lastDragY = clientY;
                lastDragTime = now;
            }

            // Flip cat based on drag direction
            if (Math.abs(velX) > 50) {
                cat.style.transform = velX < 0 ? 'scaleX(-1)' : '';
            }
        }

        function endDrag() {
            if (!isDragging) return;
            isDragging = false;
            cat.classList.remove('dragging');
            cat.classList.add('free-fall');

            // Cap velocity
            const maxVel = 2000;
            velX = Math.max(-maxVel, Math.min(maxVel, velX));
            velY = Math.max(-maxVel, Math.min(maxVel, velY));

            showSpeech(Math.abs(velY) > 400 ? 'AAAH!' : 'Whoa!');

            // Start physics
            let px = parseFloat(cat.style.left);
            let py = parseFloat(cat.style.top);
            let lastTime = performance.now();
            let bounceCount = 0;
            let settled = false;

            function physicsStep(now) {
                const dt = Math.min((now - lastTime) / 1000, 0.05);
                lastTime = now;

                // Apply gravity
                velY += GRAVITY * dt;

                // Apply air friction to horizontal
                velX *= (1 - (1 - FRICTION) * dt * 10);

                px += velX * dt;
                py += velY * dt;

                const vw = window.innerWidth;
                const vh = window.innerHeight;

                // Floor collision
                if (py > vh - 48) {
                    py = vh - 48;
                    velY = -velY * BOUNCE;
                    velX *= FRICTION;
                    bounceCount++;
                    if (Math.abs(velY) < 60) velY = 0;
                }

                // Wall collisions
                if (px < 0) { px = 0; velX = -velX * BOUNCE; }
                if (px > vw - CAT_W) { px = vw - CAT_W; velX = -velX * BOUNCE; }

                // Ceiling
                if (py < 0) { py = 0; velY = -velY * BOUNCE; }

                cat.style.left = px + 'px';
                cat.style.top = py + 'px';

                // Spin cat based on velocity
                const speed = Math.sqrt(velX * velX + velY * velY);
                if (speed > 200) {
                    const angle = Math.atan2(velY, velX) * (180 / Math.PI);
                    cat.style.transform = 'rotate(' + angle + 'deg)';
                } else {
                    cat.style.transform = '';
                }

                // Check if settled
                if (Math.abs(velX) < MIN_VEL && Math.abs(velY) < MIN_VEL && py >= vh - 50) {
                    settled = true;
                }

                // Check if cat lands on the card top edge
                const card = document.getElementById('login-card');
                if (card) {
                    const cardRect = card.getBoundingClientRect();
                    const catCenterX = px + CAT_W / 2;
                    const catBottom = py + 48;

                    // Landing on top of card
                    if (catBottom >= cardRect.top - 5 && catBottom <= cardRect.top + 15 &&
                        catCenterX > cardRect.left && catCenterX < cardRect.right &&
                        velY > 0) {
                        // Snap back to top track!
                        returnToTrack();
                        return;
                    }
                }

                if (settled) {
                    // Cat sits on floor, then after a moment returns to card
                    cat.style.transform = '';
                    showSpeech('*thud*');
                    setTimeout(() => {
                        showSpeech('hmm...');
                        setTimeout(() => returnToTrack(), 2000);
                    }, 1500);
                    return;
                }

                physicsFrame = requestAnimationFrame(physicsStep);
            }

            physicsFrame = requestAnimationFrame(physicsStep);
        }

        function returnToTrack() {
            if (physicsFrame) { cancelAnimationFrame(physicsFrame); physicsFrame = null; }

            cat.classList.remove('free-fall', 'dragging');
            cat.style.position = 'absolute';
            cat.style.top = '';
            cat.style.bottom = '0';
            cat.style.transition = 'none';
            cat.style.transform = '';

            // Return to whichever track cat was on
            const parentTrack = isOnBottom ? bottomTrack : track;
            parentTrack.appendChild(cat);
            parentTrack.appendChild(fly);

            trackW = track.offsetWidth;
            setX(Math.random() * (trackW - CAT_W));
            setFacing(false);
            showSpeech('Made it!');
            stateTimeout = setTimeout(() => nextBehavior(), 1500);
        }

        // Mouse events
        cat.addEventListener('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            startDrag(e.clientX, e.clientY);
        });
        document.addEventListener('mousemove', function(e) {
            moveDrag(e.clientX, e.clientY);
        });
        document.addEventListener('mouseup', function(e) {
            if (!isDragging) return;
            if (!dragMoved) {
                // It was a click, not a drag — show speech
                isDragging = false;
                cat.classList.remove('dragging');
                // Return cat to track position
                cat.style.position = 'absolute';
                cat.style.top = '';
                cat.style.bottom = '0';
                const parentTrack = isOnBottom ? bottomTrack : track;
                parentTrack.appendChild(cat);
                parentTrack.appendChild(fly);
                showSpeech(meows[Math.floor(Math.random() * meows.length)]);
                return;
            }
            endDrag();
        });

        // Touch events
        cat.addEventListener('touchstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const t = e.touches[0];
            startDrag(t.clientX, t.clientY);
        }, { passive: false });
        document.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
            const t = e.touches[0];
            moveDrag(t.clientX, t.clientY);
        }, { passive: false });
        document.addEventListener('touchend', function(e) {
            if (!isDragging) return;
            if (!dragMoved) {
                isDragging = false;
                cat.classList.remove('dragging');
                cat.style.position = 'absolute';
                cat.style.top = '';
                cat.style.bottom = '0';
                const parentTrack = isOnBottom ? bottomTrack : track;
                parentTrack.appendChild(cat);
                parentTrack.appendChild(fly);
                showSpeech(meows[Math.floor(Math.random() * meows.length)]);
                return;
            }
            endDrag();
        });

        // Start
        setX(x);
        nextBehavior();
    });
    </script>
</div>
