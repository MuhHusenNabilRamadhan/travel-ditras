<?php
// pages/pembeli/dashboard.php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Utama | DITRAS Premium Travel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex flex-col min-h-screen antialiased">
    
    <?php 
    include '../../components/sidebar.php'; 
    include '../../components/header.php'; 
    ?>
    
    <main class="ml-64 p-8 flex-1">
        <!-- HEADER DASHBOARD -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang di DITRAS</h2>
                <p class="text-slate-500 mt-1 text-sm">Pilih layanan premium kami atau pantau perjalanan aktif Anda dari satu dashboard.</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sistem Terintegrasi
            </div>
        </div>

        <!-- GRID UTAMA: 3 MENU LAYANAN INTENSIF (Baris Pertama) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl mb-8">
            
            <!-- CARD 1: TIKET TRAVEL REGULER -->
            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col justify-between group hover:border-emerald-500/30 transition-all">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">Layanan 1</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Tiket Travel Reguler</h3>
                    <p class="text-slate-400 text-xs mt-1 mb-6 leading-relaxed">Cari jadwal manifes aktif, tentukan kota asal dan tujuan, serta pesan kursi perjalanan reguler secara instan.</p>
                </div>
                <div>
                    <a href="travel/search.php" class="w-full inline-flex bg-slate-900 hover:bg-emerald-600 text-white font-semibold text-xs py-3 px-4 rounded-xl transition duration-200 items-center justify-center gap-2 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-[10px]"></i> Cari Manifes Aktif
                    </a>
                </div>
            </div>

            <!-- CARD 2: SEWA MOBIL + SUPIR -->
            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col justify-between group hover:border-emerald-500/30 transition-all">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">Layanan 2</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Sewa Mobil + Supir</h3>
                    <p class="text-slate-400 text-xs mt-1 leading-relaxed">Perjalanan eksklusif dengan pengemudi profesional yang ramah dan berpengalaman luas.</p>
                    
                    <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 text-center">
                        <p class="text-[11px] text-slate-500 font-medium">Armada Premium Tersedia:</p>
                        <p class="text-xs font-bold text-slate-700 mt-0.5">HiAce Luxury, Alphard, Innova Zenix</p>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="rental-supir/form-sewa.php" class="w-full inline-flex bg-slate-100 hover:bg-emerald-50 group-hover:bg-emerald-600 group-hover:text-white text-slate-700 font-semibold text-xs py-2.5 px-4 rounded-lg transition duration-200 items-center justify-center gap-1.5">
                        Pilih Armada Premium <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 3: RENTAL LEPAS KUNCI -->
            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col justify-between group hover:border-emerald-500/30 transition-all">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">Layanan 3</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Rental Lepas Kunci</h3>
                    <p class="text-slate-400 text-xs mt-1 leading-relaxed">Nikmati kebebasan penuh berkendara sendiri dengan pilihan unit mobil transmisi manual maupun matic.</p>
                    
                    <div class="mt-4 flex gap-2 justify-center">
                        <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-100 px-2 py-1 rounded-md font-medium"><i class="fa-solid fa-check"></i> Proses Cepat</span>
                        <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-100 px-2 py-1 rounded-md font-medium"><i class="fa-solid fa-check"></i> Unit Steril</span>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="lepas-kunci/form-booking.php" class="w-full inline-flex bg-slate-100 hover:bg-emerald-50 group-hover:bg-emerald-600 group-hover:text-white text-slate-700 font-semibold text-xs py-2.5 px-4 rounded-lg transition duration-200 items-center justify-center gap-1.5">
                        Setir Sendiri Sekarang <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- BARIS KEDUA: HUB TRANSAKSI & USULAN -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-6xl items-start">
            
            <!-- CARD 4: DATA TRANSAKSI & AKTIVITAS TERAKHIR -->
            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100 group flex flex-col justify-between h-full">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-slate-100 text-slate-700 group-hover:bg-emerald-50 group-hover:text-emerald-600 rounded-xl flex items-center justify-center text-lg transition-colors">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Riwayat Transaksi</h4>
                            <p class="text-[11px] text-slate-400">Pantau tiket aktif dan berkas manifes Anda.</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100 mb-4">
                        Ingin memeriksa kembali e-tiket perjalanan reguler atau mengecek status tagihan sewa armada? Riwayat transaksi Anda tersusun rapi di sini.
                    </p>
                </div>
                <div>
                    <a href="riwayat.php" class="w-full text-center inline-block bg-slate-900 hover:bg-emerald-600 text-white text-xs font-semibold py-3 rounded-xl transition shadow-sm">
                        Lihat Semua Riwayat
                    </a>
                </div>
            </div>

            <!-- CARD 5: USULAN RUTE BARU -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 rounded-2xl shadow-md text-white relative overflow-hidden group flex flex-col justify-between h-full">
                <div class="absolute -right-4 -bottom-4 text-slate-700/20 text-7xl font-bold">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <div class="mb-2">
                        <span class="text-[9px] bg-emerald-500 text-white font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">Fitur Tambahan</span>
                    </div>
                    <h4 class="text-base font-bold mt-2 text-white">Suarakan Rute Impianmu!</h4>
                    <p class="text-slate-300 text-xs mt-1 mb-6 leading-relaxed">Punya rute reguler langganan harian yang belum tercover oleh operasional armada resmi DITRAS Premium Travel?</p>
                </div>
                <div>
                    <a href="aspirasi/form.php" class="inline-flex bg-white hover:bg-emerald-500 hover:text-white text-slate-900 font-bold text-xs py-3 px-4 rounded-xl transition duration-200 items-center gap-1.5 shadow-sm">
                        Usulkan Rute Baru <i class="fa-solid fa-paper-plane text-[9px]"></i>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>