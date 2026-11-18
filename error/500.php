<?php
/**
 * 500 Internal Server Error page
 * Single-file, self-contained. Drop it on any PHP server.
 * Apache:  ErrorDocument 500 /500.php
 */
http_response_code(500);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$refId = 'ERR-500-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
$time  = date('d M Y, H:i');
$path  = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>500 — Internal Server Error</title>
  <meta name="description" content="Something went wrong on our end. Please try again or contact your Class CR.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@600;700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: oklch(0.15 0.02 260);
      --card: oklch(0.2 0.025 260 / 0.72);
      --card-solid: oklch(0.2 0.025 260);
      --fg: oklch(0.97 0.01 80);
      --muted: oklch(0.72 0.02 260);
      --primary: oklch(0.7 0.19 35);
      --glow: oklch(0.82 0.15 70);
      --border: oklch(1 0 0 / 0.1);
      --gradient: linear-gradient(135deg, var(--primary), var(--glow));
      --radius: 28px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { min-height: 100%; }
    body {
      font-family: "Manrope", system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--fg);
      -webkit-font-smoothing: antialiased;
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 48px 20px;
      overflow-x: hidden;
    }

    /* ---------- Background ---------- */
    #stars { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
    .grid {
      position: fixed; inset: 0; z-index: 0; pointer-events: none;
      background-image:
        linear-gradient(to right, oklch(1 0 0 / 0.05) 1px, transparent 1px),
        linear-gradient(to bottom, oklch(1 0 0 / 0.05) 1px, transparent 1px);
      background-size: 52px 52px;
      mask-image: radial-gradient(ellipse at center, #000 25%, transparent 75%);
      -webkit-mask-image: radial-gradient(ellipse at center, #000 25%, transparent 75%);
      animation: gridMove 20s linear infinite;
    }
    .orb { position: fixed; border-radius: 50%; filter: blur(70px); z-index: 0; pointer-events: none; }
    .orb.one { width: 460px; height: 460px; left: -140px; top: -140px;
      background: radial-gradient(circle, oklch(0.7 0.19 35 / 0.55), transparent 70%);
      animation: drift 16s ease-in-out infinite; }
    .orb.two { width: 520px; height: 520px; right: -180px; bottom: -200px;
      background: radial-gradient(circle, oklch(0.82 0.15 70 / 0.35), transparent 70%);
      animation: drift 18s ease-in-out infinite reverse; }

    /* ---------- Card ---------- */
    .wrap { position: relative; z-index: 2; width: 100%; max-width: 880px; }
    .card {
      position: relative;
      border-radius: var(--radius);
      background: var(--card);
      backdrop-filter: blur(22px);
      -webkit-backdrop-filter: blur(22px);
      box-shadow: 0 40px 100px -30px oklch(0 0 0 / 0.7);
      overflow: hidden;
      animation: rise .9s cubic-bezier(.22,1,.36,1) both;
    }
    /* animated gradient border */
    .card::before {
      content: ""; position: absolute; inset: 0; border-radius: inherit; padding: 1px;
      background: conic-gradient(from var(--angle), transparent 0 60%, var(--primary), var(--glow), transparent 90%);
      -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
      -webkit-mask-composite: xor; mask-composite: exclude;
      animation: spin 6s linear infinite; pointer-events: none;
    }
    .card::after {
      content: ""; position: absolute; inset: 0; border-radius: inherit;
      border: 1px solid var(--border); pointer-events: none;
    }
    @property --angle { syntax: "<angle>"; initial-value: 0deg; inherits: false; }

    .topbar {
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
      padding: 14px 28px; border-bottom: 1px solid var(--border);
    }
    .notice { display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 700;
      letter-spacing: .18em; text-transform: uppercase; color: var(--muted); }
    .dot { position: relative; width: 8px; height: 8px; border-radius: 50%; background: var(--primary); }
    .dot::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background: var(--primary);
      animation: ping 1.6s cubic-bezier(0,0,.2,1) infinite; }
    .pill { font-size: 12px; font-weight: 700; color: var(--primary); padding: 5px 12px; border-radius: 999px;
      background: oklch(0.7 0.19 35 / 0.14); border: 1px solid oklch(0.7 0.19 35 / 0.3); }

    .hero {
      display: grid; grid-template-columns: auto 1fr; gap: 48px; align-items: center;
      padding: 44px 44px 40px;
    }
    .code {
      position: relative; font-family: "Unbounded", sans-serif; font-weight: 900;
      font-size: clamp(84px, 16vw, 132px); line-height: .9; letter-spacing: -0.05em;
      animation: float 6s ease-in-out infinite;
      user-select: none;
    }
    .code span {
      display: inline-block;
      background: linear-gradient(180deg, var(--fg) 5%, var(--glow) 55%, var(--primary) 100%);
      -webkit-background-clip: text; background-clip: text; color: transparent;
      animation: digit 4s ease-in-out infinite;
    }
    .code span:nth-child(2) { animation-delay: .25s; }
    .code span:nth-child(3) { animation-delay: .5s; }
    .code .glitch {
      position: absolute; inset: 0; pointer-events: none; color: var(--primary); opacity: 0;
      -webkit-text-fill-color: currentColor; mix-blend-mode: screen;
      animation: glitch 5s steps(1) infinite;
    }
    .code .glitch.b { color: var(--glow); animation-delay: .08s; }
    .shadow { height: 14px; margin: 18px auto 0; width: 70%; border-radius: 50%;
      background: radial-gradient(ellipse, oklch(0.7 0.19 35 / 0.45), transparent 70%);
      animation: shadow 6s ease-in-out infinite; }

    h1 { font-family: "Unbounded", sans-serif; font-weight: 700; font-size: clamp(24px, 3.4vw, 32px);
      letter-spacing: -0.02em; line-height: 1.15; text-wrap: balance; }
    .lead { margin-top: 14px; color: var(--muted); font-size: 16px; line-height: 1.7; max-width: 46ch; }
    .path { margin-top: 14px; display: inline-flex; gap: 8px; align-items: center; max-width: 100%;
      font-family: "JetBrains Mono", monospace; font-size: 12px; color: var(--muted);
      padding: 6px 12px; border-radius: 10px; background: oklch(0 0 0 / 0.25); border: 1px solid var(--border);
      overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .path b { color: var(--primary); font-weight: 700; }

    .actions { margin-top: 26px; display: flex; flex-wrap: wrap; gap: 12px; }
    .btn {
      position: relative; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
      height: 50px; padding: 0 26px; border-radius: 999px; font: 600 15px "Manrope", sans-serif;
      cursor: pointer; text-decoration: none; border: 1px solid transparent;
      transition: transform .3s cubic-bezier(.22,1,.36,1), box-shadow .3s, background .3s, filter .3s;
    }
    .btn svg { width: 18px; height: 18px; flex-shrink: 0; transition: transform .5s; }
    .btn-primary { background: var(--gradient); color: var(--bg);
      box-shadow: 0 14px 40px -12px oklch(0.7 0.19 35 / 0.75); }
    .btn-primary::after { content: ""; position: absolute; top: 0; left: -120%; width: 60%; height: 100%;
      background: linear-gradient(110deg, transparent, oklch(1 0 0 / 0.45), transparent);
      animation: shine 3.2s ease-in-out infinite; }
    .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.08);
      box-shadow: 0 20px 50px -12px oklch(0.7 0.19 35 / 0.9); }
    .btn-primary:hover svg { transform: rotate(180deg); }
    .btn-primary.loading svg { animation: spinIcon .8s linear infinite; }
    .btn-ghost { background: oklch(1 0 0 / 0.04); color: var(--fg); border-color: var(--border); }
    .btn-ghost:hover { transform: translateY(-2px); background: oklch(1 0 0 / 0.09); }
    .btn-ghost:hover svg { transform: translateX(-4px); }
    .btn:focus-visible { outline: 2px solid var(--glow); outline-offset: 3px; }

    .bottom { display: grid; grid-template-columns: 1fr 1fr; border-top: 1px solid var(--border); }
    .panel { padding: 30px 36px; }
    .panel + .panel { border-left: 1px solid var(--border); }
    .label { font-size: 12px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--muted); }
    .steps { list-style: none; margin-top: 18px; display: grid; gap: 14px; }
    .steps li { display: flex; gap: 12px; font-size: 14px; line-height: 1.6;
      opacity: 0; animation: slideIn .6s cubic-bezier(.22,1,.36,1) forwards; }
    .steps li:nth-child(1) { animation-delay: .7s; }
    .steps li:nth-child(2) { animation-delay: .85s; }
    .steps li:nth-child(3) { animation-delay: 1s; }
    .num { flex-shrink: 0; width: 24px; height: 24px; border-radius: 8px; display: grid; place-items: center;
      font: 700 11px "JetBrains Mono", monospace; color: var(--primary);
      background: oklch(0.7 0.19 35 / 0.14); border: 1px solid oklch(0.7 0.19 35 / 0.3); }
    .steps strong { color: var(--glow); }

    .cr { display: flex; gap: 16px; align-items: flex-start; }
    .avatar { position: relative; flex-shrink: 0; width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center;
      background: var(--gradient); color: var(--bg); box-shadow: 0 12px 30px -10px oklch(0.7 0.19 35 / 0.8); }
    .avatar::before { content: ""; position: absolute; inset: -6px; border-radius: 18px;
      border: 1px solid oklch(0.7 0.19 35 / 0.5); animation: pulseRing 2.4s ease-out infinite; }
    .avatar svg { width: 22px; height: 22px; }
    .cr h2 { font-family: "Unbounded", sans-serif; font-size: 17px; font-weight: 600; }
    .cr p { margin-top: 6px; color: var(--muted); font-size: 14px; line-height: 1.65; }
    .cr p b { color: var(--glow); }

    .meta { margin-top: 20px; display: grid; grid-template-columns: 1fr auto; gap: 14px; align-items: center;
      padding: 14px 16px; border-radius: 16px; background: oklch(0 0 0 / 0.25); border: 1px solid var(--border); }
    .meta dt { font-size: 11px; color: var(--muted); }
    .meta dd { margin-top: 4px; font: 700 13px "JetBrains Mono", monospace; }
    .copy { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 10px;
      font: 600 12px "Manrope", sans-serif; color: var(--fg); cursor: pointer;
      background: oklch(1 0 0 / 0.06); border: 1px solid var(--border); transition: background .25s, color .25s; }
    .copy:hover { background: oklch(1 0 0 / 0.12); }
    .copy.done { color: var(--glow); border-color: oklch(0.82 0.15 70 / 0.4); }
    .copy svg { width: 14px; height: 14px; }

    .foot { margin-top: 22px; text-align: center; font-size: 12px; color: var(--muted);
      animation: fade 1s 1.2s both; }

    /* ---------- Keyframes ---------- */
    @keyframes rise { from { opacity: 0; transform: translateY(30px) scale(.98); } to { opacity: 1; transform: none; } }
    @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
    @keyframes shadow { 0%,100% { transform: scaleX(1); opacity: .9; } 50% { transform: scaleX(.75); opacity: .5; } }
    @keyframes digit { 0%,100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-6px) rotate(-2deg); } }
    @keyframes glitch {
      0%, 92%, 100% { opacity: 0; transform: none; clip-path: inset(0 0 0 0); }
      93% { opacity: .8; transform: translate(-4px, 1px); clip-path: inset(10% 0 60% 0); }
      95% { opacity: .8; transform: translate(4px, -2px); clip-path: inset(55% 0 15% 0); }
      97% { opacity: .6; transform: translate(-2px, 0); clip-path: inset(30% 0 40% 0); }
    }
    @keyframes spin { to { --angle: 360deg; } }
    @keyframes spinIcon { to { transform: rotate(360deg); } }
    @keyframes ping { 75%,100% { transform: scale(2.6); opacity: 0; } }
    @keyframes pulseRing { 0% { transform: scale(.9); opacity: 1; } 100% { transform: scale(1.25); opacity: 0; } }
    @keyframes shine { 0% { left: -120%; } 60%,100% { left: 160%; } }
    @keyframes drift { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(40px,-30px) scale(1.1); } }
    @keyframes gridMove { to { background-position: 52px 52px; } }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-12px); } to { opacity: 1; transform: none; } }
    @keyframes fade { from { opacity: 0; } to { opacity: 1; } }

    /* ---------- Responsive ---------- */
    @media (max-width: 760px) {
      body { padding: 24px 14px; }
      .topbar { padding: 12px 18px; }
      .notice { font-size: 11px; letter-spacing: .14em; }
      .hero { grid-template-columns: 1fr; gap: 20px; padding: 32px 22px 28px; text-align: center; }
      .lead { margin-left: auto; margin-right: auto; font-size: 15px; }
      .actions { flex-direction: column; }
      .btn { width: 100%; }
      .bottom { grid-template-columns: 1fr; }
      .panel { padding: 24px 22px; }
      .panel + .panel { border-left: 0; border-top: 1px solid var(--border); }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { animation: none !important; transition: none !important; }
      .steps li { opacity: 1; }
    }
  </style>
