<?php
http_response_code(404);
header('Cache-Control: no-store, no-cache, must-revalidate');
$refId = 'ERR-404-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
$path  = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
$short = htmlspecialchars(mb_substr($_SERVER['REQUEST_URI'] ?? '/', 0, 24), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>404 — Page Not Found</title>
<meta name="description" content="The page you requested could not be found. Please check the address or contact your Class CR.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@600;700;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
:root{--bg:oklch(.16 .02 260);--fg:oklch(.97 .01 80);--card:oklch(.21 .025 260);--muted:oklch(.72 .02 260);--primary:oklch(.7 .19 35);--glow:oklch(.8 .16 65);--neon:oklch(.86 .14 200);--success:oklch(.8 .18 150);--accent:oklch(.3 .04 260);--border:oklch(1 0 0 / 10%);--grad:linear-gradient(135deg,var(--primary),var(--glow));--gtext:linear-gradient(180deg,var(--fg) 10%,var(--glow) 55%,var(--primary) 100%)}
*{box-sizing:border-box;margin:0;padding:0}
html{color-scheme:dark}
body{background:var(--bg);color:var(--fg);font-family:Manrope,system-ui,sans-serif;-webkit-font-smoothing:antialiased;min-height:100vh;overflow-x:hidden}
.mono{font-family:"JetBrains Mono",ui-monospace,monospace}
.display{font-family:Unbounded,system-ui,sans-serif}
main{position:relative;min-height:100vh;overflow:hidden;padding:2rem 1rem}
@media(min-width:640px){main{padding:3rem 2rem}}
.grid-bg{position:absolute;inset:0;pointer-events:none;background-image:linear-gradient(to right,color-mix(in oklab,var(--fg) 6%,transparent) 1px,transparent 1px),linear-gradient(to bottom,color-mix(in oklab,var(--fg) 6%,transparent) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse at center,#000 30%,transparent 75%);-webkit-mask-image:radial-gradient(ellipse at center,#000 30%,transparent 75%);animation:gridmove 20s linear infinite}
@keyframes gridmove{to{background-position:48px 48px}}
.scan{position:absolute;inset:0;pointer-events:none;background-image:repeating-linear-gradient(0deg,color-mix(in oklab,var(--fg) 4%,transparent) 0 1px,transparent 1px 3px)}
.orb{position:absolute;border-radius:50%;pointer-events:none;filter:blur(40px);animation:drift 14s ease-in-out infinite alternate}
@keyframes drift{to{transform:translate(-40px,30px) scale(1.1)}}
.wrap{position:relative;margin:0 auto;animation:rise .8s cubic-bezier(.2,.8,.2,1) both}
@keyframes rise{from{opacity:0;transform:translateY(24px)}}
.tg{background-image:var(--gtext);-webkit-background-clip:text;background-clip:text;color:transparent}
.muted{color:var(--muted)}.neon{color:var(--neon)}.pri{color:var(--primary)}.ok{color:var(--success)}
.panel{border:1px solid var(--border);background:color-mix(in oklab,var(--card) 60%,transparent);border-radius:1rem;backdrop-filter:blur(8px)}
.btns{display:flex;flex-direction:column;gap:.75rem;margin-top:1.5rem}
@media(min-width:640px){.btns{flex-direction:row}}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;height:3rem;padding:0 1.75rem;border-radius:999px;font:600 .95rem Manrope,sans-serif;cursor:pointer;text-decoration:none;border:1px solid var(--border);transition:transform .2s,box-shadow .2s,border-color .2s;position:relative;overflow:hidden}
.btn svg{width:18px;height:18px}
.btn-hero{background:var(--grad);color:var(--bg);border:0;box-shadow:0 12px 40px -10px color-mix(in oklab,var(--primary) 60%,transparent)}
.btn-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(110deg,transparent 30%,oklch(1 0 0 / 35%) 50%,transparent 70%);transform:translateX(-100%);animation:shine 3s infinite}
@keyframes shine{60%,100%{transform:translateX(100%)}}
.btn-hero:hover{transform:translateY(-2px)}
.btn-glass{background:color-mix(in oklab,var(--fg) 5%,transparent);color:var(--fg);backdrop-filter:blur(10px)}
.btn-glass:hover{border-color:var(--neon)}
.label{display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;font-size:.72rem;letter-spacing:.15em;text-transform:uppercase;color:var(--muted)}
.label svg{width:16px;height:16px;color:var(--neon)}
.cr{display:flex;flex-direction:column;gap:1rem;padding:1.25rem;margin-top:1.5rem}
@media(min-width:640px){.cr.row{flex-direction:row;align-items:center;justify-content:space-between}}
.cr-in{display:flex;gap:.75rem;align-items:flex-start}
.av{display:grid;place-items:center;width:40px;height:40px;flex-shrink:0;border-radius:.75rem;background:var(--grad);color:var(--bg)}
.av svg{width:20px;height:20px}
.cr b{color:var(--fg)}
.copy{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.5rem .75rem;border:1px solid var(--border);border-radius:.5rem;background:color-mix(in oklab,var(--bg) 60%,transparent);color:var(--fg);font-size:.75rem;cursor:pointer}
.copy:hover{border-color:var(--neon)}
.copy svg{width:14px;height:14px}
kbd{border:1px solid var(--border);border-radius:4px;padding:0 4px}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}

