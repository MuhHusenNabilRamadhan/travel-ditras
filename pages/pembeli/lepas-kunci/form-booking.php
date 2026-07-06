<?php
// pages/pembeli/lepas-kunci/form-booking.php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}

try {
    // Mengambil data mobil yang berstatus 'tersedia' dari database db_ditras
    $query = "SELECT id, merk, tahun_kendaraan, harga_sewa_per_hari FROM mobil WHERE status_mobil = 'tersedia' ORDER BY merk ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $daftar_mobil = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal memuat data armada: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Lepas Kunci | DITRAS Premium Travel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex flex-col min-h-screen antialiased">

    <?php 
    include '../../../components/sidebar.php'; 
    include '../../../components/header.php'; 
    ?>

    <main class="ml-64 p-8 flex-1">
        <!-- HEADER FORM -->
        <div class="mb-8">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block mb-1">Layanan Rental</span>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Formulir Rental Lepas Kunci</h2>
            <p class="text-slate-500 text-sm mt-1">Silakan tentukan armada pilihan Anda dan jadwalkan tanggal petualangan mandiri Anda.</p>
        </div>

        <div class="max-w-xl bg-white rounded-2xl p-6 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] border border-slate-100">
            <form action="" method="POST" class="space-y-5">
                
                <!-- PILIH KENDARAAN (DARI DATABASE) -->
                <div>
                    <label class="text-xs font-semibold text-slate-500 block mb-2 uppercase tracking-wider">Pilih Kendaraan Aktif</label>
                    <div class="relative">
                        <i class="fa-solid fa-car absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                        <select name="mobil_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-10 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all appearance-none cursor-pointer text-slate-700 font-medium">
                            <?php if (empty($daftar_mobil)): ?>
                                <option value="" disabled selected>Maaf, semua unit saat ini sedang jalan / maintenance</option>
                            <?php else: ?>
                                <option value="" disabled selected>-- Pilih Armada Tersedia --</option>
                                <?php foreach ($daftar_mobil as $mobil): ?>
                                    <option value="<?= $mobil['id'] ?>">
                                        <?= htmlspecialchars($mobil['merk']) ?> (<?= $mobil['tahun_kendaraan'] ?>) - Rp <?= number_format($mobil['harga_sewa_per_hari'], 0, ',', '.') ?>/hari
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                
                <!-- INPUT TANGGAL AMBIL & DURASI -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-2 uppercase tracking-wider">Tanggal Ambil</label>
                        <div class="relative">
                            <i class="fa-solid fa-calendar-days absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <input type="date" name="tanggal_ambil" required min="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-10 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all text-slate-700">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-2 uppercase tracking-wider">Durasi Sewa</label>
                        <div class="relative">
                            <i class="fa-solid fa-hourglass-half absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <input type="number" name="durasi" min="1" placeholder="Berapa hari..." required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-10 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all text-slate-700">
                        </div>
                    </div>
                </div>

                <!-- ATURAN/CATATAN KECIL AGAR SANTAI -->
                <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-xl flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-info text-emerald-600 text-xs mt-0.5"></i>
                    <p class="text-[11px] text-emerald-800 leading-relaxed font-medium">
                        Pastikan membawa KTP asli serta SIM A aktif pada saat pengambilan unit fisik kendaraan di pool DITRAS Premium Travel.
                    </p>
                </div>

                <!-- ACTION BUTTON -->
                <div class="pt-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-emerald-600 text-white font-semibold text-sm py-3.5 px-6 rounded-xl transition duration-200 flex items-center justify-center gap-2 shadow-md">
                        <i class="fa-solid fa-circle-check text-xs"></i> Konfirmasi Transaksi Booking
                    </button>
                </div>
            </form>
        </div>
    </main>

    <?php include '../../../components/footer.php'; ?>
</body>
</html>