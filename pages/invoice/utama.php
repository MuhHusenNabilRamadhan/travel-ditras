<?php
// pages/invoice/utama.php
session_start();
// Mundur 2 tingkat karena folder invoice berada di dalam pages/
require_once '../../config/database.php'; 

$id_transaksi = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'travel'; 

if (!$id_transaksi) {
    die("ID Transaksi tidak valid / tidak dicantumkan.");
}

$data = [];

try {
    if ($type === 'travel') {
        // PERBAIKAN QUERY: Menyesuaikan dengan struktur tabel asli kamu
        $stmt = $pdo->prepare("
            SELECT 
                res.id AS id_reservasi,
                '-' AS nomor_kursi, 
                res.total_bayar,
                res.status_pembayaran,
                res.nama_penumpang,
                r.nama_rute
            FROM reservasi res
            INNER JOIN jadwal_keberangkatan jk ON res.jadwal_id = jk.id
            INNER JOIN rute r ON jk.rute_id = r.id
            WHERE res.id = ?
        ");
        $stmt->execute([$id_transaksi]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Fallback untuk pesanan Sewa Mobil / Lepas Kunci
        $data = [
            'id_reservasi' => $id_transaksi,
            'nama_penumpang' => 'Pelanggan DITRAS Fleet',
            'nama_rute' => 'Sewa Unit Mobil Premium (Lepas Kunci)',
            'nomor_kursi' => '-',
            'total_bayar' => 1000000,
            'status_pembayaran' => 'MENUNGGU COD'
        ];
    }
} catch (PDOException $e) {
    die("Gagal memuat sistem basis data invoice: " . $e->getMessage()); 
}

// Jika data ID dicari tidak ada di database
if (!$data) {
    die("Data transaksi #" . htmlspecialchars($id_transaksi) . " tidak ditemukan di sistem.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Invoice #<?= htmlspecialchars($id_transaksi) ?> | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        h1, h2, .serif { font-family: "Cormorant Garamond", serif; } 
        body { font-family: "Plus Jakarta Sans", sans-serif; } 
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 min-h-screen p-6 flex flex-col items-center justify-center">

    <div class="bg-white p-10 shadow-xl w-full max-w-xl border-t-4 border-emerald-600 rounded-xl">
        <div class="flex justify-between items-start mb-8">
            <div>
                <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Official Receipt</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-800">DITRAS INVOICE</h1>
                <p class="text-xs text-gray-400 mt-1">ID Transaksi: <span class="font-mono font-bold text-gray-700">#<?= htmlspecialchars($id_transaksi) ?></span></p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-bold px-3 py-1 rounded-full <?= ($data['status_pembayaran'] === 'Lunas') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                    <?= strtoupper($data['status_pembayaran'] ?? 'BELUM BAYAR') ?>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100 text-sm">
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Nama Pemesan</p>
                <p class="font-bold text-gray-800"><?= htmlspecialchars($data['nama_penumpang'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Nomor Kursi</p>
                <p class="font-mono font-bold text-gray-800"><?= htmlspecialchars($data['nomor_kursi'] ?? '-') ?></p>
            </div>
            <div class="col-span-2 mt-2 pt-2 border-t border-gray-200/60">
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Layanan / Deskripsi Rute</p>
                <p class="font-semibold text-gray-800">
                    <?= ($type === 'travel') ? '✨ Travel Rute: ' . htmlspecialchars($data['nama_rute'] ?? 'N/A') : '🚗 Sewa Mobil Lepas Kunci' ?>
                </p>
            </div>
        </div>

        <table class="w-full mb-6 text-sm">
            <thead>
                <tr class="text-left text-[10px] uppercase text-gray-400 border-b pb-2 font-bold tracking-wider">
                    <th class="py-2">Deskripsi</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-100">
                    <td class="py-4 font-semibold text-gray-700">
                        <?= ($type === 'travel') ? 'Tiket Penumpang Travel Resmi DITRAS' : 'Sewa Unit per Hari' ?>
                    </td>
                    <td class="py-4 text-right font-bold text-emerald-600">
                        Rp <?= number_format($data['total_bayar'] ?? 0, 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-between items-center font-bold text-xl bg-emerald-50/50 p-4 rounded-lg border border-emerald-100/50 text-emerald-950">
            <span class="text-xs tracking-wide uppercase text-emerald-800">GRAND TOTAL</span>
            <span>Rp <?= number_format($data['total_bayar'] ?? 0, 0, ',', '.') ?></span>
        </div>

        <div class="mt-8 text-center text-[11px] text-gray-400 italic">
            <p>Terima kasih telah mempercayai perjalanan Anda bersama DITRAS Premium Travel.</p>
        </div>

        <div class="mt-10 flex gap-3 print:hidden justify-end">
            <button onclick="history.back()" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-5 py-2.5 rounded-lg transition-all flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </button>
            <button onclick="window.print()" class="text-xs bg-neutral-900 hover:bg-emerald-600 text-white font-bold px-5 py-2.5 rounded-lg transition-all flex items-center gap-1">
                <i class="fa-solid fa-print"></i> Cetak Dokumen
            </button>
        </div>
    </div>

</body>
</html>