.wrap{max-width:64rem}
header{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:2rem;font-size:.75rem;color:var(--muted)}
header span{display:flex;align-items:center;gap:.5rem}
header svg{width:16px;height:16px;color:var(--neon)}
.pill{border:1px solid var(--border);border-radius:999px;padding:.25rem .75rem}
.hero{display:grid;gap:2rem;align-items:end}
@media(min-width:768px){.hero{grid-template-columns:1.1fr 1fr}}
h1{font-size:clamp(4.5rem,16vw,8rem);font-weight:900;line-height:1;letter-spacing:-.02em;margin-top:.5rem}
h1 span{display:inline-block;animation:float 3s ease-in-out infinite}
h1 span:nth-child(2){animation-delay:.3s}h1 span:nth-child(3){animation-delay:.6s}
@keyframes float{50%{transform:translateY(-8px)}}
.glitch{position:relative}
.glitch::before,.glitch::after{content:"404";position:absolute;inset:0;opacity:.6;pointer-events:none}
.glitch::before{color:var(--neon);animation:g1 3.5s infinite steps(1)}
.glitch::after{color:var(--primary);animation:g2 3.5s infinite steps(1)}
@keyframes g1{0%,90%,100%{clip-path:inset(0 0 100% 0)}92%{clip-path:inset(10% 0 60% 0);transform:translate(-4px,0)}95%{clip-path:inset(60% 0 10% 0);transform:translate(4px,0)}}
@keyframes g2{0%,91%,100%{clip-path:inset(0 0 100% 0)}93%{clip-path:inset(40% 0 30% 0);transform:translate(4px,0)}96%{clip-path:inset(5% 0 80% 0);transform:translate(-4px,0)}}
h2{font-size:clamp(1.5rem,4vw,1.9rem);font-weight:700;margin-top:1rem}
.lead{margin-top:.75rem;max-width:28rem;color:var(--muted);line-height:1.6}
.term{font-size:.8rem;box-shadow:0 30px 80px -30px oklch(0 0 0 / 60%)}
@media(min-width:640px){.term{font-size:.875rem}}
.dots{display:flex;gap:6px;padding:.75rem 1rem;border-bottom:1px solid var(--border)}
.dots i{width:12px;height:12px;border-radius:50%}
.tbody{padding:1rem;display:flex;flex-direction:column;gap:.5rem;min-height:9.5rem}
.tbody p{word-break:break-all}
.tbody .p{color:var(--neon);margin-right:.5rem}
.cursor{animation:blink 1s steps(2) infinite}@keyframes blink{50%{opacity:0}}
.game{position:relative;overflow:hidden;margin-top:0}
.gbar{display:flex;justify-content:space-between;gap:.5rem;padding:.5rem 1rem;border-bottom:1px solid var(--border);font-size:.72rem;color:var(--muted)}
canvas{display:block;width:100%;cursor:pointer;touch-action:none;user-select:none}
.ov{position:absolute;left:0;right:0;bottom:0;top:2.2rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.25rem;text-align:center;background:color-mix(in oklab,var(--bg) 50%,transparent);backdrop-filter:blur(2px);pointer-events:none;padding:0 1rem}
.ov.hide{display:none}
section.g{margin-top:2.5rem}
</style>
</head>
<body>
<main>
  <div class="grid-bg"></div><div class="scan"></div>
  <div class="orb" style="top:-10rem;left:50%;width:40rem;height:24rem;margin-left:-20rem;background:color-mix(in oklab,var(--neon) 15%,transparent)"></div>
  <div class="wrap">
    <header class="mono">
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 17l6-6-6-6M12 19h8"/></svg> cst@campus:~</span>
      <span class="pill">status: <b class="pri">404</b></span>
    </header>

    <section class="hero">
      <div>
        <p class="mono neon" style="font-size:.875rem">// route_not_found</p>
        <h1 class="display glitch"><span>4</span><span class="tg">0</span><span>4</span></h1>
        <h2 class="display">Page not found</h2>
        <p class="lead">The page you requested does not exist on this server. Check the address, head back home, or beat a high score while you're here.</p>
        <div class="btns">
          <a href="/" class="btn btn-hero"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10l9-7 9 7v10a2 2 0 0 1-2 2h-4v-7H9v7H5a2 2 0 0 1-2-2z"/></svg> Go to homepage</a>
          <button type="button" class="btn btn-glass" onclick="history.back()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Go back</button>
        </div>
      </div>
      <div class="panel term mono">
        <div class="dots"><i style="background:var(--primary)"></i><i style="background:var(--glow)"></i><i style="background:var(--success)"></i></div>
        <div class="tbody" id="tbody"></div>
      </div>
    </section>

    <section class="g">
      <p class="label mono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 12h4M8 10v4M15 13h.01M18 11h.01"/><rect x="2" y="6" width="20" height="12" rx="4"/></svg> Mini game · Bug Runner</p>
      <div class="panel game">
        <div class="gbar mono"><span>~/game/bug_runner.exe</span><span>SCORE <b class="neon" id="sc">00000</b> · HI <span id="hi">00000</span></span></div>
        <canvas id="cv" width="720" height="200" aria-label="Bug runner mini game"></canvas>
        <div class="ov mono" id="ov">
          <p id="ovT" style="font-size:.875rem;font-weight:600">&gt; Dodge the bugs while you're here</p>
          <p class="muted" style="font-size:.75rem">Press <kbd>Space</kbd> or tap to <span id="ovA">start</span></p>
        </div>
      </div>
    </section>

    <section class="panel cr row">
      <div class="cr-in">
        <span class="av"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg></span>
        <div><p style="font-weight:600">Need assistance?</p>
        <p class="muted" style="font-size:.875rem">If a link sent you here, please contact your <b>Class CR</b> and share the reference ID.</p></div>
      </div>
      <button type="button" class="copy mono" id="copy" data-text="<?= $refId ?> — <?= $path ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg> <span id="copyLbl"><?= $refId ?></span></button>
    </section>
  </div>