</head>
<body>
  <canvas id="stars" aria-hidden="true"></canvas>
  <div class="grid" aria-hidden="true"></div>
  <div class="orb one" aria-hidden="true"></div>
  <div class="orb two" aria-hidden="true"></div>

  <main class="wrap">
    <section class="card" role="alert" aria-live="polite">
      <div class="topbar">
        <div class="notice"><span class="dot"></span> System Notice</div>
        <span class="pill">Status 500</span>
      </div>

      <div class="hero">
        <div>
          <div class="code" aria-label="Error 500">
            <span>5</span><span>0</span><span>0</span>
            <div class="glitch" aria-hidden="true">500</div>
            <div class="glitch b" aria-hidden="true">500</div>
          </div>
          <div class="shadow" aria-hidden="true"></div>
        </div>
        <div>
          <h1>Internal Server Error</h1>
          <p class="lead">We're sorry — the server encountered an unexpected condition and was unable to complete your request. Please try again shortly.</p>
          <div class="path"><b>GET</b> <?= $path ?></div>
          <div class="actions">
            <button class="btn btn-primary" id="retry" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg>
              Try again
            </button>
            <button class="btn btn-ghost" id="back" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
              Go back
            </button>
          </div>
        </div>
      </div>

      <div class="bottom">
        <div class="panel">
          <div class="label">What you can do</div>
          <ol class="steps">
            <li><span class="num">01</span><span>Refresh the page or try again in a few minutes.</span></li>
            <li><span class="num">02</span><span>Check your internet connection.</span></li>
            <li><span class="num">03</span><span>If the problem continues, report it to your <strong>Class CR</strong>.</span></li>
          </ol>
        </div>
        <div class="panel">
          <div class="cr">
            <div class="avatar">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
            </div>
            <div>
              <h2>Need assistance?</h2>
              <p>Please contact your <b>Class CR</b> (Class Representative) and share the reference ID below.</p>
            </div>
          </div>
          <dl class="meta">
            <div>
              <dt>Reference ID · <?= $time ?></dt>
              <dd id="ref"><?= $refId ?></dd>
            </div>
            <button class="copy" id="copy" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              <span>Copy</span>
            </button>
          </dl>
        </div>
      </div>
    </section>
    <p class="foot">Error code 500 · Internal Server Error</p>
  </main>

  <script>
    // Buttons
    var retry = document.getElementById('retry');
    retry.addEventListener('click', function () {
      retry.classList.add('loading');
      retry.lastChild.textContent = ' Retrying…';
      setTimeout(function () { location.reload(); }, 600);
    });
    document.getElementById('back').addEventListener('click', function () {
      if (history.length > 1) history.back(); else location.href = '/';
    });
    var copyBtn = document.getElementById('copy');
    copyBtn.addEventListener('click', function () {
      var text = document.getElementById('ref').textContent.trim();
      var done = function () {
        copyBtn.classList.add('done'); copyBtn.querySelector('span').textContent = 'Copied';
        setTimeout(function () { copyBtn.classList.remove('done'); copyBtn.querySelector('span').textContent = 'Copy'; }, 1800);
      };
      if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, done); else done();
    });

    // Floating particles
    (function () {
      var c = document.getElementById('stars'), ctx = c.getContext('2d');
      if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      var dpr = Math.min(window.devicePixelRatio || 1, 2), w, h, pts = [];
      function size() {
        w = c.width = innerWidth * dpr; h = c.height = innerHeight * dpr;
        c.style.width = innerWidth + 'px'; c.style.height = innerHeight + 'px';
        var n = Math.round((innerWidth * innerHeight) / 16000);
        pts = Array.from({ length: n }, function () {
          return { x: Math.random() * w, y: Math.random() * h, r: (Math.random() * 1.6 + .4) * dpr,
                   s: (Math.random() * .3 + .08) * dpr, a: Math.random() * Math.PI * 2, warm: Math.random() < .35 };
        });
      }
      function tick() {
        ctx.clearRect(0, 0, w, h);
        for (var i = 0; i < pts.length; i++) {
          var p = pts[i]; p.y -= p.s; p.a += .02;
          if (p.y < -10) { p.y = h + 10; p.x = Math.random() * w; }
          var o = .35 + Math.sin(p.a) * .3;
          ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
          ctx.fillStyle = p.warm ? 'rgba(255,160,90,' + o + ')' : 'rgba(255,240,225,' + o * .7 + ')';
          ctx.fill();
        }
        requestAnimationFrame(tick);
      }
      addEventListener('resize', size); size(); tick();
    })();
  </script>
</body>
</html>
