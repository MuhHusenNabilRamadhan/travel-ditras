<?php
// invoice/extend.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$id_extend = $_GET['id'] ?? 'EXT-' . rand(1000, 9999);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bukti Extend Waktu | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style>
        h1, h2, .serif { font-family: "Cormorant Garamond", serif; }
        body { font-family: "Montserrat", sans-serif; background-color: #525252; }
        @media print {
            body { background-color: white; }
            .print-hidden { display: none !important; }
            .print-shadow-none { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="p-8 flex justify-center">

    <div class="bg-white w-full max-w-3xl p-10 shadow-2xl print-shadow-none relative">
        
        <div class="absolute top-0 left-0 w-full h-2 bg-red-700"></div>

        <div class="flex justify-between items-start border-b border-gray-200 pb-6 mb-6">
            <div>
                <h1 class="text-4xl italic font-bold text-gray-900 tracking-tight">DITRAS</h1>
                <p class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold mt-1">Premium Travel & Car Rental</p>
                <p class="text-xs text-gray-500 mt-2">Jl. Dieng KM 15, Wonosobo<br>CS: +62 812-3456-7890</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-red-100 uppercase tracking-widest">INVOICE EXTEND</h2>
                <p class="text-sm font-bold text-red-700 mt-1">#<?= htmlspecialchars($id_extend) ?></p>
                <p class="text-xs text-gray-500">Tanggal: <?= date('d M Y') ?></p>
            </div>
        </div>

        <div class="bg-red-50 p-4 border-l-4 border-red-700 mb-8">
            <p class="text-xs text-red-800 font-semibold uppercase tracking-wider">Dokumen Penagihan Tambahan Waktu (Overtime)</p>
            <p class="text-xs text-red-600 mt-1">Harap diselesaikan sebelum pengembalian unit kendaraan.</p>
        </div>

        <table class="w-full mb-8 text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-gray-800 text-[10px] uppercase tracking-widest text-gray-500">
                    <th class="py-3">Deskripsi Tagihan</th>
                    <th class="py-3 text-center">Durasi Tambahan</th>
                    <th class="py-3 text-right">Biaya / Jam</th>
                    <th class="py-3 text-right">Total Denda</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700">
                <tr class="border-b border-gray-100">
                    <td class="py-4">
                        <span class="font-semibold text-gray-900 block">Toyota Innova Reborn (AA 1234 XY)</span>
                        <span class="text-xs text-gray-400">Alasan: Terjebak macet jalur Dieng</span>
                    </td>
                    <td class="py-4 text-center">3 Jam</td>
                    <td class="py-4 text-right">Rp 50.000</td>
                    <td class="py-4 text-right font-bold text-red-700">Rp 150.000</td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-end mb-12">
            <div class="w-1/2">
                <div class="flex justify-between py-3 border-b-2 border-gray-800">
                    <span class="text-sm font-bold text-gray-900 uppercase tracking-widest">Total Bayar</span>
                    <span class="text-lg font-bold text-red-700">Rp 150.000</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 text-center text-sm mb-8 mt-16">
            <div>
                <p class="text-xs text-gray-500 mb-16">Penyewa</p>
                <p class="font-bold border-b border-gray-300 inline-block w-48 pb-1"><?= $_SESSION['nama'] ?? '........................' ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-16">CS Garasi DITRAS</p>
                <p class="font-bold border-b border-gray-300 inline-block w-48 pb-1">Admin Bertugas</p>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-center gap-4 print-hidden">
            <button onclick="window.print()" class="px-6 py-3 bg-red-700 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-red-800 transition">
                Cetak Struk Denda
            </button>
            <button onclick="history.back()" class="px-6 py-3 border border-gray-300 text-gray-600 text-[10px] font-bold uppercase tracking-widest hover:bg-gray-50 transition">
                Kembali
            </button>
        </div>

    </div>

</body>
</html>