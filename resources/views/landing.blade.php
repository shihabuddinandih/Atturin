<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atturin — Atur Match. Tanpa Drama Grup WA.</title>
    <meta name="description" content="Platform manajemen komunitas olahraga. Kelola slot pemain, tagih iuran otomatis via QRIS, dan rekap kehadiran dalam satu link yang bisa langsung dibagikan.">
    <link rel="icon" type="image/png" href="{{ asset('images/Logo/Logo (Lettermark)/Primary Dark.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Design Tokens ── */
        :root {
            --bg:           #FFFFFF;
            --bg-subtle:    #EEF1F6;
            --text-primary: #0A1628;
            --text-sec:     #5A6480;
            --electric:     #0052FF;
            --neon:         #ABD600;
            --border:       #E6EEFF;
            --danger:       #EF4444;
            --success:      #22C55E;
            --dark:         #0A1628;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ── Utility ── */
        .container { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
        .section { padding: 96px 0; }
        .section-subtle { background: var(--bg-subtle); }

        /* ── Navbar ── */
        #navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--dark);
            border-bottom: 1px solid var(--border);
            transition: box-shadow .3s;
        }
        #navbar.scrolled { box-shadow: 0 2px 16px rgba(0,65,255,.07); }
        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }
        .nav-logo img { height: 32px; object-fit: contain; }
        .nav-links { display: flex; align-items: center; gap: 32px; }
        .nav-links a {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            text-decoration: none;
            transition: color .2s;
        }
        .nav-links a:hover { color: var(--electric); }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--electric);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background .2s, box-shadow .2s, transform .15s;
        }
        .btn-primary:hover {
            background: var(--electric);
            box-shadow: 0 6px 24px rgba(0,82,255,.25);
            transform: translateY(-1px);
        }
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: var(--electric);
            font-weight: 700;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            border: 2px solid var(--electric);
            cursor: pointer;
            transition: all .2s;
        }
        .btn-outline:hover {
            background: var(--electric);
            color: #fff;
            transform: translateY(-1px);
        }
        .nav-mobile-hide { display: none; }

        /* ── Hero ── */
        #hero {
            background: var(--bg);
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero-dot-bg {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, #E6EEFF 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: .45;
            pointer-events: none;
        }
        .hero-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: center;
            position: relative;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #E6EEFF;
            border: 1px solid #CCDDFF;
            color: var(--electric);
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 99px;
            margin-bottom: 20px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .hero-title {
            font-size: clamp(40px, 5vw, 64px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1.5px;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        .hero-title .accent { color: var(--electric); }
        .hero-sub {
            font-size: 17px;
            line-height: 1.7;
            color: var(--text-sec);
            max-width: 460px;
            margin-bottom: 36px;
        }
        .hero-ctas { display: flex; gap: 12px; flex-wrap: wrap; }

        /* Hero Mockup Card */
        .hero-visual {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .event-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 16px 48px rgba(0,65,255,.1), 0 4px 12px rgba(0,0,0,.06);
            position: relative;
        }
        .event-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .event-card-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--electric), var(--neon));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        .event-card-title { font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .event-card-sub { font-size: 12px; color: var(--text-sec); margin-top: 2px; }
        .event-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }
        .event-info-row:last-child { border-bottom: none; }
        .event-info-label { color: var(--text-sec); }
        .event-info-val { font-weight: 600; color: var(--text-primary); }
        .slot-section { margin-top: 16px; }
        .slot-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .slot-label .urgent { color: var(--danger); }
        .slot-bar {
            height: 8px;
            border-radius: 4px;
            background: var(--border);
            overflow: hidden;
        }
        .slot-bar-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--electric), var(--neon));
            animation: fillBar 1.6s ease-out forwards;
            width: 0%;
        }
        @keyframes fillBar { to { width: 91.67%; } }
        .player-list { margin-top: 16px; display: flex; flex-direction: column; gap: 8px; }
        .player-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .player-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .player-name { font-weight: 600; flex: 1; }
        .status-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .status-paid { background: rgba(171, 214, 0, 0.15); color: #365314; }
        .status-pending { background: #FFF7ED; color: #92400E; }
        .floating-badge {
            position: absolute;
            bottom: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 99px;
            white-space: nowrap;
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
            animation: floatBadge 2.5s ease-in-out infinite;
        }
        @keyframes floatBadge {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-5px); }
        }

        /* ── Pain Points ── */
        .pain-intro {
            text-align: center;
            margin-bottom: 56px;
        }
        .section-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--neon);
            margin-bottom: 12px;
        }
        .section-title {
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text-primary);
            line-height: 1.15;
        }
        .section-sub {
            font-size: 17px;
            color: var(--text-sec);
            margin-top: 12px;
            max-width: 560px;
            margin-left: auto;
            margin-right: auto;
        }
        .pain-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .pain-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            position: relative;
            transition: box-shadow .2s, transform .2s;
        }
        .pain-card:hover { box-shadow: 0 8px 32px rgba(0,65,255,.08); transform: translateY(-2px); }
        .pain-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #E6EEFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }
        .pain-title { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .pain-desc { font-size: 14px; color: var(--text-sec); line-height: 1.6; }
        .pain-x {
            position: absolute;
            top: 16px;
            right: 16px;
            color: var(--danger);
            font-size: 18px;
            font-weight: 800;
        }
        .pain-arrow {
            text-align: center;
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .pain-arrow-line {
            width: 2px;
            height: 40px;
            background: linear-gradient(to bottom, var(--border), var(--electric));
            border-radius: 2px;
        }
        .pain-arrow-text {
            font-size: 14px;
            font-weight: 700;
            color: var(--electric);
        }

        /* ── Features ── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        .feature-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: box-shadow .2s, transform .2s, border-color .2s;
        }
        .feature-card:hover {
            box-shadow: 0 8px 32px rgba(0,65,255,.1);
            border-color: #CCDDFF;
            transform: translateY(-2px);
        }
        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #E6EEFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .feature-title { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
        .feature-desc { font-size: 14px; color: var(--text-sec); line-height: 1.65; }
        .feature-tag {
            display: inline-block;
            background: #E6EEFF;
            color: var(--electric);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            margin-top: 10px;
            letter-spacing: .04em;
        }

        /* ── How it works ── */
        .steps-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            position: relative;
        }
        .steps-row::before {
            content: '';
            position: absolute;
            top: 36px;
            left: calc(16.67% + 24px);
            right: calc(16.67% + 24px);
            height: 2px;
            background: linear-gradient(90deg, var(--electric), var(--neon));
            z-index: 0;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 24px;
            position: relative;
            z-index: 1;
        }
        .step-num {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--bg);
            border: 3px solid var(--electric);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            color: var(--electric);
            margin-bottom: 24px;
            position: relative;
            z-index: 2;
        }
        .step-num.active {
            background: var(--electric);
            color: #fff;
        }
        .step-title { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
        .step-desc { font-size: 14px; color: var(--text-sec); line-height: 1.65; }

        /* ── Stats ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            text-align: center;
        }
        .stat-num {
            font-size: clamp(40px, 5vw, 56px);
            font-weight: 800;
            color: var(--electric);
            letter-spacing: -2px;
            line-height: 1;
            margin-bottom: 8px;
        }
        .stat-label { font-size: 15px; color: var(--text-sec); font-weight: 500; }
        .stat-divider {
            width: 1px;
            background: var(--border);
            align-self: stretch;
            margin: 0 auto;
        }

        /* Testimonials */
        .testi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 56px;
        }
        .testi-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            transition: box-shadow .2s;
        }
        .testi-card:hover { box-shadow: 0 8px 32px rgba(0,65,255,.08); }
        .testi-stars { color: #F59E0B; font-size: 14px; margin-bottom: 14px; }
        .testi-text { font-size: 14px; color: var(--text-sec); line-height: 1.7; font-style: italic; margin-bottom: 20px; }
        .testi-author { display: flex; align-items: center; gap: 12px; }
        .testi-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
        }
        .testi-name { font-size: 14px; font-weight: 700; }
        .testi-role { font-size: 12px; color: var(--text-sec); }

        /* ── CTA Final ── */
        #cta-final {
            background: var(--electric);
            padding: 96px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        #cta-final::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        #cta-final::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .cta-title {
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 800;
            color: #fff;
            letter-spacing: -1px;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .cta-sub { font-size: 17px; color: rgba(255,255,255,.75); margin-bottom: 36px; }
        .btn-cta-white {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            color: var(--electric);
            font-weight: 800;
            font-size: 16px;
            padding: 16px 36px;
            border-radius: 14px;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 8px 32px rgba(0,0,0,.15);
        }
        .btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 16px 48px rgba(0,0,0,.2); }

        /* ── Footer ── */
        footer {
            background: var(--dark);
            padding: 48px 0;
        }
        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .footer-logo img { height: 28px; object-fit: contain; }
        .footer-copy { font-size: 13px; color: rgba(255,255,255,.4); }
        .footer-links { display: flex; gap: 24px; }
        .footer-links a { font-size: 13px; color: rgba(255,255,255,.5); text-decoration: none; transition: color .2s; }
        .footer-links a:hover { color: #fff; }

        /* ── Scroll Animations ── */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .6s ease, transform .6s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .hero-inner { grid-template-columns: 1fr; gap: 48px; }
            .hero-visual { order: -1; }
            .event-card { max-width: 100%; }
            .pain-grid { grid-template-columns: 1fr; }
            .features-grid { grid-template-columns: 1fr; }
            .steps-row { grid-template-columns: 1fr; }
            .steps-row::before { display: none; }
            .stats-grid { grid-template-columns: 1fr; gap: 32px; }
            .testi-grid { grid-template-columns: 1fr; }
            .nav-links { display: none; }
            .nav-mobile-hide { display: none !important; }
        }
        @media (max-width: 640px) {
            .section { padding: 64px 0; }
            #hero { padding: 64px 0; }
        }
    </style>
