<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Halaman Tidak Ditemukan | SiMoU RSUD Kilisuci</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: #f0f7fa;
            padding: 20px;
        }
        .err-card {
            text-align: center;
            max-width: 480px;
            background: #fff;
            border-radius: 24px;
            padding: 52px 40px;
            box-shadow: 0 20px 60px rgba(10,126,164,.12);
            border: 1px solid #cce5f0;
        }
        .err-icon {
            font-size: 4rem;
            color: #cce5f0;
            margin-bottom: 20px;
        }
        .err-code {
            font-size: 5rem;
            font-weight: 800;
            letter-spacing: -.06em;
            background: linear-gradient(135deg,#0a7ea4,#0d9488);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 12px;
        }
        .err-title { font-size: 1.2rem; font-weight: 700; color: #0d1f2d; margin-bottom: 8px; }
        .err-desc  { font-size: .85rem; color: #6b8fa3; line-height: 1.65; margin-bottom: 28px; }
        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 24px;
            background: linear-gradient(135deg,#0a7ea4,#0d9488);
            color: #fff; border-radius: 10px; font-weight: 700; font-size: .88rem;
            text-decoration: none;
            transition: opacity .2s, transform .15s;
        }
        .btn-home:hover { opacity: .9; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="err-card">
        <div class="err-icon"><i class="fa-solid fa-file-circle-xmark"></i></div>
        <div class="err-code">404</div>
        <div class="err-title">Halaman Tidak Ditemukan</div>
        <p class="err-desc">
            Maaf, halaman atau dokumen yang Anda cari tidak tersedia atau telah dipindahkan.
        </p>
        <a href="/" class="btn-home">
            <i class="fa-solid fa-house"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>
