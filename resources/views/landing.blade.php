@php
    $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.balisantih.undanganbali';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Buat undangan pernikahan digital bernuansa Bali, bagikan momen, dan kelola Wedding Gift dari satu aplikasi.">
    <meta name="theme-color" content="#130f0b">
    <title>Undangan Pernikahan Bali | Cerita Cinta Bali</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
    <header class="nav-wrap">
        <nav class="nav shell" aria-label="Navigasi utama">
            <a class="brand" href="#beranda" aria-label="Undangan Pernikahan Bali">
                <span class="brand-mark" aria-hidden="true"><span>B</span></span>
                <span class="brand-text">Undangan Bali</span>
            </a>
            <div class="nav-links">
                <a href="#template">Template</a>
                <a href="#fitur">Fitur</a>
                <a href="#cara-kerja">Cara kerja</a>
                <a href="#wedding-gift">Wedding Gift</a>
            </div>
            <a class="nav-cta" href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer">Download aplikasi</a>
        </nav>
    </header>

    <main>
        <section class="hero" id="beranda">
            <div class="hero-grid shell">
                <div class="hero-copy reveal">
                    <p class="eyebrow">Undangan digital bernuansa Bali</p>
                    <h1 class="display">Cerita cinta Bali, <em>hadir lebih dekat.</em></h1>
                    <p class="hero-lead">Buat undangan yang terasa personal, bagikan momen perjalanan kalian, dan kelola semuanya langsung dari aplikasi.</p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer">Download di Google Play <span aria-hidden="true">→</span></a>
                        <a class="button button-secondary" href="#template">Lihat pilihan template</a>
                    </div>
                    <div class="hero-note">
                        <span>Coba tanpa login</span>
                        <span>Preview sebelum publish</span>
                        <span>Mudah dibagikan</span>
                    </div>
                </div>

                <div class="phone-stage reveal" aria-label="Contoh tampilan undangan di ponsel">
                    <div class="halo" aria-hidden="true"></div>
                    <div class="phone">
                        <div class="phone-screen">
                            <div class="phone-notch" aria-hidden="true"></div>
                            <img src="{{ asset('storage/templates/bali-preview/hero-couple.jpg') }}" alt="Pasangan mengenakan busana pernikahan Bali">
                            <div class="phone-content">
                                <small>Pawiwahan</small>
                                <h2 class="display">Wira &amp; Ayu</h2>
                                <p>Merayakan hari bahagia dalam hangatnya tradisi Bali.</p>
                            </div>
                        </div>
                    </div>
                    <div class="float-card float-one">
                        <span class="float-icon" aria-hidden="true">✓</span>
                        <strong>Langsung mencoba</strong>
                        <span>Login baru diminta saat undangan akan dipublish.</span>
                    </div>
                    <div class="float-card float-two">
                        <span class="float-icon" aria-hidden="true">Rp</span>
                        <strong>Wedding Gift</strong>
                        <span>Pembayaran tamu tetap aman melalui halaman web.</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="signal shell reveal" aria-label="Keunggulan utama">
            <div class="signal-grid">
                <div class="signal-item"><strong>Tanpa hambatan</strong><span>Mulai membuat tanpa wajib login</span></div>
                <div class="signal-item"><strong>Nuansa Bali</strong><span>Template dengan karakter yang berbeda</span></div>
                <div class="signal-item"><strong>Siap dibagikan</strong><span>Link publik untuk WhatsApp dan browser</span></div>
                <div class="signal-item"><strong>Lebih personal</strong><span>Foto, galeri, musik, dan cerita kalian</span></div>
            </div>
        </div>

        <section class="section section-cream" id="template">
            <div class="shell">
                <div class="section-heading reveal">
                    <div>
                        <p class="eyebrow">Pilihan tema</p>
                        <h2 class="display">Setiap kisah layak punya suasana sendiri.</h2>
                    </div>
                    <p>Lihat preview dengan data contoh sebelum menentukan template. Setelah cocok, lanjutkan pengisian undangan dari aplikasi.</p>
                </div>
                <div class="template-grid">
                    <a class="template-card reveal" href="{{ route('templates.preview', ['template' => 'bali-classic']) }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('storage/templates/bali-preview/hero-couple.jpg') }}" alt="Preview template Bali Classic" loading="lazy">
                        <span class="template-meta"><span><small>Klasik &amp; hangat</small><h3>Bali Classic</h3></span><span class="template-arrow" aria-hidden="true">↗</span></span>
                    </a>
                    <a class="template-card reveal" href="{{ route('templates.preview', ['template' => 'pura-sunset']) }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('storage/templates/bali-preview/gallery-evening.jpg') }}" alt="Preview template Pura Sunset" loading="lazy">
                        <span class="template-meta"><span><small>Dramatis &amp; temaram</small><h3>Pura Sunset</h3></span><span class="template-arrow" aria-hidden="true">↗</span></span>
                    </a>
                    <a class="template-card reveal" href="{{ route('templates.preview', ['template' => 'ubud-garden']) }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('storage/templates/bali-preview/gallery-pavilion.jpg') }}" alt="Preview template Ubud Garden" loading="lazy">
                        <span class="template-meta"><span><small>Terang &amp; editorial</small><h3>Ubud Garden</h3></span><span class="template-arrow" aria-hidden="true">↗</span></span>
                    </a>
                    <a class="template-card reveal" href="{{ route('templates.preview', ['template' => 'puspa-kencana']) }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('storage/templates/bali-heritage/bali-heritage-frame.jpg') }}" alt="Preview template Puspa Kencana" loading="lazy">
                        <span class="template-meta"><span><small>Warisan &amp; berkarakter</small><h3>Puspa Kencana</h3></span><span class="template-arrow" aria-hidden="true">↗</span></span>
                    </a>
                </div>
            </div>
        </section>

        <section class="section" id="fitur">
            <div class="shell">
                <div class="section-heading reveal">
                    <div>
                        <p class="eyebrow">Dibuat untuk kisah kalian</p>
                        <h2 class="display">Bukan sekadar link undangan.</h2>
                    </div>
                    <p>Semua bagian penting disusun agar mudah digunakan pasangan dan tetap nyaman dibuka tamu dari ponsel.</p>
                </div>
                <div class="feature-grid">
                    <article class="feature-card reveal"><span class="feature-number">01</span><h3>Foto &amp; galeri pribadi</h3><p>Gunakan foto mempelai dan galeri milik kalian sendiri agar undangan terasa autentik.</p></article>
                    <article class="feature-card reveal"><span class="feature-number">02</span><h3>Musik pilihan</h3><p>Dengarkan musik bawaan lebih dulu atau unggah musik sendiri untuk membangun suasana.</p></article>
                    <article class="feature-card reveal"><span class="feature-number">03</span><h3>Lokasi yang jelas</h3><p>Simpan lokasi dan tautan Google Maps agar tamu lebih mudah menemukan tempat acara.</p></article>
                    <article class="feature-card reveal"><span class="feature-number">04</span><h3>Moment perjalanan</h3><p>Bagikan foto perjalanan menuju hari bahagia dan berinteraksi melalui feed Moment.</p></article>
                </div>
            </div>
        </section>

        <section class="section section-cream" id="cara-kerja">
            <div class="shell">
                <div class="section-heading reveal">
                    <div><p class="eyebrow">Cara kerja</p><h2 class="display">Dari ide menjadi undangan dalam langkah sederhana.</h2></div>
                </div>
                <div class="steps">
                    <article class="step reveal"><h3>Pilih template</h3><p>Preview dulu, lalu gunakan tema yang paling mewakili suasana kalian.</p></article>
                    <article class="step reveal"><h3>Lengkapi cerita</h3><p>Isi data mempelai, acara, lokasi, galeri, musik, dan pengaturan gift.</p></article>
                    <article class="step reveal"><h3>Periksa hasil</h3><p>Lihat rangkuman data dan preview sebelum menyimpan undangan secara online.</p></article>
                    <article class="step reveal"><h3>Publish &amp; bagikan</h3><p>Login saat siap publish, lalu bagikan link undangan kepada orang terdekat.</p></article>
                </div>
            </div>
        </section>

        <section class="section" id="wedding-gift">
            <div class="shell">
                <div class="gift-panel reveal">
                    <div class="gift-copy">
                        <p class="eyebrow">Wedding Gift</p>
                        <h2 class="display">Hadiah digital dengan alur yang transparan.</h2>
                        <p>Pasangan mengatur dan memantau gift dari aplikasi. Tamu melakukan pembayaran melalui halaman undangan di browser, bukan melalui checkout di aplikasi.</p>
                        <div class="gift-list">
                            <span>Nominal gift dan biaya layanan ditampilkan terpisah sebelum pembayaran.</span>
                            <span>Status pembayaran diverifikasi oleh backend melalui penyedia pembayaran.</span>
                            <span>Dashboard membantu pasangan melihat gift yang berhasil diterima.</span>
                        </div>
                    </div>
                    <div class="receipt-wrap">
                        <div class="receipt">
                            <span class="receipt-label">Contoh rincian</span>
                            <h3>Wedding Gift</h3>
                            <div class="receipt-row"><span>Nominal gift</span><strong>Rp100.000</strong></div>
                            <div class="receipt-row"><span>Biaya layanan</span><strong>Rp2.000</strong></div>
                            <div class="receipt-row total"><span>Total bayar</span><strong>Rp102.000</strong></div>
                            <p class="receipt-note">Rincian final selalu ditampilkan kepada tamu sebelum melanjutkan pembayaran.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="download">
            <div class="shell reveal">
                <p class="eyebrow">Mulai dari ponselmu</p>
                <h2 class="display">Buat undangan yang terasa seperti kalian.</h2>
                <p>Coba pilih template dan isi draft tanpa login. Akun baru dibutuhkan ketika kalian siap publish dan membagikan undangan.</p>
                <a class="play-badge" href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Download Undangan Pernikahan Bali di Google Play">
                    <span class="play-icon" aria-hidden="true">▶</span>
                    <span><small>Download di</small><strong>Google Play</strong></span>
                </a>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-grid shell">
            <span>&copy; {{ date('Y') }} Undangan Pernikahan Bali. Dibuat dengan hangat di Bali.</span>
            <div class="footer-links">
                <a href="{{ route('privacy-policy') }}">Kebijakan Privasi</a>
                <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer">Google Play</a>
            </div>
        </div>
    </footer>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));
    </script>
</body>
</html>