</head>
<body>

<!-- ── NAVBAR ── -->
<nav id="navbar">
    <div class="container">
        <div class="nav-inner">
            <a href="/" class="nav-logo">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" alt="Atturin">
            </a>
            <div class="nav-links">
                <a href="#fitur">Fitur</a>
                <a href="#cara-kerja">Cara Kerja</a>
                <a href="#testimoni">Testimoni</a>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <a href="{{ route('login') }}" class="btn-outline nav-mobile-hide" style="display:inline-flex;">Masuk</a>
                <a href="{{ route('login') }}" class="btn-primary">
                    Coba Gratis
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ── HERO ── -->
<section id="hero">
    <div class="hero-dot-bg"></div>
    <div class="container">
        <div class="hero-inner">
            <!-- Left: Text -->
            <div>
                <!-- <div class="hero-badge reveal">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    Platform Komunitas Olahraga
                </div> -->
                <h1 class="hero-title reveal">
                    Atur Match.<br>
                    Tanpa <span class="accent">Drama</span><br>
                    Grup WA.
                </h1>
                <p class="hero-sub reveal">
                    Kelola slot pemain, tagih iuran otomatis via QRIS, dan rekap kehadiran komunitas olahragamu — semua dalam satu link yang bisa langsung dibagikan.
                </p>
                <div class="hero-ctas reveal">
                    <a href="{{ route('login') }}" class="btn-primary" style="font-size:15px; padding:13px 26px;">
                        Buat Match Pertamamu
                    </a>
                    <a href="#cara-kerja" class="btn-outline" style="font-size:15px; padding:13px 26px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Lihat Demo
                    </a>
                </div>
            </div>

            <!-- Right: Event Card Mockup -->
            <div class="hero-visual reveal">
                <div style="position: relative; padding-bottom: 20px; width:100%;">
                    <div class="event-card">
                        <div class="event-card-header">
                            <div class="event-card-avatar">B</div>
                            <div>
                                <div class="event-card-title">Badminton Sabtu Pagi</div>
                                <div class="event-card-sub">Dibuat oleh Rian · Admin</div>
                            </div>
                        </div>

                        <div class="event-info-row">
                            <span class="event-info-label">Jadwal</span>
                            <span class="event-info-val">Sabtu, 21 Jun · 07.00</span>
                        </div>
                        <div class="event-info-row">
                            <span class="event-info-label">Lokasi</span>
                            <span class="event-info-val">GOR Cendrawasih</span>
                        </div>
                        <div class="event-info-row">
                            <span class="event-info-label">Iuran</span>
                            <span class="event-info-val" style="color:var(--electric)">Rp 35.000/orang</span>
                        </div>

                        <div class="slot-section">
                            <div class="slot-label">
                                <span>Slot Terisi</span>
                                <span class="urgent">1 Slot Tersisa!</span>
                            </div>
                            <div class="slot-bar">
                                <div class="slot-bar-fill"></div>
                            </div>
                            <div style="font-size:12px; color:var(--text-sec); margin-top:6px; font-weight:600;">11 / 12 Pemain</div>
                        </div>

                        <div class="player-list">
                            <div class="player-row">
                                <div class="player-dot" style="background:#E6EEFF; color:var(--electric);">BR</div>
                                <span class="player-name">Bintang R.</span>
                                <span class="status-badge status-paid">✓ Lunas</span>
                            </div>
                            <div class="player-row">
                                <div class="player-dot" style="background: rgba(171, 214, 0, 0.15); color: #365314;">FA</div>
                                <span class="player-name">Fajar A.</span>
                                <span class="status-badge status-pending">Pending</span>
                            </div>
                            <div class="player-row">
                                <div class="player-dot" style="background: #E6EEFF; color: var(--electric);">DS</div>
                                <span class="player-name">Dika S.</span>
                                <span class="status-badge status-paid">✓ Lunas</span>
                            </div>
                        </div>
                    </div>
                    <div class="floating-badge">Bayar via QRIS — Instan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── PAIN POINTS ── -->
