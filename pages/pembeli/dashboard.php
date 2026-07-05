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
    // PERBAIKAN QUERY: jk.harga diganti menjadi r.harga_dasar
    $query = "
        SELECT 
            jk.id AS id_jadwal,
            jk.tanggal_berangkat,
            jk.jam_berangkat,
            jk.sisa_kursi,
            r.harga_dasar,       -- Diambil dari tabel rute sesuai struktur database
            r.nama_rute,
            r.estimasi_waktu
        FROM jadwal_keberangkatan jk
        INNER JOIN rute r ON jk.rute_id = r.id
        WHERE 1=1
    ";

    $params = [];

    // Filter pencarian berdasarkan nama rute
    if (!empty($search_asal)) {
        $query .= " AND r.nama_rute LIKE ? ";
        $params[] = "%$search_asal%";
    }

    if (!empty($search_tujuan)) {
        $query .= " AND r.nama_rute LIKE ? ";
        $params[] = "%$search_tujuan%";
    }

    // Urutkan berdasarkan jadwal terdekat
    $query .= " ORDER BY jk.tanggal_berangkat ASC, jk.jam_berangkat ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $daftar_jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Gagal memuat jadwal perjalanan: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layanan | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } 
        body { font-family: "Montserrat", sans-serif; } 
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">
    
    <?php 
    include '../../components/sidebar.php'; 
    include '../../components/header.php'; 
    ?>
    
    <main class="ml-64 p-8 flex-1">
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Travel Service Hub</span>
            <h2 class="text-4xl italic text-gray-800">Layanan Tiket Travel</h2>
            <p class="text-gray-400 mt-2 text-sm">Temukan rute perjalanan terbaik dengan armada premium kami.</p>
        </div>

        <!-- FITUR PENCARIAN ANTAR KOTA -->
        <div class="bg-white p-6 border border-gray-100 shadow-sm rounded-xl mb-10 max-w-5xl">
            <h3 class="text-xl font-serif italic mb-4 text-gray-800"><i class="fa-solid fa-magnifying-glass text-emerald-600 mr-2"></i>Cari Rute Perjalanan</h3>
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="text-xs uppercase tracking-wider font-semibold text-gray-500 block mb-2">Kota Asal</label>
                    <input type="text" name="asal" value="<?= htmlspecialchars($search_asal) ?>" placeholder="Contoh: Wonosobo" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wider font-semibold text-gray-500 block mb-2">Kota Tujuan</label>
                    <input type="text" name="tujuan" value="<?= htmlspecialchars($search_tujuan) ?>" placeholder="Contoh: Semarang" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-bus"></i> Cari Jadwal
                    </button>
                </div>
            </form>
        </div>

        <!-- DAFTAR JADWAL AKTIF -->
        <div class="max-w-5xl">
            <h3 class="text-2xl font-serif italic mb-6 text-gray-800">Jadwal Keberangkatan Tersedia</h3>
            
            <?php if (empty($daftar_jadwal)): ?>
                <div class="bg-amber-50 border border-amber-200 p-6 rounded-xl text-center text-amber-800 text-sm">
                    <i class="fa-solid fa-circle-info text-xl mb-2 block"></i>
                    Belum ada jadwal operasional yang cocok atau aktif untuk rute tersebut saat ini.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($daftar_jadwal as $jadwal): ?>
                        <div class="bg-white border border-gray-100 shadow-sm rounded-xl p-6 hover:shadow-md transition duration-300 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    <span class="bg-emerald-50 text-emerald-700 font-bold text-[10px] px-2.5 py-1 rounded-full tracking-wider uppercase">
                                        Sisa Kursi: <?= htmlspecialchars($jadwal['sisa_kursi']) ?>
                                    </span>
                                    <!-- PERBAIKAN: Menggunakan harga_dasar -->
                                    <span class="text-emerald-700 font-bold text-lg">
                                        Rp <?= number_format($jadwal['harga_dasar'], 0, ',', '.') ?>
                                    </span>
                                </div>
                                
                                <h4 class="text-xl font-semibold text-gray-800 mb-2">
                                    <?= htmlspecialchars($jadwal['nama_rute']) ?>
                                </h4>
                                
                                <div class="text-xs text-gray-500 space-y-1.5 mb-6">
                                    <p class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar text-emerald-600 w-4"></i>
                                        <?= date('d M Y', strtotime($jadwal['tanggal_berangkat'])) ?>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <i class="fa-regular fa-clock text-emerald-600 w-4"></i>
                                        <?= date('H:i', strtotime($jadwal['jam_berangkat'])) ?> WIB
                                    </p>
                                    <p class="flex items-center gap-2 text-gray-400">
                                        <i class="fa-solid fa-hourglass-half text-amber-600 w-4"></i>
                                        Estimasi Perjalanan: <?= htmlspecialchars($jadwal['estimasi_waktu'] ?? '-') ?>
                                    </p>
                                </div>
                            </div>

                            <a href="travel/pesan.php?jadwal_id=<?= $jadwal['id_jadwal'] ?>" class="block text-center w-full bg-gray-900 hover:bg-emerald-700 text-white font-semibold text-xs py-2.5 rounded-lg transition duration-300">
                                Pesan Tiket Sekarang
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>