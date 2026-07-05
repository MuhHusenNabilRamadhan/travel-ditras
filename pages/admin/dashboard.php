<?php
// pages/admin/dashboard.php
session_start();
require_once '../../config/database.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
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
      
      /* Animasi Transisi Konten Utama */
      .fade-in { animation: fadeIn 0.6s ease-out forwards; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      
      /* Animasi Meluncur Aktif dari Kanan ke Kiri untuk Panel Anggaran & Perawatan */
      .slide-in-right { opacity: 0; animation: slideIn 1.2s cubic-bezier(0.19, 1, 0.22, 1) forwards; }
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
                    <p class="text-xs text-gray-400">Target: 80% Unit Beroperasi</p>
                </div>
                <div class="w-24 h-24 relative">
                    <canvas id="chartSemiPie"></canvas>
                </div>
            </div>
        </div>

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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800">
                <h4 class="text-lg font-semibold font-serif italic mb-4">Penyebaran Tipe Kendaraan</h4>
                <div class="h-56 relative flex justify-center">
                    <canvas id="chartLingkaran"></canvas>
                </div>
            </div>

            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 slide-in-right">
                <h4 class="text-lg font-semibold font-serif italic mb-4">Penyerapan Dana Pemasaran</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs mb-1"><span>Iklan Google Digital Travel</span><span class="font-bold">69%</span></div>
                        <div class="w-full bg-gray-100 h-2 rounded-full dark:bg-zinc-700"><div class="bg-brand-accent h-full rounded-full transition-all duration-1000 ease-out" id="bar-dana-1" style="width: 0%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1"><span>Promosi Media Sosial</span><span class="font-bold">78%</span></div>
                        <div class="w-full bg-gray-100 h-2 rounded-full dark:bg-zinc-700"><div class="bg-brand-blue h-full rounded-full transition-all duration-1000 ease-out" id="bar-dana-2" style="width: 0%"></div></div>
                    </div>
                </div>
            </div>

            <div class="bg-brand-cardlight p-6 rounded-2xl border border-gray-100 shadow-sm dark:bg-brand-carddark dark:border-zinc-800 flex flex-col justify-between slide-in-right">
                <div>
                    <span class="text-[10px] text-red-500 font-bold uppercase tracking-wider block mb-2">Status Logistik Sistem</span>
                    <div class="flex items-center gap-3 bg-red-50 p-3 rounded-xl dark:bg-red-950/30">
                        <div class="p-2 bg-red-500 text-white rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold">Pemeliharaan Armada</p>
                            <p class="text-[10px] text-gray-400">3 Unit Isuzu Elf terdeteksi masuk masa ganti oli berkala.</p>
                        </div>
                    </div>
                </div>
                <button class="w-full bg-brand-blue text-white text-[10px] uppercase font-bold tracking-widest py-3 rounded-xl mt-4 hover:bg-blue-600 transition">Lihat Seluruh Log Perawatan</button>
            </div>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        // 1. ENGINE COUNTER ANGKA
        function animateCounter(id, target, isCurrency = false) {
            let current = 0;
            const duration = 1500;
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

        animateCounter('count-revenue', 48295000, true);
        animateCounter('count-orders', 1284, false);

        // Memicu Bar kemajuan setelah komponen bergeser masuk
        setTimeout(() => {
            document.getElementById('bar-dana-1').style.width = '69%';
            document.getElementById('bar-dana-2').style.width = '78%';
        }, 300);


        // ====================================================================
        // 2. ENGINE ANIMASI DIAGRAM REAL-TIME (KINETIK PROGRESSIVE)
        // ====================================================================
        
        // Data Sumber untuk Diagram Garis
        const targetLabelsGaris = ['10 Apr', '11 Apr', '12 Apr', '13 Apr', '14 Apr', '15 Apr', '16 Apr'];
        const targetDataGaris   = [12000000, 24000000, 18000000, 31000000, 23000000, 35000000, 42000000];

        const ctxGaris = document.getElementById('chartGaris').getContext('2d');
        const gradienGaris = ctxGaris.createLinearGradient(0, 0, 0, 250);
        gradienGaris.addColorStop(0, 'rgba(5, 150, 105, 0.4)');
        gradienGaris.addColorStop(1, 'rgba(5, 150, 105, 0)');

        // DIAGRAM GARIS: Diinisialisasi KOSONG agar titik bisa digambar meluncur ke kanan
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
                animation: {
                    duration: 450, // Durasi luncuran titik antar koordinat (Sangat Halus)
                    easing: 'easeOutQuad'
                },
                scales: {
                    y: { min: 0, max: 50000000, grid: { display: false }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // FUNGSI UTAMA: Membuat titik berjalan maju ke depan secara berkala (Star Trailing Effect)
        let dataIndex = 0;
        function renderLineProgressive() {
            if (dataIndex < targetDataGaris.length) {
                chartGaris.data.labels.push(targetLabelsGaris[dataIndex]);
                chartGaris.data.datasets[0].data.push(targetDataGaris[dataIndex]);
                chartGaris.update();
                dataIndex++;
                setTimeout(renderLineProgressive, 350); // Kecepatan luncuran titik ke titik berikutnya
            }
        }
        // Jalankan efek bintang berjalan setelah struktur dashboard termuat
        setTimeout(renderLineProgressive, 500);


        // B. DIAGRAM BATANG: Diinisialisasi dari angka 0, lalu meroket tumbuh elastis ke atas
        const chartBatang = new Chart(document.getElementById('chartBatang'), {
            type: 'bar',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [
                    { label: 'Travel', data: [0, 0, 0, 0, 0, 0, 0], backgroundColor: '#059669', borderRadius: 6 },
                    { label: 'Rental', data: [0, 0, 0, 0, 0, 0, 0], backgroundColor: '#3b82f6', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, labels: { font: { size: 10 } } } },
                animation: {
                    duration: 2000,
                    easing: 'easeOutElastic' // Efek pegas elastis murni dari bawah ke atas
                },
                scales: {
                    y: { max: 100, grid: { display: false }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // Picu pertumbuhan diagram batang sesaat setelah inisialisasi awal
        setTimeout(() => {
            chartBatang.data.datasets[0].data = [40, 35, 55, 30, 70, 85, 90];
            chartBatang.data.datasets[1].data = [20, 25, 30, 45, 50, 65, 60];
            chartBatang.update();
        }, 600);


        // C. DIAGRAM LINGKARAN: Diinisialisasi dari 0, mekar melingkar berputar dari titik tengah
        const chartLingkaran = new Chart(document.getElementById('chartLingkaran'), {
            type: 'pie',
            data: {
                labels: ['Mobil Pribadi', 'Elf / Microbus', 'Bus Pariwisata'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#059669', '#3b82f6', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 10 } } } },
                animation: {
                    animateScale: true,  // Mekar mengembang dari 0% di titik tengah
                    animateRotate: true, // Berputar dinamis secara simultan
                    duration: 1800,
                    easing: 'easeOutBack'
                }
            }
        });

        // Picu pemekaran diagram lingkaran dari poros tengah
        setTimeout(() => {
            chartLingkaran.data.datasets[0].data = [50, 30, 20];
            chartLingkaran.update();
        }, 700);


        // D. DIAGRAM TARGET SEMI PIE
        new Chart(document.getElementById('chartSemiPie'), {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Standby'],
                datasets: [{ data: [65, 35], backgroundColor: ['#059669', '#e4e4e7'], borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                rotation: -90, circumference: 180, cutout: '75%',
                animation: { animateScale: true, duration: 2000, easing: 'easeOutBack' }
            }
        });

        // --- 3. LOGIKA TEMA GELAP/TERANG ---
        const themeToggle = document.getElementById('theme-toggle');
        const themeIconSun = document.getElementById('theme-icon-sun');
        const themeIconMoon = document.getElementById('theme-icon-moon');

        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.classList.add(currentTheme);
        if (currentTheme === 'dark') {
            themeIconSun.classList.add('hidden');
            themeIconMoon.classList.remove('hidden');
        }

        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            themeIconSun.classList.toggle('hidden');
            themeIconMoon.classList.toggle('hidden');
            
            let theme = 'light';
            if (document.documentElement.classList.contains('dark')) { theme = 'dark'; }
            localStorage.setItem('theme', theme);
            setTimeout(() => { location.reload(); }, 150);
        });
    </script>
</body>
</html>