<section class="section section-subtle">
    <div class="container">
        <div class="pain-intro reveal">
            <div class="section-label">Masalah Klasik</div>
            <h2 class="section-title">Masih Begini Cara Kamu<br>Atur Match?</h2>
            <p class="section-sub">Setiap admin komunitas olahraga pasti pernah merasakannya.</p>
        </div>
        <div class="pain-grid">
            <div class="pain-card reveal">
                <span class="pain-x">✕</span>
                <div class="pain-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="pain-title">"Siapa aja yang ikut besok?"</div>
                <p class="pain-desc">Pesan tenggelam di grup WA yang ramai. Tidak ada yang balas. Admin kelelahan follow-up satu per satu.</p>
            </div>
            <div class="pain-card reveal">
                <span class="pain-x">✕</span>
                <div class="pain-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="pain-title">"Transfer iuran nanti ya bro"</div>
                <p class="pain-desc">Ujungnya lupa atau sengaja lupa. Admin yang akhirnya nombok duluan demi kelancaran lapangan.</p>
            </div>
            <div class="pain-card reveal">
                <span class="pain-x">✕</span>
                <div class="pain-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                </div>
                <div class="pain-title">"Batal dadakan, maaf banget"</div>
                <p class="pain-desc">Slot kosong di menit-menit terakhir. Susah cari pengganti. Biaya lapangan sudah kadung dibayar.</p>
            </div>
        </div>
        <div class="pain-arrow reveal">
            <div class="pain-arrow-line"></div>
            <div class="pain-arrow-text">Atturin hadir untuk itu.</div>
        </div>
    </div>
