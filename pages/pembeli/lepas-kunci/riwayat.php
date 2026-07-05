<?php
// pages/pembeli/lepas-kunci/riwayat.php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Lepas Kunci | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style> h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } body { font-family: "Montserrat", sans-serif; } </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../../components/sidebar.php'; ?>
    <?php include '../../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Customer History</span>
            <h2 class="text-3xl italic text-gray-800">Riwayat Lepas Kunci</h2>
        </div>

        <div class="bg-white border border-gray-100 shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest text-gray-500">
                        <th class="py-4 px-6 font-semibold">ID Booking</th>
                        <th class="py-4 px-6 font-semibold">Mobil</th>
                        <th class="py-4 px-6 font-semibold text-center">Status</th>
                        <th class="py-4 px-6 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="py-4 px-6 font-bold text-gray-800">#TRX-7821</td>
                        <td class="py-4 px-6">Toyota Innova Reborn</td>
                        <td class="py-4 px-6 text-center">
                            <span class="bg-emerald-100 text-emerald-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Selesai</span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="../../../invoice/utama.php?id=TRX-7821" class="text-emerald-600 font-bold text-[10px] uppercase underline">Invoice</a>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="py-4 px-6 font-bold text-gray-800">#EXT-1102</td>
                        <td class="py-4 px-6">Toyota Innova Reborn (Overtime)</td>
                        <td class="py-4 px-6 text-center">
                            <span class="bg-red-100 text-red-700 py-1 px-3 rounded-full text-[9px] uppercase tracking-wider font-bold">Denda</span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="extend.php?id=EXT-1102" class="text-red-600 font-bold text-[10px] uppercase underline">Lihat Denda</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <?php include '../../../components/footer.php'; ?>
</body>
</html>