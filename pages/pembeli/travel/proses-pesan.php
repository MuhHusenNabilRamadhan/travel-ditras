<?php
// pages/pembeli/travel/proses-pesan.php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['id_user'];
    $id_jadwal = $_POST['id_jadwal'];
    $nama_penumpang = $_POST['nama_penumpang'];
    $jumlah_tiket = intval($_POST['jumlah_tiket']);
    $whatsapp_pembeli = $_POST['whatsapp_pembeli'];
    $titik_jemput = $_POST['titik_jemput'];

    try {
        $pdo->beginTransaction();

        // 1. Ambil sisa kursi, harga rute, dan data supir dari tabel users
        $stmtCheck = $pdo->prepare("
            SELECT jk.sisa_kursi, jk.supir_id, r.harga_dasar, r.nama_rute, jk.tanggal_berangkat, jk.jam_berangkat, u.nama AS nama_supir, u.nomor_hp AS hp_supir 
            FROM jadwal_keberangkatan jk
            INNER JOIN rute r ON jk.rute_id = r.id
            INNER JOIN users u ON jk.supir_id = u.id
            WHERE jk.id = ? FOR UPDATE
        ");
        $stmtCheck->execute([$id_jadwal]);
        $jadwal = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$jadwal) {
            throw new Exception("Jadwal perjalanan tidak ditemukan atau tidak valid.");
        }

        if ($jadwal['sisa_kursi'] < $jumlah_tiket) {
            throw new Exception("Maaf, sisa kapasitas kursi sudah penuh atau tidak mencukupi.");
        }

        // Hitung total nominal tarif bayar COD
        $total_bayar = $jumlah_tiket * $jadwal['harga_dasar'];

        // 2. Simpan data booking ke dalam tabel reservasi
        $stmtInsert = $pdo->prepare("
            INSERT INTO reservasi (id_user, jadwal_id, nama_penumpang, jumlah_tiket, whatsapp_pembeli, titik_jemput, total_bayar, status_pembayaran)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Belum Bayar')
        ");
        $stmtInsert->execute([$id_user, $id_jadwal, $nama_penumpang, $jumlah_tiket, $whatsapp_pembeli, $titik_jemput, $total_bayar]);

        // 3. Kurangi stok sisa_kursi di tabel jadwal_keberangkatan
        $stmtUpdateKursi = $pdo->prepare("UPDATE jadwal_keberangkatan SET sisa_kursi = sisa_kursi - ? WHERE id = ?");
        $stmtUpdateKursi->execute([$jumlah_tiket, $id_jadwal]);

        // Berhasil, commit transaksi database
        $pdo->commit();

        // Siapkan format text untuk dikirim via api WhatsApp target nomor hp supir
        $pesan_wa = "Halo Driver DITRAS, saya penumpang baru resmi.\n"
                  . "Nama: " . $nama_penumpang . "\n"
                  . "Rute: " . $jadwal['nama_rute'] . "\n"
                  . "Jadwal: " . date('d M Y', strtotime($jadwal['tanggal_berangkat'])) . " - " . $jadwal['jam_berangkat'] . "\n"
                  . "Jumlah Tiket: " . $jumlah_tiket . " Kursi\n"
                  . "Titik Jemput: " . $titik_jemput;
        
        $link_wa = "https://api.whatsapp.com/send?phone=" . preg_replace('/[^0-9]/', '', $jadwal['hp_supir']) . "&text=" . urlencode($pesan_wa);

        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Pemesanan Berhasil | DITRAS Travel</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body { font-family: "Montserrat", sans-serif; }
            </style>
        </head>
        <body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 max-w-md w-full text-center">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border border-emerald-100">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 tracking-wide">Pemesanan Berhasil!</h2>
                <p class="text-gray-400 text-xs mt-1 mb-6 font-medium">Tiket Anda telah terbit dan masuk ke sistem manifest jalan supir.</p>
                
                <div class="bg-neutral-50 border border-neutral-100 p-4 rounded-xl text-left space-y-2.5 mb-6">
                    <div class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">Informasi Penugasan Supir</div>
                    <div class="flex justify-between text-xs font-medium">
                        <span class="text-gray-500">Nama Driver:</span>
                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($jadwal['nama_supir']); ?></span>
                    </div>
                    <div class="flex justify-between text-xs font-medium">
                        <span class="text-gray-500">No. WhatsApp:</span>
                        <span class="font-bold text-emerald-600"><?= htmlspecialchars($jadwal['hp_supir']); ?></span>
                    </div>
                    <div class="flex justify-between text-xs font-medium">
                        <span class="text-gray-500">Total Tagihan (COD):</span>
                        <span class="font-bold text-gray-900">Rp <?= number_format($total_bayar, 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="space-y-2 text-xs font-semibold">
                    <a href="<?= $link_wa; ?>" target="_blank" class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl transition-all shadow-sm tracking-wide">
                        <i class="fa-brands fa-whatsapp text-sm"></i> Kirim Detail ke WhatsApp Driver
                    </a>
                    <a href="search.php" class="block w-full bg-black hover:bg-neutral-900 text-white py-3 rounded-xl transition-all tracking-wide">
                        Kembali ke Cari Jadwal
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Gagal Memesan: " . addslashes($e->getMessage()) . "'); window.location.href='search.php';</script>";
        exit;
    }
} else {
    header("Location: search.php");
    exit;
}