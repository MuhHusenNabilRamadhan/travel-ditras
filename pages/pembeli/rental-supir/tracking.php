<?php
// pages/pembeli/tracking.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Armada | DITRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style> h1, h2, h3, .serif { font-family: "Cormorant Garamond", serif; } body { font-family: "Montserrat", sans-serif; } </style>
</head>
<body class="bg-[#faf9f6] text-gray-900 flex flex-col min-h-screen">

    <?php include '../../components/sidebar.php'; ?>
    <?php include '../../components/header.php'; ?>

    <main class="ml-64 p-8 flex-1">
        <div class="mb-8">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-600 font-bold block mb-1">Live Monitoring</span>
            <h2 class="text-3xl italic text-gray-800">Lacak Armada Anda</h2>
            <p class="text-xs text-gray-400 mt-2">Pantau posisi kendaraan yang sedang Anda sewa melalui satelit.</p>
        </div>

        <div class="bg-stone-900 border border-stone-800 shadow-sm h-[500px] flex items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"></div>
            
            <div class="text-center z-10 p-6">
                <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-white text-lg font-bold font-serif italic">Menghubungkan ke GPS Armada...</h3>
                <p class="text-stone-400 text-[10px] uppercase tracking-widest mt-2">Status: Unit AA 1234 XY sedang aktif di jalur Wonosobo.</p>
            </div>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>