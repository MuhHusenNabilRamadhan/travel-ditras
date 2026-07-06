<?php
// pages/pembeli/riwayat.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// PROSES UPLOAD BUKTI TRANSFER (Jika ada form yang disubmit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_bukti'])) {
    $transaksi_id = $_POST['transaksi_id'];
    
    if (isset($_FILES['bukti_tf']) && $_FILES['bukti_tf']['error'] == 0) {
        $target_dir = "../../assets/uploads/bukti_transfer/";
        
        // Buat folder jika belum ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['bukti_tf']['name'], PATHINFO_EXTENSION);
        $filename = "TF_" . $transaksi_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $filename;
        
        // Validasi ekstensi gambar
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (in_html_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['bukti_tf']['tmp_name'], $target_file)) {
                // Update nama file & ubah status menjadi menunggu ACC supir kembali / atau tetap sesuai alur
                $update_query = "UPDATE transaksi SET bukti_transfer = ?, status_pembayaran = 'menunggu_acc_supir' WHERE id = ? AND pembeli_id = ?";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute([$filename, $transaksi_id, $id_user]);
                $success_msg = "Bukti transfer berhasil diunggah! Menunggu konfirmasi.";
            } else {
                $error_msg = "Gagal mengunggah file.";
            }
        } else {
            $error_msg = "Format file harus JPG, JPEG, atau PNG.";
        }
    }
}

