<?php
// pages/admin/dashboard.php
session_start();
require_once '../../config/database.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

/** 
 * ==========================================
 * 1. QUERY INTEGRASI DATA REAL-TIME (PDO)
 * ==========================================
 */

try {
    // A. Total Pendapatan & Transaksi Selesai
    $query_stats = "SELECT 
                        SUM(CASE WHEN status_pembayaran = 'selesai' THEN total_harga ELSE 0 END) as total_revenue,
                        COUNT(CASE WHEN status_pembayaran = 'selesai' THEN id END) as total_completed
                    FROM transaksi";
    $stmt_stats = $pdo->query($query_stats);
    $stats = $stmt_stats->fetch();

    $total_revenue = $stats['total_revenue'] ?? 0;
    $total_completed = $stats['total_completed'] ?? 0;

    // B. Efisiensi & Status Armada
    $query_armada = "SELECT 
                        COUNT(CASE WHEN status_mobil = 'jalan' THEN id END) as armada_jalan,
                        COUNT(CASE WHEN status_mobil = 'tersedia' THEN id END) as armada_tersedia,
                        COUNT(CASE WHEN status_mobil = 'maintenance' THEN id END) as armada_maintenance,
                        COUNT(id) as total_armada
                     FROM mobil";
    $stmt_armada = $pdo->query($query_armada);
    $armada = $stmt_armada->fetch();

    $armada_jalan = $armada['armada_jalan'] ?? 0;
    $armada_tersedia = $armada['armada_tersedia'] ?? 0;
    $armada_maintenance = $armada['armada_maintenance'] ?? 0;
    $total_armada = $armada['total_armada'] ?? 1;

    $efisiensi_persen = ($total_armada > 0) ? round(($armada_jalan / $total_armada) * 100) : 0;

    // C. Data Grafik Omset Mingguan (7 Hari Terakhir)
    $grafik_omset_labels = [];
    $grafik_omset_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date_string = date('Y-m-d', strtotime("-$i days"));
        $label_string = date('d M', strtotime("-$i days"));
        
        $query_omset_harian = "SELECT SUM(total_harga) as total FROM transaksi WHERE status_pembayaran = 'selesai' AND DATE(tanggal_transaksi) = :date_string";
        $stmt_harian = $pdo->prepare($query_omset_harian);
        $stmt_harian->execute(['date_string' => $date_string]);
        $data_harian = $stmt_harian->fetch();
        
        $grafik_omset_labels[] = $label_string;
        $grafik_omset_data[] = (int)($data_harian['total'] ?? 0);
    }

    // D. Data Grafik Volume Pesanan Mingguan (Travel vs Rental Supir vs Lepas Kunci)
    $hari_nama = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    $data_travel = [0, 0, 0, 0, 0, 0, 0];
    $data_rental = [0, 0, 0, 0, 0, 0, 0]; // Gabungan rental_supir & lepas_kunci agar bagan tetap sinkron

    $query_layanan = "SELECT 
                        DAYOFWEEK(tanggal_transaksi) as hari, 
                        jenis_layanan, 
                        COUNT(id) as jumlah 
                      FROM transaksi 
                      WHERE tanggal_transaksi >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      GROUP BY hari, jenis_layanan";
    $stmt_layanan = $pdo->query($query_layanan);
    while ($row = $stmt_layanan->fetch()) {
        // Konversi index hari PHP (1 = Minggu, 2 = Senin...) ke urutan array (Senin=0 ... Minggu=6)
        $idx = ($row['hari'] == 1) ? 6 : $row['hari'] - 2;
        if ($row['jenis_layanan'] == 'travel') {
            $data_travel[$idx] = (int)$row['jumlah'];
        } else {
            // Menggabungkan rental_supir dan lepas_kunci ke kelompok rental
            $data_rental[$idx] += (int)$row['jumlah'];
        }
    }

    // E. Penyebaran Tipe/Merk Kendaraan
    $query_tipe = "SELECT merk, COUNT(*) as jumlah FROM mobil GROUP BY merk";
    $stmt_tipe = $pdo->query($query_tipe);
    $tipe_labels = [];
    $tipe_data = [];
    while ($row = $stmt_tipe->fetch()) {
        $tipe_labels[] = $row['merk'];
        $tipe_data[] = (int)$row['jumlah'];
    }

    // F. Log Mobil Masuk Masa Maintenance
    $query_alert_maintenance = "SELECT merk, plat_nomor FROM mobil WHERE status_mobil = 'maintenance' LIMIT 3";
    $stmt_alert = $pdo->query($query_alert_maintenance);
    $alert_list = $stmt_alert->fetchAll();

    // G. Evaluasi Rating Supir Real-Time dari Tabel review_supir
    // Catatan: Jika ada tabel master `supir` untuk mengambil nama supir, query ini bisa di-JOIN nantinya. 
    // Sementara mengambil ID supir dan komentarnya.
    $query_rating = "SELECT r.rating_diberikan as rating, r.komentar as ulasan, r.created_at, r.supir_id 
                     FROM review_supir r 
                     ORDER BY r.created_at DESC LIMIT 4";
    $stmt_rating = $pdo->query($query_rating);
    $result_rating = $stmt_rating->fetchAll();

} catch (PDOException $e) {
    // Jika ada error query, tampilkan pesan aman agar dashboard tidak crash total
    echo "<script>console.error('Database Error: " . addslashes($e->getMessage()) . "');</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Kendali Admin | DITRAS</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              brand: {
                lightbg: '#faf9f6',
                darkbg: '#121212',
                cardlight: '#ffffff',
                carddark: '#1c1c1e',
                accent: '#059669', 
                blue: '#3b82f6'
              }
            },
            fontFamily: {
              serif: ['Cormorant Garamond', 'serif'],
              sans: ['Montserrat', 'sans-serif'],
            }
          }
        }
      }
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    
    <style>
      h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; }
      body { font-family: "Montserrat", sans-serif; }
      
      .fade-in { animation: fadeIn 0.6s ease-out forwards; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      
      .slide-in-right { animation: slideIn 1.2s cubic-bezier(0.19, 1, 0.22, 1) forwards; }
      @keyframes slideIn { from { opacity: 0; transform: translateX(60px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body class="bg-brand-lightbg text-stone-900 min-h-screen flex flex-col dark:bg-brand-darkbg dark:text-gray-100 transition-colors duration-300">

    <?php include '../../components/sidebar.php'; ?>
    <?php include '../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1 fade-in">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <p class="text-[12px] uppercase tracking-[0.2em] text-brand-accent font-bold">Monitor Operasional DITRAS</p>
                <h2 class="text-3xl italic font-semibold">Melayani 25 jam sek 1 jam nggo umbah-umbah</h2>
            </div>
            
            <button id="theme-toggle" class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 dark:bg-brand-carddark dark:border-zinc-800 text-brand-accent transition-all duration-300">
                <svg id="theme-icon-sun" class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg id="theme-icon-moon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
        </div>

        <!-- Row 1: Statistik Utama -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Total Pendapatan</p>
                <h3 class="text-3xl font-bold tracking-tight mb-2" id="count-revenue">Rp 0</h3>
                <span class="text-xs text-brand-accent bg-emerald-50 px-2 py-1 rounded-md dark:bg-emerald-950/50">47% ↑ <span class="text-gray-400 font-normal">dari bulan lalu</span></span>
            </div>
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Total Transaksi Selesai</p>
                <h3 class="text-3xl font-bold tracking-tight mb-2" id="count-orders">0</h3>
                <span class="text-xs text-brand-accent bg-emerald-50 px-2 py-1 rounded-md dark:bg-emerald-950/50">12% ↑ <span class="text-gray-400 font-normal">dari bulan lalu</span></span>
            </div>
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 flex items-center justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Efisiensi Bulanan</p>
                    <h3 class="text-2xl font-bold mb-1">Armada Aktif</h3>
                    <p class="text-xs text-gray-400">Target: 80% Unit Beroperasi (Saat Ini: <?php echo $efisiensi_persen; ?>%)</p>
                </div>
                <div class="w-24 h-24 relative">
                    <canvas id="chartSemiPie"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: Grafik Utama -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold font-serif italic">Grafik Omset Layanan (Real-Time)</h4>
                    <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded dark:bg-emerald-950">Titik Bergerak Aktif</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="chartGaris"></canvas>
                </div>
            </div>

            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <h4 class="text-lg font-semibold font-serif italic mb-4">Volume Pesanan Mingguan</h4>
                <div class="h-64 relative">
                    <canvas id="chartBatang"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 3: Detail Logistik & Fitur Baru (Evaluasi Rating) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <h4 class="text-lg font-semibold font-serif italic mb-4">Penyebaran Tipe Kendaraan</h4>
                <div class="h-56 relative flex justify-center">
                    <canvas id="chartLingkaran"></canvas>
                </div>
            </div>

            <!-- Panel Evaluasi Rating Supir -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 slide-in-right">
                <h4 class="text-lg font-semibold font-serif italic mb-2">Evaluasi Performa Supir</h4>
                <p class="text-[11px] text-gray-400 mb-4">Rating & Masukan pengguna secara real-time</p>
                <div class="space-y-3 max-h-56 overflow-y-auto pr-1">
                    <?php if (!empty($result_rating)): ?>
                        <?php foreach($result_rating as $row_rate): ?>
                            <div class="border-b border-gray-50 pb-2 dark:border-zinc-800/50 last:border-0">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold">Supir ID: <?php echo htmlspecialchars($row_rate['supir_id']); ?></span>
                                    <span class="text-xs text-amber-500 font-bold">★ <?php echo number_format($row_rate['rating'], 1); ?></span>
                                </div>
                                <p class="text-[11px] text-gray-500 italic dark:text-gray-400">"<?php echo htmlspecialchars($row_rate['ulasan'] ?: 'Tanpa ulasan teks'); ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-gray-400 text-center py-8">Belum ada data rating masuk.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Logistik Pemeliharaan -->
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 flex flex-col justify-between slide-in-right">
                <div>
                    <span class="text-[10px] text-red-500 font-bold uppercase tracking-wider block mb-2">Status Logistik Sistem</span>
                    <div class="space-y-2">
                        <?php if(!empty($alert_list)): ?>
                            <?php foreach($alert_list as $alert): ?>
                                <div class="flex items-center gap-3 bg-red-50 p-3 rounded-xl dark:bg-red-950/30">
                                    <div class="p-2 bg-red-500 text-white rounded-lg shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold"><?php echo htmlspecialchars($alert['merk']); ?></p>
                                        <p class="text-[10px] text-gray-400">Unit (<?php echo htmlspecialchars($alert['plat_nomor']); ?>) sedang dalam masa maintenance berkala.</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="flex items-center gap-3 bg-emerald-50 p-3 rounded-xl dark:bg-emerald-950/30">
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Semua unit armada dalam kondisi prima.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <button class="w-full bg-brand-blue text-white text-[10px] uppercase font-bold tracking-widest py-3 rounded-xl mt-4 hover:bg-blue-600 transition">Lihat Seluruh Log Perawatan</button>
            </div>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        // 1. ENGINE COUNTER OPERASIONAL
        function animateCounter(id, target, isCurrency = false) {
            let current = 0;
            const duration = 1200;
            const stepTime = 20;
            const steps = duration / stepTime;
            const increment = target / steps;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                document.getElementById(id).innerText = isCurrency 
                    ? 'Rp ' + Math.floor(current).toLocaleString('id-ID') 
                    : Math.floor(current).toLocaleString('id-ID');
            }, stepTime);
        }

        animateCounter('count-revenue', <?php echo $total_revenue; ?>, true);
        animateCounter('count-orders', <?php echo $total_completed; ?>, false);


        // ====================================================================
        // 2. DIAGRAM ENGINE INTERAKTIF
        // ====================================================================
        
        // A. DIAGRAM GARIS: Omset Layanan Real-time 7 Hari Terakhir
        const targetLabelsGaris = <?php echo json_encode($grafik_omset_labels); ?>;
        const targetDataGaris   = <?php echo json_encode($grafik_omset_data); ?>;

        const ctxGaris = document.getElementById('chartGaris').getContext('2d');
        const gradienGaris = ctxGaris.createLinearGradient(0, 0, 0, 250);
        gradienGaris.addColorStop(0, 'rgba(5, 150, 105, 0.4)');
        gradienGaris.addColorStop(1, 'rgba(5, 150, 105, 0)');

        const chartGaris = new Chart(ctxGaris, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Omset Harian',
                    data: [],
                    borderColor: '#059669',
                    borderWidth: 4,
                    fill: true,
                    backgroundColor: gradienGaris,
                    tension: 0.3,
                    pointRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#059669',
                    pointBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 0, grid: { display: false }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        let dataIndex = 0;
        function renderLineProgressive() {
            if (dataIndex < targetDataGaris.length) {
                chartGaris.data.labels.push(targetLabelsGaris[dataIndex]);
                chartGaris.data.datasets[0].data.push(targetDataGaris[dataIndex]);
                chartGaris.update();
                dataIndex++;
                setTimeout(renderLineProgressive, 200);
            }
        }
        setTimeout(renderLineProgressive, 300);


        // B. DIAGRAM BATANG: Volume Pesanan Mingguan (Travel vs Rental)
        const chartBatang = new Chart(document.getElementById('chartBatang'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($hari_nama); ?>,
                datasets: [
                    { label: 'Travel', data: [0,0,0,0,0,0,0], backgroundColor: '#059669', borderRadius: 6 },
                    { label: 'Rental', data: [0,0,0,0,0,0,0], backgroundColor: '#3b82f6', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, labels: { font: { size: 10 } } } },
                animation: { duration: 1200, easing: 'easeOutQuart' },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        setTimeout(() => {
            chartBatang.data.datasets[0].data = <?php echo json_encode($data_travel); ?>;
            chartBatang.data.datasets[1].data = <?php echo json_encode($data_rental); ?>;
            chartBatang.update();
        }, 500);


        // C. DIAGRAM LINGKARAN: Master Data Tipe Kendaraan
        const chartLingkaran = new Chart(document.getElementById('chartLingkaran'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($tipe_labels); ?>,
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#059669', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 10 } } } },
                animation: { animateScale: true, animateRotate: true, duration: 1200 }
            }
        });

        setTimeout(() => {
            chartLingkaran.data.datasets[0].data = <?php echo json_encode($tipe_data); ?>;
            chartLingkaran.update();
        }, 600);


        // D. DIAGRAM TARGET SEMI PIE: Armada Aktif (Jalan vs Tersedia)
        new Chart(document.getElementById('chartSemiPie'), {
            type: 'doughnut',
            data: {
                labels: ['Jalan', 'Tersedia'],
                datasets: [{ 
                    data: [<?php echo $armada_jalan; ?>, <?php echo $armada_tersedia; ?>], 
                    backgroundColor: ['#059669', '#e4e4e7'], 
                    borderWidth: 0 
                }]
            },
            options: {
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                rotation: -90, 
                circumference: 180, 
                cutout: '75%'
            }
        });


        // 3. LOGIKA TEMA GELAP/TERANG (SMOOTH TRANSITION)
        const themeToggle = document.getElementById('theme-toggle');
        const themeIconSun = document.getElementById('theme-icon-sun');
        const themeIconMoon = document.getElementById('theme-icon-moon');

        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeIconSun.classList.add('hidden');
            themeIconMoon.classList.remove('hidden');
        } else {
            document.documentElement.classList.remove('dark');
            themeIconSun.classList.remove('hidden');
            themeIconMoon.classList.add('hidden');
        }

        themeToggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            themeIconSun.classList.toggle('hidden');
            themeIconMoon.classList.toggle('hidden');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>
</html>