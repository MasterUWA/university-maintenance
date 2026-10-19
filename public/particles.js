(function () {
    var canvas = document.getElementById('particles-bg');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var mouse = { x: null, y: null };
    var drag = { node: null, lastX: 0, lastY: 0, vx: 0, vy: 0 };
    var particles = [];
    var cfg;
    var width;
    var height;

    function resize() {
        var ratio = window.devicePixelRatio || 1;
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = width * ratio;
        canvas.height = height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    }

    function rand(min, max) {
        return Math.random() * (max - min) + min;
    }

    function create() {
        var count = Math.round(cfg.count * Math.min(1, (width * height) / 1000000 + 0.3));
        particles = [];
        for (var i = 0; i < count; i++) {
            var angle = Math.random() * Math.PI * 2;
            particles.push({
                x: rand(0, width),
                y: rand(0, height),
                vx: Math.cos(angle) * cfg.speed * rand(0.4, 1),
                vy: Math.sin(angle) * cfg.speed * rand(0.4, 1),
                r: rand(cfg.size.min, cfg.size.max),
                c: cfg.colors[Math.floor(Math.random() * cfg.colors.length)]
            });
        }
    }

    function step() {
        ctx.clearRect(0, 0, width, height);
        var maxDist = cfg.linkDistance;

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];

            if (p === drag.node) {
                p.x = mouse.x;
                p.y = mouse.y;
            } else {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0 || p.x > width) p.vx *= -1;
                if (p.y < 0 || p.y > height) p.vy *= -1;

                if (drag.node) {
                    // Linked neighbours are pulled along with the dragged node
                    var gx = drag.node.x - p.x;
                    var gy = drag.node.y - p.y;
                    var gd = Math.sqrt(gx * gx + gy * gy);
                    if (gd < maxDist && gd > 1) {
                        var pull = (1 - gd / maxDist) * 0.02;
                        p.x += gx * pull;
                        p.y += gy * pull;
                    }
                } else if (mouse.x !== null) {
                    var dx = p.x - mouse.x;
                    var dy = p.y - mouse.y;
                    var d = Math.sqrt(dx * dx + dy * dy);
                    if (d < cfg.interaction.radius && d > 0) {
                        var force = (1 - d / cfg.interaction.radius) * cfg.interaction.push;
                        p.x += (dx / d) * force;
                        p.y += (dy / d) * force;
                    }
                }
            }

            ctx.globalAlpha = cfg.opacity;
            ctx.fillStyle = p.c;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();

            for (var j = i + 1; j < particles.length; j++) {
                var q = particles[j];
                var lx = p.x - q.x;
                var ly = p.y - q.y;
                var dist = Math.sqrt(lx * lx + ly * ly);
                if (dist < maxDist) {
                    ctx.globalAlpha = cfg.linkOpacity * (1 - dist / maxDist);
                    ctx.strokeStyle = 'rgb(' + cfg.linkColor + ')';
                    ctx.lineWidth = cfg.linkWidth;
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(q.x, q.y);
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(step);
    }

    function nodeAt(x, y) {
        var best = null;
        var bestDist = 14;
        for (var i = 0; i < particles.length; i++) {
            var d = Math.hypot(particles[i].x - x, particles[i].y - y) - particles[i].r;
            if (d < bestDist) {
                bestDist = d;
                best = particles[i];
            }
        }
        return best;
    }

    function isInteractive(el) {
        return el && el.closest && el.closest('a,button,input,select,textarea,label,summary,[role=button]');
    }

    window.addEventListener('pointerdown', function (e) {
        if (!cfg || isInteractive(e.target)) return;
        var node = nodeAt(e.clientX, e.clientY);
        if (!node) return;
        drag.node = node;
        drag.lastX = e.clientX;
        drag.lastY = e.clientY;
        drag.vx = 0;
        drag.vy = 0;
        mouse.x = e.clientX;
        mouse.y = e.clientY;
        e.preventDefault();
    });

    function release() {
        if (!drag.node) return;
        drag.node.vx = Math.max(-4, Math.min(4, drag.vx));
        drag.node.vy = Math.max(-4, Math.min(4, drag.vy));
        drag.node = null;
    }

    window.addEventListener('pointerup', release);
    window.addEventListener('pointercancel', release);

    window.addEventListener('resize', function () {
        resize();
        if (cfg) create();
    });
    window.addEventListener('pointermove', function (e) {
        if (drag.node) {
            drag.vx = (e.clientX - drag.lastX) * 0.5;
            drag.vy = (e.clientY - drag.lastY) * 0.5;
            drag.lastX = e.clientX;
            drag.lastY = e.clientY;
        } else if (cfg && !isInteractive(e.target)) {
            document.body.style.cursor = nodeAt(e.clientX, e.clientY) ? 'grab' : '';
        }
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });
    window.addEventListener('mouseout', function (e) {
        if (e.relatedTarget || drag.node) return;
        mouse.x = null;
        mouse.y = null;
    });

    resize();
    fetch(canvas.dataset.config)
        .then(function (response) { return response.json(); })
        .then(function (config) {
            cfg = config;
            create();
            step();
        });
})();
