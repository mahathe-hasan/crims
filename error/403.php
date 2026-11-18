<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden | CRIMS Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #020617; color: #f8fafc;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; position: relative;
        }
        .bg-huge-text {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 24vw; font-weight: 800; color: rgba(255, 255, 255, 0.02);
            z-index: 1; user-select: none; pointer-events: none; white-space: nowrap;
            animation: watermarkGlow 5s ease-in-out infinite alternate;
        }
        @keyframes watermarkGlow {
            0% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
            100% { opacity: 1; transform: translate(-50%, -50%) scale(1.03); }
        }
        .ambient-glow { position: absolute; width: 100%; height: 100%; overflow: hidden; z-index: 1; pointer-events: none; }
        .glow-orb {
            position: absolute; width: 450px; height: 450px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0) 70%);
            border-radius: 50%; animation: floatOrb 10s ease-in-out infinite alternate;
        }
        .orb-1 { top: -10%; left: 10%; }
        .orb-2 { bottom: -10%; right: 10%; animation-delay: -5s; }
        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.2); }
        }
        .enterprise-card {
            position: relative; z-index: 2; background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 28px;
            padding: 45px 35px; width: 90%; max-width: 500px; text-align: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: cardFloat 4s ease-in-out infinite;
        }
        @keyframes cardFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .system-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25);
            padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600;
            color: #f87171; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 20px;
        }
        .system-badge .pulse-dot {
            width: 6px; height: 6px; background-color: #f87171; border-radius: 50%;
            box-shadow: 0 0 8px #f87171; animation: pulseAnim 2s infinite;
        }
        @keyframes pulseAnim {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }
        .error-code {
            font-size: 5.5rem; font-weight: 800; color: #ef4444;
            text-shadow: 0 0 25px rgba(239, 68, 68, 0.4);
            line-height: 1; margin-bottom: 10px; letter-spacing: -3px;
            animation: codeGlow 3s ease-in-out infinite alternate;
        }
        @keyframes codeGlow {
            0% { text-shadow: 0 0 15px rgba(239, 68, 68, 0.3); }
            100% { text-shadow: 0 0 35px rgba(239, 68, 68, 0.6); }
        }
        .error-title { font-size: 1.4rem; font-weight: 700; color: #ffffff; margin-bottom: 8px; }
        .error-subtitle { font-size: 0.85rem; color: #94a3b8; margin-bottom: 30px; }
        .cr-notice-box {
            background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(239, 68, 68, 0.2);
            border-left: 3px solid #ef4444; border-radius: 14px; padding: 16px 20px;
            display: flex; align-items: center; gap: 16px; text-align: left;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .cr-notice-box i {
            font-size: 1.3rem; color: #f87171; background: rgba(239, 68, 68, 0.1);
            padding: 12px; border-radius: 10px; flex-shrink: 0;
            animation: iconBounce 2s ease-in-out infinite;
        }
        @keyframes iconBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        .cr-notice-box p { margin: 0; font-size: 0.82rem; font-weight: 500; color: #cbd5e1; line-height: 1.5; }
        .cr-notice-box strong { color: #ffffff; font-weight: 600; }
        .error-footer { margin-top: 25px; font-size: 0.72rem; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase; }
        @media (max-width: 576px) {
            .enterprise-card { padding: 35px 20px; }
            .error-code { font-size: 4.5rem; }
            .error-title { font-size: 1.25rem; }
            .cr-notice-box { padding: 14px; }
        }
    </style>
</head>
<body>
    <div class="bg-huge-text">CRIMS</div>
    <div class="ambient-glow">
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
    </div>
    <div class="enterprise-card">
        <div class="system-badge">
            <span class="pulse-dot"></span>
            CRIMS Secure Gateway
        </div>
        <div class="error-code">403</div>
        <h1 class="error-title">Access Forbidden</h1>
        <div class="error-subtitle">You do not have permission to view this directory or resource.</div>
        <div class="cr-notice-box">
            <i class="fa-solid fa-headset"></i>
            <div class="cr-text">
                <p>Please contact your <strong>CR </strong> regarding the issue.</p>
            </div>
        </div>
        <div class="error-footer">Error Ref: ERR_FORBIDDEN_403</div>
    </div>
</body>
</html>