</section>

<!-- ── FEATURES ── -->
<section id="fitur" class="section">
    <div class="container">
        <div class="pain-intro reveal">
            <div class="section-label">Fitur Unggulan</div>
            <h2 class="section-title">Semua yang Dibutuhkan<br>Admin Lapangan</h2>
            <p class="section-sub">Dirancang khusus untuk pengelola komunitas olahraga — bukan untuk perusahaan besar.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
                <div>
                    <div class="feature-title">Slot Real-time</div>
                    <p class="feature-desc">Kuota penuh? Pendaftaran otomatis ditutup. Tidak ada lagi over-capacity, tidak ada perdebatan siapa yang duluan daftar.</p>
                    <span class="feature-tag">AUTO-CLOSE SLOT</span>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
                <div>
                    <div class="feature-title">Kustomisasi Role & Harga</div>
                    <p class="feature-desc">Tetapkan harga berbeda untuk kiper, pemain inti, atau cadangan. Setiap role punya kuota dan harga sendiri — fleksibel sesuai kebutuhanmu.</p>
                    <span class="feature-tag">ROLE MANAGEMENT</span>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <div class="feature-title">Pembayaran QRIS Otomatis</div>
                    <p class="feature-desc">Iuran langsung terkumpul tanpa transfer manual. Status "Lunas" update otomatis di database begitu pembayaran berhasil.</p>
                    <span class="feature-tag">POWERED BY MIDTRANS</span>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                </div>
                <div>
                    <div class="feature-title">Skema Loyalitas</div>
                    <p class="feature-desc">Berikan apresiasi kepada anggota setia komunitasmu. Diskon iuran otomatis dihitung berdasarkan riwayat keikutsertaan mereka.</p>
                    <span class="feature-tag">LOYALTY SCHEME</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section id="cara-kerja" class="section section-subtle">
    <div class="container">
        <div class="pain-intro reveal">
            <div class="section-label">Cara Kerja</div>
            <h2 class="section-title">Mulai dalam 3 Langkah</h2>
            <p class="section-sub">Tidak perlu install aplikasi. Tidak perlu training. Langsung jalan.</p>
        </div>
        <div class="steps-row">
            <div class="step-item reveal">
                <div class="step-num active">1</div>
                <div class="step-title">Buat Event</div>
                <p class="step-desc">Isi nama pertandingan, jadwal, lokasi, kuota slot, dan biaya iuran. Selesai dalam 2 menit.</p>
            </div>
            <div class="step-item reveal">
                <div class="step-num">2</div>
                <div class="step-title">Bagikan Link</div>
                <p class="step-desc">Kirim link pendaftaran ke grup WA atau sosmed. Pemain daftar dan bayar sendiri tanpa perlu akun.</p>
            </div>
            <div class="step-item reveal">
                <div class="step-num">3</div>
                <div class="step-title">Tinggal Main</div>
                <p class="step-desc">Iuran masuk otomatis via QRIS. Admin rekap kehadiran langsung dari dashboard. Fokus ke permainan.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── STATS + TESTIMONIALS ── -->