// AMBIL SEMUA DATA TRANSAKSI GABUNGAN
try {
    // Menggabungkan data induk transaksi dengan detail spesifik rute (jika jenis_layanan = travel)
    $query = "SELECT t.*, dt.nama_penumpang, dt.jumlah_tiket 
              FROM transaksi t
              LEFT JOIN detail_travel dt ON t.id = dt.transaksi_id
              WHERE t.pembeli_id = ? 
              ORDER BY t.tanggal_transaksi DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_user]);
    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data riwayat: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex flex-col min-h-screen antialiased">

    <?php 
    include '../../components/sidebar.php'; 
    include '../../components/header.php'; 
    ?>

    <main class="ml-64 p-8 flex-1">
        <!-- HEADER -->
        <div class="mb-6">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block mb-1">Pusat Informasi</span>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Transaksi Anda</h2>
            <p class="text-slate-500 text-sm mt-1">Pantau status pemesanan travel reguler, rental mobil, dan unduh invoice resmi Anda.</p>
        </div>

        <!-- NOTIFIKASI SEMENTARA -->
        <?php if(isset($success_msg)): ?>
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i> <?= $success_msg ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error_msg)): ?>
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <!-- FILTER TAB LAYANAN -->
        <div class="flex gap-2 mb-6 border-b border-slate-200 pb-px">
            <button onclick="filterLayanan('semua')" id="btn-semua" class="tab-btn px-4 py-2.5 text-xs font-bold rounded-t-xl bg-slate-900 text-white transition-all">Semua Layanan</button>
            <button onclick="filterLayanan('travel')" id="btn-travel" class="tab-btn px-4 py-2.5 text-xs font-semibold rounded-t-xl text-slate-500 hover:text-slate-800 transition-all">🚌 Tiket Travel</button>
            <button onclick="filterLayanan('rental_supir')" id="btn-rental_supir" class="tab-btn px-4 py-2.5 text-xs font-semibold rounded-t-xl text-slate-500 hover:text-slate-800 transition-all">👨‍✈️ Dengan Supir</button>
            <button onclick="filterLayanan('lepas_kunci')" id="btn-lepas_kunci" class="tab-btn px-4 py-2.5 text-xs font-semibold rounded-t-xl text-slate-500 hover:text-slate-800 transition-all">🔑 Lepas Kunci</button>
        </div>

        <!-- CONTAINER TABEL -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                            <th class="p-4">ID / Tanggal</th>
                            <th class="p-4">Jenis Layanan</th>
                            <th class="p-4">Detail Perjalanan / Unit</th>
                            <th class="p-4">Total Biaya</th>
                            <th class="p-4">Status & Pembayaran</th>
                            <th class="p-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if(empty($riwayat)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada riwayat transaksi yang tercatat.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($riwayat as $row): ?>
                                <tr class="item-riwayat hover:bg-slate-50/50 transition-colors" data-layanan="<?= $row['jenis_layanan'] ?>">
                                    <!-- ID & TANGGAL -->
                                    <td class="p-4">
                                        <span class="font-bold text-slate-900 block">#DTR-<?= $row['id'] ?></span>
                                        <span class="text-[11px] text-slate-400 block mt-0.5"><?= date('d M Y, H:i', strtotime($row['tanggal_transaksi'])) ?> WIB</span>
                                    </td>
                                    
                                    <!-- JENIS LAYANAN BADGE -->
                                    <td class="p-4">
                                        <?php if($row['jenis_layanan'] == 'travel'): ?>
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 uppercase">Travel Reguler</span>
                                        <?php elseif($row['jenis_layanan'] == 'rental_supir'): ?>
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100 uppercase">Sewa + Supir</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100 uppercase">Lepas Kunci</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- DETAIL ITEMS -->
                                    <td class="p-4 font-medium text-slate-700">
                                        <?php if($row['jenis_layanan'] == 'travel'): ?>
                                            <div class="flex flex-col">
                                                <span>Atas Nama: <strong class="text-slate-900"><?= htmlspecialchars($row['nama_penumpang'] ?? '-') ?></strong></span>
                                                <span class="text-[11px] text-slate-400 mt-0.5"><i class="fa-solid fa-chair text-[10px]"></i> <?= $row['jumlah_tiket'] ?? 1 ?> Kursi Terpesan</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">Lihat lembar invoice/form sewa</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- TOTAL BIAYA -->
                                    <td class="p-4 font-bold text-slate-900">
                                        Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                    </td>

                                    <!-- STATUS & METODE PEMBAYARAN -->
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1.5 items-start">
                                            <!-- Badge Status -->
                                            <?php 
                                            $status = $row['status_pembayaran'];
                                            if($status == 'pending') {
                                                echo '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Pending</span>';
                                            } elseif($status == 'menunggu_acc_supir') {
                                                echo '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-50 text-orange-700 border border-orange-100">Menunggu ACC Supir</span>';
                                            } elseif($status == 'diterima_supir') {
                                                echo '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">Di-ACC Supir (Cek TF)</span>';
                                            } elseif($status == 'lunas' || $status == 'selesai') {
                                                echo '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">Lunas / Selesai</span>';
                                            } else {
                                                echo '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700">Ditolak</span>';
                                            }
                                            ?>
                                            <!-- Badge Metode -->
                                            <span class="text-[10px] text-slate-400">
                                                Method: <strong class="uppercase text-slate-600"><?= $row['metode_pembayaran'] ?? 'Tunai' ?></strong>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- ACTION BUTTONS -->
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- JIKA LAYANAN TRAVEL & TRANSFER & BELUM ADA BUKTI TF -->
                                            <?php if($row['jenis_layanan'] == 'travel' && $row['metode_pembayaran'] == 'transfer' && empty($row['bukti_transfer'])): ?>
                                                <button onclick="bukaModalUpload(<?= $row['id'] ?>)" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] px-3 py-1.5 rounded-lg transition">
                                                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload TF
                                                </button>
                                            <?php endif; ?>

                                            <!-- ATURAN DIREKSI STRUK INVOICE SESUAI SYARAT -->
                                            <?php if($row['jenis_layanan'] == 'travel' || $row['jenis_layanan'] == 'rental_supir'): ?>
                                                <!-- Bisa langsung cetak struk kecil jika sudah di-acc supir/lunas -->
                                                <a href="invoice/cetak_termal.php?id=<?= $row['id'] ?>" target="_blank" class="bg-slate-900 hover:bg-emerald-600 text-white font-medium text-[11px] px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                                    <i class="fa-solid fa-print text-[10px]"></i> Struk
                                                </a>
                                            <?php else: ?>
                                                <!-- Lepas Kunci wajib Lunas & Selesai baru tombol cetak aktif -->
                                                <?php if($row['status_pembayaran'] == 'lunas' || $row['status_pembayaran'] == 'selesai'): ?>
                                                    <a href="invoice/cetak_nota.php?id=<?= $row['id'] ?>" target="_blank" class="bg-slate-900 hover:bg-emerald-600 text-white font-medium text-[11px] px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                                        <i class="fa-solid fa-file-invoice"></i> Nota Resmi
                                                    </a>
                                                <?php else: ?>
                                                    <button disabled class="bg-slate-100 text-slate-400 font-medium text-[11px] px-3 py-1.5 rounded-lg cursor-not-allowed" title="Menunggu Validasi Pengembalian & Pelunasan Admin">
                                                        <i class="fa-solid fa-lock text-[10px]"></i> Terkunci
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL POPUP UNTUK UPLOAD BUKTI TRANSFER -->
    <div id="modal-upload" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-900">Kirim Bukti Transfer Bank</h3>
                <button onclick="tutupModalUpload()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="transaksi_id" id="modal-transaksi-id">
                
                <div>
                    <label class="text-[11px] font-semibold text-slate-500 block mb-1.5 uppercase">Pilih File Gambar (JPG/PNG)</label>
                    <input type="file" name="bukti_tf" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 p-2 rounded-xl bg-slate-50">
                </div>

                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" onclick="tutupModalUpload()" class="px-4 py-2 text-xs font-semibold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" name="upload_bukti" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition">Kirim Bukti</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT FILTER TAB & MODAL INTERAKTIF -->
    <script>
        function filterLayanan(jenis) {
            // Atur gaya aktif tombol tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-slate-900', 'text-white');
                btn.classList.add('text-slate-500', 'font-semibold');
            });
            const activeBtn = document.getElementById('btn-' + jenis);
            activeBtn.classList.remove('text-slate-500', 'font-semibold');
            activeBtn.classList.add('bg-slate-900', 'text-white', 'font-bold');

            // Sembunyikan/tampilkan baris tabel sesuai filter jenis_layanan
            document.querySelectorAll('.item-riwayat').forEach(row => {
                if (jenis === 'semua' || row.getAttribute('data-layanan') === jenis) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        function bukaModalUpload(id) {
            document.getElementById('modal-transaksi-id').value = id;
            document.getElementById('modal-upload').classList.remove('hidden');
        }

        function tutupModalUpload() {
            document.getElementById('modal-upload').classList.add('hidden');
        }
    </script>

    <?php include '../../components/footer.php'; ?>
</body>
</html>