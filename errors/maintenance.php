<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - NOXARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0A0E1A;
            color: #FFFFFF;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }
        .container { max-width: 500px; }
        .robot-svg {
            width: 150px;
            height: 150px;
            margin: 0 auto 30px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        @keyframes blink {
            0%, 90%, 100% { opacity: 1; }
            95% { opacity: 0; }
        }
        h2 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .eta {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 10px;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.3);
            color: #00D4FF;
            font-weight: 600;
            font-size: 14px;
            margin-top: 20px;
        }
        .logo {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 3px;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="robot-svg">
            <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Robot head -->
                <rect x="30" y="30" width="60" height="50" rx="10" fill="#1A1F2E" stroke="#00D4FF" stroke-width="2"/>
                <!-- Eyes -->
                <circle cx="48" cy="52" r="6" fill="#00D4FF" style="animation: blink 3s infinite"/>
                <circle cx="72" cy="52" r="6" fill="#7B2FFF" style="animation: blink 3s infinite 0.5s"/>
                <!-- Mouth -->
                <rect x="44" y="66" width="32" height="4" rx="2" fill="#FFD700"/>
                <!-- Antenna -->
                <line x1="60" y1="30" x2="60" y2="18" stroke="#00D4FF" stroke-width="2"/>
                <circle cx="60" cy="15" r="4" fill="#00D4FF"/>
                <!-- Body -->
                <rect x="38" y="82" width="44" height="25" rx="6" fill="#1A1F2E" stroke="#7B2FFF" stroke-width="2"/>
                <!-- Wrench icon -->
                <path d="M54 90 L66 102 M66 90 L54 102" stroke="#FFD700" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </div>
        <h2>Sedang Dalam Perbaikan</h2>
        <p>Kami sedang melakukan peningkatan sistem untuk memberikan pengalaman yang lebih baik. Silakan kembali beberapa saat lagi.</p>
        <div class="eta">Estimasi selesai: 30 menit</div>
        <div class="logo">NOXARA</div>
    </div>
</body>
</html>
