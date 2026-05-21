<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - NOXARA</title>
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
        .error-code {
            font-size: clamp(80px, 20vw, 150px);
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #00D4FF, #7B2FFF, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #FFFFFF;
        }
        p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            color: #FFFFFF;
            transition: all 0.3s ease;
        }
        .btn:hover {
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
        <div class="error-code">404</div>
        <h2>Halaman Tidak Ditemukan</h2>
        <p>Halaman yang Anda cari tidak ada atau telah dipindahkan. Silakan kembali ke halaman utama.</p>
        <a href="/" class="btn">Kembali ke Beranda</a>
        <div class="logo">NOXARA</div>
    </div>
</body>
</html>
