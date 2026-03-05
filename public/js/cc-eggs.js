/**
 * Computer Cat — Easter Eggs
 */
(function() {
    'use strict';

    // -- 1. KONAMI CODE → Matrix cat rain --
    const KONAMI = [38,38,40,40,37,39,37,39,66,65];
    let konamiPos = 0;

    document.addEventListener('keydown', function(e) {
        if (e.keyCode === KONAMI[konamiPos]) {
            konamiPos++;
            if (konamiPos === KONAMI.length) {
                konamiPos = 0;
                catMatrix();
            }
        } else {
            konamiPos = 0;
        }

        // Track typed chars for "meow" detector
        typedChars += e.key;
        if (typedChars.length > 20) typedChars = typedChars.slice(-20);
        if (typedChars.toLowerCase().endsWith('meow')) {
            typedChars = '';
            summonCat();
        }
        if (typedChars.toLowerCase().endsWith('nyan')) {
            typedChars = '';
            nyanMode();
        }
    });

    function catMatrix() {
        const canvas = document.createElement('canvas');
        canvas.style.cssText = 'position:fixed;inset:0;z-index:99999;pointer-events:none;';
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        document.body.appendChild(canvas);
        const ctx = canvas.getContext('2d');

        const cats = ['🐱','🐈','😺','😸','😹','😻','🙀','😾','🐈‍⬛','🐾'];
        const fontSize = 16;
        const cols = Math.floor(canvas.width / fontSize);
        const drops = Array(cols).fill(0);

        let frames = 0;
        function draw() {
            ctx.fillStyle = 'rgba(10,10,26,0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#00f0ff';
            ctx.font = fontSize + 'px monospace';

            for (let i = 0; i < cols; i++) {
                const char = cats[Math.floor(Math.random() * cats.length)];
                ctx.fillText(char, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }

            frames++;
            if (frames < 200) {
                requestAnimationFrame(draw);
            } else {
                canvas.style.transition = 'opacity 1s';
                canvas.style.opacity = '0';
                setTimeout(() => canvas.remove(), 1000);
            }
        }
        draw();
    }

    // -- 2. TYPE "MEOW" → Cat walks across screen --
    let typedChars = '';

    function summonCat() {
        const cat = document.createElement('div');
        cat.innerHTML = '🐈';
        cat.style.cssText = 'position:fixed;bottom:0;font-size:48px;z-index:99999;pointer-events:none;transition:none;';
        const fromLeft = Math.random() > 0.5;
        cat.style[fromLeft ? 'left' : 'right'] = '-60px';
        if (!fromLeft) cat.style.transform = 'scaleX(-1)';
        document.body.appendChild(cat);

        const speech = document.createElement('div');
        speech.textContent = 'Meow!';
        speech.style.cssText = 'position:absolute;bottom:100%;left:50%;transform:translateX(-50%);background:#12122a;border:1px solid #00f0ff;color:#00f0ff;padding:4px 10px;font-family:"JetBrains Mono",monospace;font-size:12px;white-space:nowrap;border-radius:0;';
        cat.appendChild(speech);

        let pos = -60;
        const speed = 2;
        const target = window.innerWidth + 80;

        function step() {
            pos += speed;
            cat.style[fromLeft ? 'left' : 'right'] = pos + 'px';
            if (pos < target) {
                requestAnimationFrame(step);
            } else {
                cat.remove();
            }
        }
        requestAnimationFrame(step);
    }

    // -- 3. TYPE "NYAN" → Rainbow trail on cursor for 10 seconds --
    function nyanMode() {
        const colors = ['#ff0000','#ff8800','#ffff00','#00ff00','#0088ff','#8800ff'];
        let active = true;

        function trail(e) {
            if (!active) return;
            const dot = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            dot.style.cssText = `position:fixed;left:${e.clientX}px;top:${e.clientY}px;width:8px;height:8px;background:${color};pointer-events:none;z-index:99998;box-shadow:0 0 6px ${color};transition:all 0.8s;`;
            document.body.appendChild(dot);
            requestAnimationFrame(() => {
                dot.style.opacity = '0';
                dot.style.transform = 'scale(0) translateY(20px)';
            });
            setTimeout(() => dot.remove(), 800);
        }

        document.addEventListener('mousemove', trail);
        setTimeout(() => {
            active = false;
            document.removeEventListener('mousemove', trail);
        }, 10000);
    }

    // -- 4. CLICK BRAND 7 TIMES → Hacker mode glitch --
    let brandClicks = 0;
    let brandTimer = null;

    document.addEventListener('click', function(e) {
        const brand = e.target.closest('.fi-sidebar-header a, .fi-topbar a[href*="admin"]');
        if (!brand) { brandClicks = 0; return; }

        brandClicks++;
        clearTimeout(brandTimer);
        brandTimer = setTimeout(() => brandClicks = 0, 2000);

        if (brandClicks >= 7) {
            brandClicks = 0;
            hackerMode();
        }
    });

    function hackerMode() {
        // Glitch effect
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;pointer-events:none;';
        document.body.appendChild(overlay);

        // Rapid glitch flashes
        let flashes = 0;
        const glitchInterval = setInterval(() => {
            overlay.style.background = flashes % 2 === 0
                ? 'rgba(0,240,255,0.1)'
                : 'rgba(255,0,170,0.1)';
            document.body.style.transform = flashes % 2 === 0
                ? 'translateX(2px)' : 'translateX(-2px)';
            flashes++;
            if (flashes > 10) {
                clearInterval(glitchInterval);
                document.body.style.transform = '';
                overlay.style.background = 'transparent';

                // Show ACCESS GRANTED
                const msg = document.createElement('div');
                msg.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:99999;font-family:"Press Start 2P",monospace;font-size:1.2rem;color:#39ff14;text-shadow:0 0 10px #39ff14,0 0 30px rgba(57,255,20,0.3);text-align:center;pointer-events:none;';
                msg.innerHTML = '> ACCESS_GRANTED<br><span style="font-size:0.6rem;color:#00f0ff;">welcome back, operator</span>';
                document.body.appendChild(msg);

                setTimeout(() => {
                    msg.style.transition = 'opacity 1s';
                    msg.style.opacity = '0';
                    setTimeout(() => { msg.remove(); overlay.remove(); }, 1000);
                }, 2000);
            }
        }, 80);
    }

    // -- 5. IDLE CAT → After 60s of no interaction, cat peeks from sidebar --
    let idleTimer = null;
    let idleCatShown = false;

    function resetIdle() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(showIdleCat, 60000);
    }

    function showIdleCat() {
        if (idleCatShown) return;
        idleCatShown = true;

        const peek = document.createElement('div');
        peek.innerHTML = '😺';
        peek.style.cssText = 'position:fixed;bottom:-50px;right:20px;font-size:48px;z-index:99999;cursor:pointer;transition:bottom 0.5s cubic-bezier(0.34,1.56,0.64,1);';
        document.body.appendChild(peek);

        setTimeout(() => peek.style.bottom = '-10px', 100);

        const bubble = document.createElement('div');
        bubble.textContent = 'Still there?';
        bubble.style.cssText = 'position:absolute;bottom:100%;right:0;background:#12122a;border:1px solid #00f0ff;color:#00f0ff;padding:4px 10px;font-family:"JetBrains Mono",monospace;font-size:11px;white-space:nowrap;opacity:0;transition:opacity 0.3s;';
        peek.appendChild(bubble);
        setTimeout(() => bubble.style.opacity = '1', 600);

        peek.addEventListener('click', function() {
            bubble.textContent = '*purr*';
            setTimeout(() => {
                peek.style.bottom = '-60px';
                setTimeout(() => { peek.remove(); idleCatShown = false; resetIdle(); }, 500);
            }, 1000);
        });

        // Auto-hide after 10s
        setTimeout(() => {
            if (peek.parentNode) {
                peek.style.bottom = '-60px';
                setTimeout(() => { peek.remove(); idleCatShown = false; resetIdle(); }, 500);
            }
        }, 10000);
    }

    ['mousemove','keydown','click','scroll'].forEach(e => document.addEventListener(e, function() {
        idleCatShown = false;
        resetIdle();
    }));
    resetIdle();

})();
