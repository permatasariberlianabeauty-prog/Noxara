<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Diblokir - NOXARA</title>
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
        .shield-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
        }
        h2 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            color: #FF4757;
        }
        p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .reason-box {
            padding: 16px 24px;
            border-radius: 10px;
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin: 24px 0;
            text-align: left;
        }
        .reason-box strong { color: #FF4757; }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 8px;
        }
        .btn-support {
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            color: #FFFFFF;
        }
        .btn-support:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
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
        <div class="shield-icon">
            <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M40 8L12 20v20c0 17.7 11.9 34.2 28 38 16.1-3.8 28-20.3 28-38V20L40 8z" fill="rgba(255,71,87,0.1)" stroke="#FF4757" stroke-width="2.5"/>
                <path d="M30 38L38 46L52 32" stroke="none"/>
                <line x1="30" y1="30" x2="50" y2="50" stroke="#FF4757" stroke-width="3" stroke-linecap="round"/>
                <line x1="50" y1="30" x2="30" y2="50" stroke="#FF4757" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </div>
        <h2>Akun Anda Diblokir</h2>
        <p>Akun Anda telah diblokir oleh administrator karena pelanggaran ketentuan layanan.</p>
        <div class="reason-box">
            <strong>Kemungkinan alasan:</strong><br>
            - Aktivitas mencurigakan terdeteksi<br>
            - Pelanggaran terms of service<br>
            - Multiple account terdeteksi<br>
            - Request dari administrator
        </div>
        <p>Jika Anda merasa ini adalah kesalahan, silakan hubungi tim support kami.</p>
        <a href="mailto:support@noxara.page" class="btn btn-support">Hubungi Admin</a>
        <div class="logo">NOXARA</div>
    </div>
</body>
</html>
