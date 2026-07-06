<?php
// pages/pembeli/dashboard.php
session_start();

// Naik 2 tingkat untuk kembali ke root DITRAS-SYSTEM/
require_once '../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Menangkap data pencarian jika ada
$search_asal = $_GET['asal'] ?? '';
$search_tujuan = $_GET['tujuan'] ?? '';

try {
    // QUERY DENGAN FILTER OTOMATISASI KEDALUWARSA (KELIPATAN 24 JAM)
    $query = "
        SELECT 
            jk.id AS id_jadwal,
            jk.tanggal_berangkat,
            jk.jam_berangkat,
            jk.sisa_kursi,
            r.harga_dasar,
            r.nama_rute,
            r.estimasi_waktu,
            u.nama AS nama_supir,
            CONCAT(jk.tanggal_berangkat, ' ', jk.jam_berangkat) AS waktu_keberangkatan
        FROM jadwal_keberangkatan jk
        INNER JOIN rute r ON jk.rute_id = r.id
        LEFT JOIN users u ON jk.supir_id = u.id AND u.role = 'supir'
        WHERE 1=1
    ";

    $params = [];

    // Filter Pencarian Tradisional
    if (!empty($search_asal)) {
        $query .= " AND r.nama_rute LIKE ? ";
        $params[] = "%$search_asal%";
    }
    if (!empty($search_tujuan)) {
        $query .= " AND r.nama_rute LIKE ? ";
        $params[] = "%$search_tujuan%";
    }

    $query .= " ORDER BY jk.tanggal_berangkat ASC, jk.jam_berangkat ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $semua_jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // FILTER LOGIKA DI SISI PHP UNTUK USER COMFORT & KELIPATAN 24 JAM
    $daftar_jadwal = [];
    $waktu_sekarang = new DateTime(); // Waktu saat ini

    foreach ($semua_jadwal as $jadwal) {
        $waktu_berangkat = new DateTime($jadwal['waktu_keberangkatan']);
        
        preg_match('/\d+/', $jadwal['estimasi_waktu'], $matches);
        $durasi_jam = isset($matches[0]) ? (int)$matches[0] : 24;

        $batas_jam_tampil = ceil($durasi_jam / 24) * 24;

        $waktu_kedaluwarsa = clone $waktu_berangkat;
        $waktu_kedaluwarsa->modify("+" . $batas_jam_tampil . " hours");

        if ($waktu_sekarang < $waktu_kedaluwarsa) {
            $daftar_jadwal[] = $jadwal;
        }
    }

} catch (PDOException $e) {
    die("Gagal memuat jadwal perjalanan: " . $e->getMessage());
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
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang di DITRAS</h2>
                <p class="text-slate-500 mt-1 text-sm">Pilih layanan premium kami atau pantau perjalanan aktif Anda dari satu dashboard.</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sistem Terintegrasi
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl mb-8">
            
            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col justify-between group hover:border-emerald-500/30 transition-all">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">Layanan 1</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Tiket Travel Reguler</h3>
                    <p class="text-slate-400 text-xs mt-1 mb-4 leading-relaxed">Cari jadwal manifes aktif dan pesan kursi perjalanan reguler secara instan.</p>
                    
                    <form method="GET" action="" class="space-y-3">
                        <div class="relative">
                            <i class="fa-solid fa-location-dot absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="text" name="asal" value="<?= htmlspecialchars($search_asal) ?>" placeholder="Kota Asal..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 pl-8 text-xs focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-location-arrow absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="text" name="tujuan" value="<?= htmlspecialchars($search_tujuan) ?>" placeholder="Kota Tujuan..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 pl-8 text-xs focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                        </div>
                        <button type="submit" class="w-full bg-slate-900 hover:bg-emerald-600 text-white font-semibold text-xs py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-magnifying-glass text-[10px]"></i> Cari Manifes Aktif
                        </button>
                    </form>
                </div>
            </div>

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
                    <a href="sewa-mobil-supir.php" class="w-full inline-flex bg-slate-100 hover:bg-emerald-50 group-hover:bg-emerald-600 group-hover:text-white text-slate-700 font-semibold text-xs py-2.5 px-4 rounded-lg transition duration-200 items-center justify-center gap-1.5">
                        Pilih Armada Premium <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

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
                    <a href="rental-lepas-kunci.php" class="w-full inline-flex bg-slate-100 hover:bg-emerald-50 group-hover:bg-emerald-600 group-hover:text-white text-slate-700 font-semibold text-xs py-2.5 px-4 rounded-lg transition duration-200 items-center justify-center gap-1.5">
                        Setir Sendiri Sekarang <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl items-start">
            
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-[0_4px_12px_-5px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Papan Manifes Keberangkatan</span>
                    <span class="text-xs text-slate-500 font-medium bg-slate-200/60 px-2.5 py-1 rounded-md">Total: <?= count($daftar_jadwal) ?> Jadwal</span>
                </div>

                <?php if (empty($daftar_jadwal)): ?>
                    <div class="p-16 text-center text-slate-400 text-sm">
                        <i class="fa-solid fa-square-poll-horizontal text-4xl text-slate-200 mb-3 block"></i>
                        Belum ada jadwal perjalanan aktif atau rute terdekat yang sesuai pencarian saat ini.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase bg-slate-50/30">
                                    <th class="py-4 px-6">Informasi Rute</th>
                                    <th class="py-4 px-4">Keberangkatan</th>
                                    <th class="py-4 px-4">Ketersediaan</th>
                                    <th class="py-4 px-4 text-right">Tarif</th>
                                    <th class="py-4 px-6 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                                <?php foreach ($daftar_jadwal as $jadwal): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors"><?= htmlspecialchars($jadwal['nama_rute']) ?></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5"><i class="fa-solid fa-clock-rotate-left"></i> Est: <?= htmlspecialchars($jadwal['estimasi_waktu'] ?? '-') ?></div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-slate-900 font-semibold"><?= date('H:i', strtotime($jadwal['jam_berangkat'])) ?> <span class="text-[10px] font-normal text-slate-400">WIB</span></div>
                                            <div class="text-[11px] text-slate-400 font-normal"><?= date('d M Y', strtotime($jadwal['tanggal_berangkat'])) ?></div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($jadwal['sisa_kursi'] > 0): ?>
                                                <span class="text-emerald-700 bg-emerald-50 border border-emerald-100 text-[11px] px-2 py-0.5 rounded-md font-bold">
                                                    <?= htmlspecialchars($jadwal['sisa_kursi']) ?> Kursi
                                                </span>
                                            <?php else: ?>
                                                <span class="text-rose-600 bg-rose-50 border border-rose-100 text-[11px] px-2 py-0.5 rounded-md font-bold">Penuh</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold text-slate-900">
                                            Rp<?= number_format($jadwal['harga_dasar'], 0, ',', '.') ?>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <?php if ($jadwal['sisa_kursi'] > 0): ?>
                                                <a href="travel/pesan.php?jadwal_id=<?= $jadwal['id_jadwal'] ?>" class="inline-block bg-slate-900 hover:bg-emerald-600 text-white font-semibold text-[11px] px-3 py-1.5 rounded-lg transition shadow-sm">
                                                    Pesan
                                                </a>
                                            <?php else: ?>
                                                <button disabled class="bg-slate-100 text-slate-400 font-semibold text-[11px] px-3 py-1.5 rounded-lg cursor-not-allowed">Habis</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                
                <div class="bg-white p-5 rounded-2xl shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100 group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-slate-100 text-slate-700 group-hover:bg-emerald-50 group-hover:text-emerald-600 rounded-lg flex items-center justify-center text-sm transition-colors">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Riwayat Transaksi</h4>
                            <p class="text-[11px] text-slate-400">Pantau tiket aktif dan manifestasi Anda.</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                        Ingin memeriksa kembali e-tiket atau status pembayaran sewa mobil? Riwayat Anda tersusun rapi.
                    </p>
                    <a href="riwayat-transaksi.php" class="w-full text-center mt-3 inline-block bg-slate-900 hover:bg-emerald-600 text-white text-xs font-semibold py-2 rounded-lg transition shadow-sm">
                        Lihat Semua Riwayat
                    </a>
                </div>

                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-2xl shadow-md text-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 text-slate-700/20 text-7xl font-bold">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <span class="text-[9px] bg-emerald-500 text-white font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">Fitur Tambahan</span>
                    <h4 class="text-sm font-bold mt-2 text-white">Suarakan Rute Impianmu!</h4>
                    <p class="text-slate-300 text-[11px] mt-1 mb-4 leading-relaxed">Punya rute reguler langganan yang belum tercover oleh armada DITRAS Premium Travel?</p>
                    
                    <a href="usulan-rute-baru.php" class="inline-flex bg-white hover:bg-emerald-500 hover:text-white text-slate-900 font-bold text-[11px] py-2 px-3 rounded-lg transition duration-200 items-center gap-1.5 shadow-sm">
                        Usulkan Rute Baru <i class="fa-solid fa-paper-plane text-[9px]"></i>
                    </a>
                </div>

            </div>

        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>