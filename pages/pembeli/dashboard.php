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
            -- Menggabungkan tanggal dan jam menjadi format DATETIME
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
        
        // Mengambil angka jam saja dari string estimasi_waktu (Contoh: "4 jam" atau "50 jam" -> diambil 4 atau 50)
        preg_match('/\d+/', $jadwal['estimasi_waktu'], $matches);
        $durasi_jam = isset($matches[0]) ? (int)$matches[0] : 24; // Default ke 24 jam jika kosong

        // Logika Batas Kelipatan 24 Jam (CEIL ke kelipatan 24)
        // Misal 4 jam -> CEIL(4/24) * 24 = 24 jam. Misal 50 jam -> CEIL(50/24) * 24 = 72 jam.
        $batas_jam_tampil = ceil($durasi_jam / 24) * 24;

        // Tentukan batas waktu maksimal jadwal boleh tampil di layar user
        $waktu_kedaluwarsa = clone $waktu_berangkat;
        $waktu_kedaluwarsa->modify("+" . $batas_jam_tampil . " hours");

        // Jadwal hanya dimasukkan jika waktu sekarang BELUM melewati batas kedaluwarsa
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
    <title>Manifes Perjalanan | DITRAS</title>
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
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Papan Jadwal Keberangkatan</h2>
                <p class="text-slate-500 mt-1 text-sm">Menampilkan rute operasional aktif. Jadwal kedaluwarsa otomatis disembunyikan.</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Mode Manifes Otomatis
            </div>
        </div>

        <!-- FITUR PENCARIAN PREMIUM -->
        <div class="bg-white p-5 rounded-2xl mb-8 max-w-6xl shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="text-xs font-semibold text-slate-500 block mb-2 uppercase tracking-wider">Kota Asal</label>
                    <div class="relative">
                        <i class="fa-solid fa-location-dot absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                        <input type="text" name="asal" value="<?= htmlspecialchars($search_asal) ?>" placeholder="Masukkan kota asal..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-10 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all placeholder:text-slate-400">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 block mb-2 uppercase tracking-wider">Kota Tujuan</label>
                    <div class="relative">
                        <i class="fa-solid fa-location-arrow absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                        <input type="text" name="tujuan" value="<?= htmlspecialchars($search_tujuan) ?>" placeholder="Masukkan kota tujuan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-10 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all placeholder:text-slate-400">
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-emerald-700 text-white font-semibold text-sm py-3.5 px-6 rounded-xl transition duration-200 flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i> Cari Jadwal
                    </button>
                </div>
            </form>
        </div>

        <!-- DAFTAR JADWAL MODERN -->
        <div class="max-w-6xl bg-white rounded-2xl shadow-[0_4px_12px_-5px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Manifes Aktif</span>
                <span class="text-xs text-slate-500 font-medium bg-slate-200/60 px-2.5 py-1 rounded-md">Total: <?= count($daftar_jadwal) ?> Jadwal</span>
            </div>

            <?php if (empty($daftar_jadwal)): ?>
                <div class="p-16 text-center text-slate-400 text-sm">
                    <i class="fa-solid fa-square-poll-horizontal text-4xl text-slate-200 mb-3 block"></i>
                    Belum ada jadwal perjalanan aktif atau rute terdekat yang tersedia saat ini.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase bg-slate-50/30">
                                <th class="py-4 px-6">Informasi Rute</th>
                                <th class="py-4 px-4">Waktu Keberangkatan</th>
                                <th class="py-4 px-4">Estimasi / Durasi</th>
                                <th class="py-4 px-4">Supir Penanggung Jawab</th>
                                <th class="py-4 px-4">Ketersediaan</th>
                                <th class="py-4 px-4 text-right">Tarif Tiket</th>
                                <th class="py-4 px-6 text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                            <?php foreach ($daftar_jadwal as $jadwal): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <!-- Rute -->
                                    <td class="py-5 px-6">
                                        <div class="font-bold text-slate-900 text-base group-hover:text-emerald-700 transition-colors"><?= htmlspecialchars($jadwal['nama_rute']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-medium mt-1 flex items-center gap-1">
                                            <i class="fa-solid fa-shield-halved text-emerald-500 text-[10px]"></i> Layanan Resmi DITRAS-SYSTEM
                                        </div>
                                    </td>
                                    
                                    <!-- Waktu Keberangkatan -->
                                    <td class="py-5 px-4">
                                        <div class="text-slate-900 font-semibold flex items-center gap-2">
                                            <i class="fa-regular fa-clock text-slate-400"></i>
                                            <?= date('H:i', strtotime($jadwal['jam_berangkat'])) ?> <span class="text-xs font-normal text-slate-400">WIB</span>
                                        </div>
                                        <div class="text-xs text-slate-400 font-normal mt-1">
                                            <?= date('d M Y', strtotime($jadwal['tanggal_berangkat'])) ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Estimasi Waktu -->
                                    <td class="py-5 px-4">
                                        <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                            <i class="fa-solid fa-circle-notch text-[10px] text-slate-400"></i>
                                            <?= htmlspecialchars($jadwal['estimasi_waktu'] ?? '-') ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Nama Supir -->
                                    <td class="py-5 px-4 text-slate-600">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[10px] border border-slate-200">
                                                <i class="fa-solid fa-user-tie"></i>
                                            </div>
                                            <span class="text-xs font-semibold"><?= htmlspecialchars($jadwal['nama_supir'] ?? 'Belum Ditunjuk') ?></span>
                                        </div>
                                    </td>
                                    
                                    <!-- Sisa Kursi -->
                                    <td class="py-5 px-4">
                                        <?php if ($jadwal['sisa_kursi'] > 0): ?>
                                            <span class="text-emerald-700 bg-emerald-50/60 border border-emerald-100 text-xs px-2.5 py-1 rounded-md font-bold">
                                                <?= htmlspecialchars($jadwal['sisa_kursi']) ?> Kursi Sisa
                                            </span>
                                        <?php else: ?>
                                            <span class="text-rose-600 bg-rose-50 border border-rose-100 text-xs px-2.5 py-1 rounded-md font-bold">
                                                Penuh
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Harga -->
                                    <td class="py-5 px-4 text-right font-bold text-slate-900 text-base">
                                        Rp <?= number_format($jadwal['harga_dasar'], 0, ',', '.') ?>
                                    </td>
                                    
                                    <!-- Tombol Pesan -->
                                    <td class="py-5 px-6 text-center">
                                        <?php if ($jadwal['sisa_kursi'] > 0): ?>
                                            <a href="travel/pesan.php?jadwal_id=<?= $jadwal['id_jadwal'] ?>" class="inline-block bg-slate-900 hover:bg-emerald-600 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition shadow-sm">
                                                Pesan Tiket
                                            </a>
                                        <?php else: ?>
                                            <button disabled class="bg-slate-100 text-slate-400 font-semibold text-xs px-4 py-2.5 rounded-xl cursor-not-allowed">
                                                Terjual Habis
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>