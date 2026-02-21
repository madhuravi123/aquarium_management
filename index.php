<?php
session_start();

// ── DEMO MODE: Include DB connection (which triggers auto_reset.php) ─────
// This ensures the demo reset timer runs on every landing-page visit,
// even before checking session.  To disable demo mode, comment out the
// require_once line for auto_reset.php inside config/db_connect.php.
require_once 'config/db_connect.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'customer') {
        header("Location: shop.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}
// Get live fish count for the landing page stats
$fish_count    = $conn->query("SELECT COUNT(*) FROM fish WHERE stock_quantity > 0")->fetch_row()[0] ?? 0;
$tank_count    = $conn->query("SELECT COUNT(*) FROM tanks WHERE status='active'")->fetch_row()[0] ?? 0;
$species_count = $conn->query("SELECT COUNT(DISTINCT species) FROM fish")->fetch_row()[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aquamart & Fish Shop – Cuddalore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --ocean-dark: #0a3d62;
            --ocean-mid:  #1e6091;
            --ocean-teal: #0d7377;
            --gold:       #f39c12;
        }

        * { box-sizing: border-box; }

        body {
            background: #0a0e1a;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Global container breathing space */
        .container {
            padding-left: 24px;
            padding-right: 24px;
        }

        @media (min-width: 768px) {
            .container {
                padding-left: 32px;
                padding-right: 32px;
            }
        }

        @media (min-width: 1200px) {
            .container {
                padding-left: 48px;
                padding-right: 48px;
            }
        }

        /* ── HERO ─────────────────────────────────────────────── */
        .hero {
            height: 100vh;
            max-height: 100vh;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(30,96,145,0.35) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(13,115,119,0.25) 0%, transparent 55%),
                linear-gradient(135deg, #060d1a 0%, #0a3d62 40%, #0d7377 75%, #06121e 100%);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 20px 0;
        }

        /* Floating bubbles */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            animation: rise linear infinite;
        }
        @keyframes rise {
            0%   { transform: translateY(100vh) scale(0);   opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.4; }
            100% { transform: translateY(-20vh) scale(1.3); opacity: 0; }
        }

        /* Floating fish icons */
        .float-fish {
            position: absolute;
            opacity: 0.07;
            animation: swim ease-in-out infinite alternate;
        }
        @keyframes swim {
            from { transform: translateX(0)    rotate(-5deg); }
            to   { transform: translateX(60px) rotate(5deg); }
        }

        .hero-badge {
            display: inline-block;
            background: rgba(243,156,18,0.15);
            border: 1px solid rgba(243,156,18,0.4);
            border-radius: 50px;
            padding: 6px 18px;
            font-size: 0.82rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 1.2rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(90deg, #ffffff 0%, #7ecbf5 55%, #14d2c8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .hero-tagline {
            font-size: 1rem;
            color: rgba(255,255,255,0.7);
            max-width: 520px;
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .quote-block {
            border-left: 3px solid var(--gold);
            padding: 8px 14px;
            background: rgba(243,156,18,0.07);
            border-radius: 0 8px 8px 0;
            font-style: italic;
            color: rgba(255,255,255,0.65);
            font-size: 0.85rem;
            max-width: 480px;
        }

        .btn-shop {
            background: linear-gradient(135deg, #14d2c8 0%, #1e6091 100%);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 24px rgba(20,210,200,0.35);
        }
        .btn-shop:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 32px rgba(20,210,200,0.5);
            color: #fff;
        }

        .btn-admin {
            background: transparent;
            color: rgba(255,255,255,0.75);
            border: 1.5px solid rgba(255,255,255,0.3);
            padding: 11px 24px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-color: rgba(255,255,255,0.6);
        }

        /* ── STATS BAR ─────────────────────────────────────────── */
        .stats-bar {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 12px 20px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 1.5rem;
            backdrop-filter: blur(8px);
        }
        .stat-item { text-align: center; }
        .stat-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: #14d2c8;
            line-height: 1;
        }
        .stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-top: 2px; }

        /* ── FEATURES SECTION ─────────────────────────────────── */
        .features { background: #060d1a; padding: 100px 0 120px; }
        .feature-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 40px 28px;
            text-align: center;
            transition: transform 0.3s ease, border-color 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            border-color: rgba(20,210,200,0.3);
        }
        .feature-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #0d7377, #1e6091);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        .feature-card h5 { color: #fff; font-weight: 700; margin-bottom: 8px; }
        .feature-card p  { color: rgba(255,255,255,0.5); font-size: 0.9rem; margin: 0; }

        /* ── QUOTES CAROUSEL ──────────────────────────────────── */
        .quotes-section { background: var(--ocean-dark); padding: 80px 0 100px; text-align: center; }
        .quote-text {
            font-size: 1.3rem;
            font-style: italic;
            color: rgba(255,255,255,0.85);
            max-width: 680px;
            margin: 0 auto;
            line-height: 1.8;
        }
        .quote-author { color: var(--gold); font-size: 0.9rem; margin-top: 16px; }

        /* ── FOOTER ───────────────────────────────────────────── */
        .site-footer {
            background: #030609;
            border-top: 1px solid rgba(255,255,255,0.06);
            padding: 28px 0;
            text-align: center;
            color: rgba(255,255,255,0.35);
            font-size: 0.85rem;
        }

        /* Hero image fish illustration */
        .hero-fish-img {
            position: relative;
            z-index: 2;
        }
        .fish-circle {
            width: clamp(220px, 32vw, 380px);
            height: clamp(220px, 32vw, 380px);
            background: radial-gradient(circle at 40% 40%, rgba(20,210,200,0.18), rgba(10,61,98,0.5));
            border: 1px solid rgba(20,210,200,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative;
            margin: 0 auto;
            box-shadow: 0 0 80px rgba(20,210,200,0.12), inset 0 0 60px rgba(30,96,145,0.3);
        }
        .fish-circle .main-icon {
            font-size: clamp(5rem, 12vw, 9rem);
            opacity: 0.75;
            color: #7ecbf5;
            filter: drop-shadow(0 0 30px rgba(126,203,245,0.5));
            animation: swim ease-in-out 4s infinite alternate;
        }
        .orbit-icon {
            position: absolute;
            font-size: 1.8rem;
            opacity: 0.6;
            animation: orbit linear 10s infinite;
        }
        @keyframes orbit {
            from { transform: rotate(0deg) translateX(130px) rotate(0deg); }
            to   { transform: rotate(360deg) translateX(130px) rotate(-360deg); }
        }
        .orbit-icon:nth-child(2) { animation-delay: -3.3s; color: #f39c12; }
        .orbit-icon:nth-child(3) { animation-delay: -6.6s; color: #14d2c8; }
    </style>
</head>
<body>

<!-- ═══ BUBBLES (decorative) ═══════════════════════════════════════════════ -->
<?php
$bubble_sizes  = [30,50,20,70,40,25,60,35,45,80,22,55];
$bubble_delays = [0,2,4,1,6,3,8,5,7,0.5,9,2.5];
$bubble_lefts  = [5,15,25,35,45,55,65,75,85,10,50,70];
foreach ($bubble_sizes as $k => $sz) {
    $dur   = 8 + ($sz / 10);
    $style = "width:{$sz}px;height:{$sz}px;left:{$bubble_lefts[$k]}%;animation-duration:{$dur}s;animation-delay:{$bubble_delays[$k]}s;";
    echo '<div class="bubble" style="' . $style . '"></div>' . "\n";
}
?>

<!-- Floating fish BG -->
<i class="fas fa-fish float-fish" style="font-size:6rem; top:12%; right:4%; animation-duration:5s;"></i>
<i class="fas fa-fish float-fish" style="font-size:3rem; top:65%; left:3%;  animation-duration:7s; animation-delay:1s; transform:scaleX(-1);"></i>
<i class="fas fa-fish float-fish" style="font-size:9rem; bottom:8%; right:12%; animation-duration:6s; animation-delay:2s;"></i>

<!-- ═══ HERO ════════════════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="container position-relative" style="z-index:10;">
        <div class="row align-items-center gy-5">

            <!-- Left: Text content -->
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    Manjakuppam, Cuddalore – 607001
                </div>

                <h1>Welcome to<br>Aquamart</h1>

                <p class="hero-tagline mt-3">
                    Discover the beauty beneath the surface. Premium freshwater &amp;
                    exotic fish, delivered with care from the heart of Tamil Nadu.
                </p>

                <div class="quote-block mt-3 mb-3">
                    "An aquarium is a window to the world beneath the waves —
                    bringing the ocean's calm into your home."
                </div>

                <!-- CTA Buttons -->
                <div class="d-flex flex-wrap gap-3 mt-3">
                    <a href="shop.php" class="btn btn-shop">
                        <i class="fas fa-fish me-2"></i>Browse Fish Shop
                    </a>
                    <a href="admin_login.php" class="btn btn-admin">
                        <i class="fas fa-lock me-2"></i>Admin Login
                    </a>
                </div>

                <!-- Stats bar -->
                <div class="stats-bar mt-3">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $fish_count; ?>+</div>
                        <div class="stat-label">Fish In Stock</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $species_count; ?>+</div>
                        <div class="stat-label">Species Available</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $tank_count; ?></div>
                        <div class="stat-label">Live Display Tanks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">₹40</div>
                        <div class="stat-label">Starting Price</div>
                    </div>
                </div>
            </div>

            <!-- Right: Fish visual -->
            <div class="col-lg-6 hero-fish-img text-center d-none d-lg-block">
                <div class="fish-circle">
                    <i class="fas fa-fish main-icon"></i>
                    <i class="fas fa-star orbit-icon"></i>
                    <i class="fas fa-water orbit-icon"></i>
                    <i class="fas fa-leaf orbit-icon"></i>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══ FEATURES ═════════════════════════════════════════════════════════════ -->
<section class="features">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color:#fff;">Why Choose Aquamart?</h2>
            <p class="text-muted" style="color:rgba(255,255,255,0.7) !important;">Tamil Nadu's trusted source for premium fish since 2018</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-fish text-white"></i></div>
                    <h5>32+ Fish Varieties</h5>
                    <p>From ₹40 Zebra Danio to ₹15,000 Golden Arowana — freshwater &amp; exotic species for every home.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shipping-fast text-white"></i></div>
                    <h5>Same-Day Delivery</h5>
                    <p>Live fish packed with oxygen and delivered same-day within Cuddalore and surrounding areas.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-heart text-white"></i></div>
                    <h5>Expert Fish Care</h5>
                    <p>Our specialists monitor health, water quality and feeding daily to ensure your fish thrive.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-hand-holding-water text-white"></i></div>
                    <h5>Guest-Friendly Shopping</h5>
                    <p>No account needed — browse, select and checkout as a guest in just a few clicks.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-rupee-sign text-white"></i></div>
                    <h5>Best INR Prices</h5>
                    <p>Competitive Indian market prices. UPI, Cash, Card and Online payments all accepted.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt text-white"></i></div>
                    <h5>Healthy &amp; Certified</h5>
                    <p>All fish quarantined and health-checked before sale. Disease-free guarantee from local Tamil Nadu farms.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ QUOTES CAROUSEL ══════════════════════════════════════════════════════ -->
<section class="quotes-section">
    <div class="container">
        <div id="quoteCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <p class="quote-text">
                        "In every drop of water, there is a story of life waiting to be discovered."
                    </p>
                    <div class="quote-author">— Leena Naidoo, Marine Biologist</div>
                </div>
                <div class="carousel-item">
                    <p class="quote-text">
                        "An aquarium is not just a decoration — it is a living ecosystem, a daily reminder
                        of nature's delicate balance."
                    </p>
                    <div class="quote-author">— Aquarium Enthusiast Proverb</div>
                </div>
                <div class="carousel-item">
                    <p class="quote-text">
                        "Watching fish glide silently through water is one of life's most peaceful meditations."
                    </p>
                    <div class="quote-author">— Dr. Suresh Babu, Marine Sciences, IIT Madras</div>
                </div>
                <div class="carousel-item">
                    <p class="quote-text">
                        "Buy a fish, bring the ocean home. Every tank tells a thousand stories of the deep."
                    </p>
                    <div class="quote-author">— Aquamart, Cuddalore</div>
                </div>
            </div>
            <!-- Indicators -->
            <div class="d-flex justify-content-center gap-2 mt-4">
                <button class="btn btn-sm" style="width:8px;height:8px;border-radius:50%;background:rgba(243,156,18,0.8);padding:0;" data-bs-target="#quoteCarousel" data-bs-slide-to="0"></button>
                <button class="btn btn-sm" style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3);padding:0;" data-bs-target="#quoteCarousel" data-bs-slide-to="1"></button>
                <button class="btn btn-sm" style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3);padding:0;" data-bs-target="#quoteCarousel" data-bs-slide-to="2"></button>
                <button class="btn btn-sm" style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3);padding:0;" data-bs-target="#quoteCarousel" data-bs-slide-to="3"></button>
            </div>
        </div>
    </div>
</section>

<!-- ═══ SITE FOOTER ══════════════════════════════════════════════════════════ -->
<footer class="site-footer">
    <div class="container">
        <div class="row align-items-center gy-2">
            <div class="col-md-6 text-md-start">
                <i class="fas fa-fish me-2" style="color:#14d2c8;"></i>
                <strong style="color:rgba(255,255,255,0.7);">Aquamart &amp; Fish Shop</strong>
                &nbsp;|&nbsp; Manjakuppam, Cuddalore – 607001
            </div>
            <div class="col-md-6 text-md-end">
                <i class="fas fa-phone me-1" style="color:#14d2c8;"></i>9876543210
                &nbsp;|&nbsp;
                &copy; <?php echo date('Y'); ?> All rights reserved
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize quote carousel with auto-play
    document.addEventListener('DOMContentLoaded', function() {
        var quoteCarousel = document.getElementById('quoteCarousel');
        if (quoteCarousel) {
            new bootstrap.Carousel(quoteCarousel, {
                interval: 4000,
                wrap: true,
                touch: true,
                ride: 'carousel'
            });
        }
    });
</script>
</body>
</html>