</main>
<script>
// typed terminal
const lines=[["$","curl -I <?= $path ?>",""],[">","HTTP/1.1 404 Not Found","pri"],["$","grep -r \"<?= $short ?>\" ./routes",""],[">","0 matches found. The page may be moved, renamed or removed.","muted"]];
const tb=document.getElementById('tbody');
(async()=>{for(const [p,t,c] of lines){const el=document.createElement('p');el.className=c;el.innerHTML='<span class="p">'+p+'</span>';const s=document.createElement('span');el.appendChild(s);tb.appendChild(el);for(const ch of t){s.textContent+=ch;await new Promise(r=>setTimeout(r,p==='$'?28:8))}await new Promise(r=>setTimeout(r,250))}const c=document.createElement('p');c.innerHTML='<span class="p">$</span><span class="cursor">▋</span>';tb.appendChild(c)})();

const copyBtn=document.getElementById('copy');
copyBtn.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(copyBtn.dataset.text);const l=document.getElementById('copyLbl'),o=l.textContent;l.textContent='Copied!';setTimeout(()=>l.textContent=o,1800)}catch(e){}});

// Bug Runner
(()=>{
const c=document.getElementById('cv'),ctx=c.getContext('2d'),W=720,H=200,G=170;
const ov=document.getElementById('ov'),ovT=document.getElementById('ovT'),ovA=document.getElementById('ovA'),sc=document.getElementById('sc'),hiEl=document.getElementById('hi');
const css=getComputedStyle(document.documentElement),col=v=>css.getPropertyValue(v).trim();
const pad=n=>String(n).padStart(5,'0');
let best=+(localStorage.getItem('cst-runner-best')||0);hiEl.textContent=pad(best);
const s={y:0,vy:0,obs:[],speed:5,t:0,score:0,state:'idle'};
let actx;const beep=(f,d)=>{try{actx=actx||new (window.AudioContext||window.webkitAudioContext)();const o=actx.createOscillator(),g=actx.createGain();o.type='square';o.frequency.value=f;g.gain.value=.04;o.connect(g);g.connect(actx.destination);o.start();g.gain.exponentialRampToValueAtTime(.0001,actx.currentTime+d);o.stop(actx.currentTime+d)}catch(e){}};
function jump(){if(s.state!=='run'){Object.assign(s,{y:0,vy:0,obs:[],speed:5,t:0,score:0,state:'run'});ov.classList.add('hide');return}if(s.y===0){s.vy=11;beep(660,.08)}}
addEventListener('keydown',e=>{if(e.code==='Space'||e.code==='ArrowUp'){e.preventDefault();jump()}});
c.addEventListener('pointerdown',jump);
function over(){s.state='over';beep(140,.3);best=Math.max(best,Math.floor(s.score));localStorage.setItem('cst-runner-best',best);hiEl.textContent=pad(best);ovT.textContent='> SEGMENTATION FAULT — you hit a bug';ovA.textContent='restart';ov.classList.remove('hide')}
function loop(){
 if(s.state==='run'){s.t++;s.vy-=.6;s.y=Math.max(0,s.y+s.vy);if(s.y===0)s.vy=0;s.speed+=.002;
  const last=s.obs[s.obs.length-1];if(!last||last.x<W-220-Math.random()*260)s.obs.push({x:W+20,w:14+Math.random()*16,h:22+Math.random()*28});
  s.obs.forEach(o=>o.x-=s.speed);s.obs=s.obs.filter(o=>o.x+o.w>0);s.score+=.15;
  if(s.t%6===0)sc.textContent=pad(Math.floor(s.score));
  if(Math.floor(s.score)%100===0&&s.t%6===0&&s.score>1)beep(990,.1);
  const py=G-30-s.y;for(const o of s.obs)if(96>o.x+3&&70<o.x+o.w-3&&py+30>G-o.h+3){over();break}}
 ctx.clearRect(0,0,W,H);ctx.strokeStyle=col('--border');ctx.lineWidth=1;
 const off=(s.t*s.speed)%40;for(let x=-off;x<W;x+=40){ctx.beginPath();ctx.moveTo(x,G);ctx.lineTo(x-30,H);ctx.stroke()}
 ctx.strokeStyle=col('--neon');ctx.lineWidth=2;ctx.beginPath();ctx.moveTo(0,G);ctx.lineTo(W,G);ctx.stroke();
 const py=G-30-s.y;ctx.shadowColor=col('--neon');ctx.shadowBlur=14;ctx.fillStyle=col('--neon');
 ctx.fillRect(70,py,26,22);const leg=s.state==='run'&&s.y===0&&(s.t>>2)%2;ctx.fillRect(74,py+22,6,leg?6:8);ctx.fillRect(86,py+22,6,leg?8:6);
 ctx.fillStyle=col('--bg');ctx.fillRect(84,py+6,8,5);
 ctx.shadowColor=col('--primary');ctx.fillStyle=col('--primary');
 for(const o of s.obs){ctx.fillRect(o.x,G-o.h,o.w,o.h);ctx.fillStyle=col('--bg');ctx.fillRect(o.x+3,G-o.h+4,3,3);ctx.fillRect(o.x+o.w-6,G-o.h+4,3,3);ctx.fillStyle=col('--primary')}
 ctx.shadowBlur=0;requestAnimationFrame(loop)}
loop();
})();
</script>
</body>
</html>
