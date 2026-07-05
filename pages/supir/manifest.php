<?php
// pages/supir/manifest.php
session_start();
require_once '../../config/database.php';

// Pastikan supir sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_supir = $_SESSION['id_user'];
// Mengambil nama supir secara dinamis dari session login
$nama_supir = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Supir');

try {
    // 1. Ambil jadwal aktif yang sedang ditugaskan ke supir ini
    $stmtJadwal = $pdo->prepare("
        SELECT jk.id AS id_jadwal, jk.tanggal_berangkat, jk.jam_berangkat, r.nama_rute, m.merk, m.plat_nomor
        FROM jadwal_keberangkatan jk
        INNER JOIN rute r ON jk.rute_id = r.id
        INNER JOIN mobil m ON jk.mobil_id = m.id
        WHERE jk.supir_id = ? AND jk.tanggal_berangkat >= CURDATE()
        ORDER BY jk.tanggal_berangkat ASC, jk.jam_berangkat ASC LIMIT 1
    ");
    $stmtJadwal->execute([$id_supir]);
    $jadwal = $stmtJadwal->fetch(PDO::FETCH_ASSOC);

    $penumpang = [];
    if ($jadwal) {
        // 2. Ambil data penumpang yang memesan jadwal tersebut
        $stmtPenumpang = $pdo->prepare("
            SELECT res.id AS id_reservasi, res.nama_penumpang, res.jumlah_tiket, res.total_bayar, res.status_pembayaran, res.titik_jemput
            FROM reservasi res
            WHERE res.jadwal_id = ?
            ORDER BY res.id DESC
        ");
        $stmtPenumpang->execute([$jadwal['id_jadwal']]);
        $penumpang = $stmtPenumpang->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifest Penumpang | DITRAS Driver</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: "Plus Jakarta Sans", sans-serif; background-color: #f8f9fa; } </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 min-h-screen flex">
    
    <aside class="w-64 bg-[#1a1a1a] text-gray-300 flex flex-col justify-between p-5 shrink-0">
        <div>
            <div class="mb-10 px-2">
                <div class="flex items-center gap-2 text-white font-bold text-xl tracking-wider">
                    <span class="text-[#00a86b]"><i class="fa-solid fa-route"></i></span> DITRAS
                </div>
                <div class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mt-0.5">Premium Travel</div>
            </div>

            <div class="text-[11px] text-gray-600 font-bold uppercase tracking-wider mb-3 px-2">Main Navigation</div>
            <nav class="space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-table-columns text-sm"></i> Dashboard Perjalanan
                </a>
                <a href="manifest.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#262626] text-white font-medium transition-all">
                    <i class="fa-solid fa-clipboard-list text-sm text-[#00a86b]"></i> Manifest Penumpang
                </a>
                <a href="konfirmasi-cod.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#222] hover:text-white transition-all text-gray-400">
                    <i class="fa-solid fa-wallet text-sm"></i> Konfirmasi COD
                </a>
            </nav>
        </div>

        <div class="border-t border-neutral-800 pt-4">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#00a86b] flex items-center justify-center text-white font-bold text-sm">
                        <?= strtoupper(substr(trim($nama_supir), 0, 1)); ?>
                    </div>
                    <div class="text-xs">
                        <p class="text-white font-medium truncate w-32"><?= htmlspecialchars($nama_supir); ?></p>
                        <p class="text-gray-500 text-[10px]">Driver DITRAS</p>
                    </div>
                </div>
                <a href="../auth/logout.php" class="text-gray-500 hover:text-red-400 p-1 transition-colors" title="Keluar Sistem">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <main class="p-10 max-w-7xl w-full mx-auto flex-1">
            
            <div class="mb-10 border-b border-gray-200 pb-6">
                <h2 class="text-3xl font-bold text-gray-800">Manifest Penumpang</h2>
                <p class="text-sm text-gray-500 mt-1">Sistem Manifes Perjalanan Driver: <span class="font-bold text-[#00a86b]"><?= htmlspecialchars($nama_supir); ?></span></p>
            </div>

            <?php if (!$jadwal): ?>
                <div class="bg-amber-50 border border-amber-200 p-4 text-amber-700 rounded-xl text-sm font-medium shadow-xs">
                    <i class="fa-solid fa-circle-info mr-1.5"></i> Anda saat ini belum memiliki jadwal perjalanan aktif atau penugasan rute resmi dari admin.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white p-6 border border-gray-100 shadow-xs mb-8 rounded-xl">
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Rute Lintasan</p>
                        <p class="text-base font-bold text-emerald-700"><?= htmlspecialchars($jadwal['nama_rute']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Unit Armada</p>
                        <p class="text-base font-bold text-gray-800"><?= htmlspecialchars($jadwal['merk']) ?> <span class="text-xs font-mono font-bold bg-gray-100 px-1.5 py-0.5 rounded text-gray-600"><?= htmlspecialchars($jadwal['plat_nomor']) ?></span></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Jadwal Keberangkatan</p>
                        <p class="text-base font-bold text-gray-800"><?= date('d M Y', strtotime($jadwal['tanggal_berangkat'])) ?> — <?= htmlspecialchars($jadwal['jam_berangkat']) ?> WIB</p>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 shadow-xs rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-bold">
                                <th class="p-4">Nama Penumpang</th>
                                <th class="p-4 text-center">Jumlah Tiket</th>
                                <th class="p-4">Titik Penjemputan</th>
                                <th class="p-4 text-right">Total Tagihan</th>
                                <th class="p-4 text-center">Status Manifest / Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($penumpang)): ?>
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-sm text-gray-400 font-medium">
                                        <i class="fa-solid fa-folder-open block text-2xl text-gray-300 mb-2"></i>
                                        Belum ada data reservasi kursi atau manifes yang masuk pada jadwal jalan ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($penumpang as $p): ?>
                                    <tr class="border-b border-gray-50 text-sm hover:bg-gray-50 transition-colors">
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($p['nama_penumpang']) ?></td>
                                        <td class="p-4 text-center font-semibold text-neutral-600"><?= $p['jumlah_tiket'] ?> Kursi</td>
                                        <td class="p-4 text-gray-500 text-xs max-w-xs truncate" title="<?= htmlspecialchars($p['titik_jemput']) ?>"><?= htmlspecialchars($p['titik_jemput']) ?></td>
                                        <td class="p-4 text-right font-bold text-gray-900">Rp <?= number_format($p['total_bayar'], 0, ',', '.') ?></td>
                                        <td class="p-4 text-center">
                                            <?php if ($p['status_pembayaran'] === 'Belum Bayar'): ?>
                                                <a href="konfirmasi-cod.php?id=<?= $p['id_reservasi'] ?>" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all">
                                                    Konfirmasi Pembayaran
                                                </a>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">
                                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Lunas (COD Sukses)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>