<section id="testimoni" class="section">
    <div class="container">
        <!-- Stats -->
        <div class="stats-grid reveal">
            <div>
                <div class="stat-num" data-target="500">0</div>
                <div class="stat-label">Match Dikelola</div>
            </div>
            <div>
                <div class="stat-num" data-target="10000">0</div>
                <div class="stat-label">Pemain Terdaftar</div>
            </div>
            <div>
                <div class="stat-num" data-target="98">0</div>
                <div class="stat-label">% Iuran Tertagih Tepat Waktu</div>
            </div>
        </div>

        <!-- Testimonials -->
        <div class="testi-grid">
            <div class="testi-card reveal">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"Semenjak pakai Atturin, saya nggak perlu lagi bikin list manual setiap minggu di WhatsApp. Pemain tinggal daftar sendiri dan bayar via QRIS. Simple banget."</p>
                <div class="testi-author">
                    <div class="testi-avatar" style="background: linear-gradient(135deg, var(--electric), var(--neon));">RA</div>
                    <div>
                        <div class="testi-name">Rian Andika</div>
                        <div class="testi-role">Admin Badminton Jakarta Selatan</div>
                    </div>
                </div>
            </div>
            <div class="testi-card reveal">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"Dulu selalu ada aja yang 'lupa' bayar iuran. Sekarang karena harus bayar dulu baru slot-nya terkunci, tidak ada yang bisa skip. Masalah selesai."</p>
                <div class="testi-author">
                    <div class="testi-avatar" style="background: linear-gradient(135deg, #3377FF, #0052FF);">DN</div>
                    <div>
                        <div class="testi-name">Dimas N.</div>
                        <div class="testi-role">Kapten Futsal Komunitas Senayan</div>
                    </div>
                </div>
            </div>
            <div class="testi-card reveal">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">"Fitur role-nya sangat berguna untuk komunitas kami yang punya harga berbeda untuk member dan non-member. Rekap kehadiran juga rapi banget di dashboard."</p>
                <div class="testi-author">
                    <div class="testi-avatar" style="background: linear-gradient(135deg, #ABD600, #0052FF);">SP</div>
                    <div>
                        <div class="testi-name">Sari P.</div>
                        <div class="testi-role">Koordinator Mini Soccer Bekasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA FINAL ── -->
<section id="cta-final">
    <div class="container" style="position:relative; z-index:1;">
        <h2 class="cta-title reveal">Komunitas Olahragamu<br>Layak Dapat yang Lebih Baik.</h2>
        <p class="cta-sub reveal">Mulai gratis. Tidak perlu kartu kredit.</p>
        <a href="{{ route('login') }}" class="btn-cta-white reveal">
            Daftarkan Komunitasmu Sekarang
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</section>

<!-- ── FOOTER ── -->
<footer>
    <div class="container">
        <div class="footer-inner">
            <div class="footer-logo">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" alt="Atturin">
            </div>
            <div class="footer-links">
                <a href="#fitur">Fitur</a>
                <a href="#cara-kerja">Cara Kerja</a>
                <a href="{{ route('login') }}">Masuk</a>
            </div>
            <p class="footer-copy">© {{ date('Y') }} Atturin. Hak cipta dilindungi.</p>
        </div>
    </div>
</footer>

<script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    });

    // Scroll reveal
    const revealEls = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e, i) => {
            if (e.isIntersecting) {
                setTimeout(() => e.target.classList.add('visible'), i * 60);
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });
    revealEls.forEach(el => io.observe(el));

    // Animated counters
    function animateCounter(el, target, duration = 1800) {
        let start = 0;
        const step = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);
            el.textContent = current >= 1000
                ? (current >= 10000 ? current.toLocaleString('id') : current.toLocaleString('id'))
                : current + (target === 98 ? '%' : '+');
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target >= 1000
                ? target.toLocaleString('id') + '+'
                : target + (target === 98 ? '%' : '+');
        };
        requestAnimationFrame(step);
    }

    const statNums = document.querySelectorAll('.stat-num[data-target]');
    const statIo = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                animateCounter(e.target, parseInt(e.target.dataset.target));
                statIo.unobserve(e.target);
            }
        });
    }, { threshold: 0.3 });
    statNums.forEach(el => statIo.observe(el));
</script>
</body>
</html>
