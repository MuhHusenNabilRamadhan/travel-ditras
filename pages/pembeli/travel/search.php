<?php
// pages/pembeli/travel/search.php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../../auth/login.php");
    exit;
}

// Menangkap data pencarian kota asal & tujuan
$search_asal = $_GET['asal'] ?? '';
$search_tujuan = $_GET['tujuan'] ?? '';

try {
    // KUERI UTAMA: Mengambil semua data rute, mobil, tarif, dan supir
    $query_str = "
        SELECT 
            jk.id,
            jk.jam_berangkat,
            jk.tanggal_berangkat,
            jk.sisa_kursi,
            r.nama_rute,
            r.harga_dasar, -- Diambil untuk tarif tiket
            r.estimasi_waktu, -- Diambil untuk kalkulasi kelipatan 24 jam
            m.merk,
            m.plat_nomor,
            u.nama AS nama_supir,
            CONCAT(jk.tanggal_berangkat, ' ', jk.jam_berangkat) AS waktu_keberangkatan
        FROM jadwal_keberangkatan jk
        JOIN rute r ON jk.rute_id = r.id
        JOIN mobil m ON jk.mobil_id = m.id
        LEFT JOIN users u ON jk.supir_id = u.id AND u.role = 'supir'
        WHERE 1=1
    ";

    $params = [];

    // Filter pencarian berdasarkan kota asal / tujuan di nama_rute
    if (!empty($search_asal)) {
        $query_str .= " AND r.nama_rute LIKE ? ";
        $params[] = "%$search_asal%";
    }
    if (!empty($search_tujuan)) {
        $query_str .= " AND r.nama_rute LIKE ? ";
        $params[] = "%$search_tujuan%";
    }

    $query_str .= " ORDER BY jk.tanggal_berangkat ASC, jk.jam_berangkat ASC";

    $stmt = $pdo->prepare($query_str);
    $stmt->execute($params);
    $semua_jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // LOGIKA FILTER KEDALUWARSA OTOMATIS (KELIPATAN 24 JAM)
    $daftar_jadwal = [];
    $waktu_sekarang = new DateTime(); 

    foreach ($semua_jadwal as $jadwal) {
        $waktu_berangkat = new DateTime($jadwal['waktu_keberangkatan']);
        
        // Mengambil angka jam dari kolom estimasi_waktu
        preg_match('/\d+/', $jadwal['estimasi_waktu'], $matches);
        $durasi_jam = isset($matches[0]) ? (int)$matches[0] : 24; 

        // Pembulatan ke atas kelipatan 24 jam (CEIL)
        $batas_jam_tampil = ceil($durasi_jam / 24) * 24;

        $waktu_kedaluwarsa = clone $waktu_berangkat;
        $waktu_kedaluwarsa->modify("+" . $batas_jam_tampil . " hours");

        // Hanya tampilkan jika belum melewati batas kedaluwarsa
        if ($waktu_sekarang < $waktu_kedaluwarsa) {
            $daftar_jadwal[] = $jadwal;
        }
    }

} catch (PDOException $e) {
    $daftar_jadwal = [];
    $error_db = "Gagal mengambil info perjalanan: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Jadwal Perjalanan | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: "Plus Jakarta Sans", sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen flex antialiased">

    <?php include '../../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 ml-64">
        <!-- HEADER SISTEM -->
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between shadow-sm">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Sistem DITRAS</h1>
        </header>

        <main class="p-8 max-w-7xl w-full mx-auto flex-1">
            
            <!-- TITEL UTAMA -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Papan Jadwal Keberangkatan</h2>
                <p class="text-slate-500 mt-1 text-sm">Menampilkan rute operasional aktif. Jadwal kedaluwarsa otomatis disembunyikan.</p>
            </div>

            <?php if (isset($error_db)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-xs font-mono border border-red-100"><?= $error_db; ?></div>
            <?php endif; ?>

            <!-- INPUT PENCARIAN (SAMA SEPERTI DI FOTO) -->
            <div class="bg-white p-5 rounded-2xl mb-8 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100">
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="text-xs font-bold text-slate-400 block mb-2 uppercase tracking-wider">Kota Asal</label>
                        <div class="relative">
                            <i class="fa-solid fa-location-dot absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <input type="text" name="asal" value="<?= htmlspecialchars($search_asal) ?>" placeholder="Masukkan kota asal..." class="w-full bg-slate-50/50 border border-slate-200 rounded-xl p-3 pl-10 text-sm focus:outline-none focus:border-slate-400 focus:bg-white transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 block mb-2 uppercase tracking-wider">Kota Tujuan</label>
                        <div class="relative">
                            <i class="fa-solid fa-location-arrow absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <input type="text" name="tujuan" value="<?= htmlspecialchars($search_tujuan) ?>" placeholder="Masukkan kota tujuan..." class="w-full bg-slate-50/50 border border-slate-200 rounded-xl p-3 pl-10 text-sm focus:outline-none focus:border-slate-400 focus:bg-white transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-[#111827] hover:bg-slate-800 text-white font-semibold text-sm py-3.5 px-6 rounded-xl transition duration-200 flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i> Cari Jadwal
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABEL JADWAL MANIFES -->
            <div class="bg-white border border-slate-100 shadow-[0_4px_12px_-5px_rgba(0,0,0,0.05)] rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Manifes Aktif</span>
                    <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-3 py-1 rounded-md">Total: <?= count($daftar_jadwal) ?> Jadwal</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase bg-slate-50/30 tracking-wider">
                                <th class="p-5 pl-6">Informasi Rute</th>
                                <th class="p-5">Waktu Keberangkatan</th>
                                <th class="p-5">Estimasi / Durasi</th>
                                <th class="p-5">Supir Penanggung Jawab</th>
                                <th class="p-5">Ketersediaan</th>
                                <th class="p-5 text-right">Tarif Tiket</th>
                                <th class="p-5 pr-6 text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100 font-medium text-slate-700">
                            <?php if (!empty($daftar_jadwal)): ?>
                                <?php foreach ($daftar_jadwal as $jadwal): ?>
                                    <tr class="hover:bg-slate-50/50 transition group">
                                        <!-- Rute & Detail Mobil -->
                                        <td class="p-5 pl-6">
                                            <div class="font-bold text-slate-900 text-base"><?= htmlspecialchars($jadwal['nama_rute']); ?></div>
                                            <div class="text-[11px] text-slate-400 font-normal mt-1 flex items-center gap-1.5">
                                                <i class="fa-solid fa-bus text-emerald-600 text-[10px]"></i> 
                                                <span><?= htmlspecialchars($jadwal['merk']); ?> (<?= htmlspecialchars($jadwal['plat_nomor']); ?>)</span>
                                            </div>
                                        </td>
                                        
                                        <!-- Waktu Keberangkatan -->
                                        <td class="p-5">
                                            <div class="text-slate-900 font-semibold flex items-center gap-1.5">
                                                <i class="fa-regular fa-clock text-slate-400"></i>
                                                <?= date('H:i', strtotime($jadwal['jam_berangkat'])); ?> <span class="text-xs font-normal text-slate-400">WIB</span>
                                            </div>
                                            <div class="text-xs text-slate-400 font-normal mt-1">
                                                <?= date('d M Y', strtotime($jadwal['tanggal_berangkat'])); ?>
                                            </div>
                                        </td>

                                        <!-- Durasi Perjalanan -->
                                        <td class="p-5">
                                            <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                                <i class="fa-solid fa-circle-notch text-[10px] text-slate-400"></i>
                                                <?= htmlspecialchars($jadwal['estimasi_waktu'] ?? '-') ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Nama Supir -->
                                        <td class="p-5 text-slate-600">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[10px] border border-slate-200">
                                                    <i class="fa-solid fa-user-tie"></i>
                                                </div>
                                                <span class="text-xs font-semibold"><?= htmlspecialchars($jadwal['nama_supir'] ?? 'Belum Ditunjuk'); ?></span>
                                            </div>
                                        </td>
                                        
                                        <!-- Status Kursi -->
                                        <td class="p-5">
                                            <?php if ($jadwal['sisa_kursi'] <= 0): ?>
                                                <span class="text-rose-600 bg-rose-50 border border-rose-100 text-xs px-2.5 py-1 rounded-md font-bold">Penuh</span>
                                            <?php else: ?>
                                                <span class="text-emerald-700 bg-emerald-50/60 border border-emerald-100 text-xs px-2.5 py-1 rounded-md font-bold">
                                                    <?= $jadwal['sisa_kursi']; ?> Kursi Sisa
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Tarif Tiket -->
                                        <td class="p-5 text-right font-bold text-slate-900 text-base">
                                            Rp <?= number_format($jadwal['harga_dasar'], 0, ',', '.') ?>
                                        </td>

                                        <!-- Aksi Pesan -->
                                        <td class="p-5 pr-6 text-center">
                                            <?php if ($jadwal['sisa_kursi'] > 0): ?>
                                                <a href="pesan.php?id_jadwal=<?= $jadwal['id']; ?>" class="inline-block bg-[#111827] hover:bg-emerald-600 text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition shadow-sm whitespace-nowrap">
                                                    Pesan Tiket
                                                </a>
                                            <?php else: ?>
                                                <button disabled class="bg-slate-100 text-slate-400 text-xs font-semibold px-4 py-2.5 rounded-xl cursor-not-allowed whitespace-nowrap">
                                                    Terjual Habis
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-16 text-center text-sm text-slate-400 font-normal">
                                        <i class="fa-solid fa-square-poll-horizontal text-4xl text-slate-200 mb-3 block"></i>
                                        Tidak ada jadwal keberangkatan aktif yang cocok dengan kriteria pencarian.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>