<?php
// pages/supir/dashboard.php
session_start();
require_once '../../config/database.php';

// Memastikan hanya role supir yang bisa akses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'supir') {
    header("Location: ../auth/login.php");
    exit;
}

// Mengambil nama supir secara dinamis dari session
$nama_supir = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Supir');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supir | DITRAS Travel</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js untuk Interaksi Dinamis -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #fdfbf7 0%, #f5f7fa 100%);
        }
        /* Efek kelap-kelip halus untuk status aktif */
        .pulse-soft {
            animation: softPulse 2s infinite;
        }
        @keyframes softPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.95); }
        }
        /* Kustomisasi scrollbar halus untuk dropdown */
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-x-hidden" x-data="{ sidebarOpen: true }">

    <!-- SIDEBAR -->
    <aside 
        class="bg-[#111111] text-gray-400 flex flex-col justify-between p-6 shrink-0 transition-all duration-300 relative z-20 border-r border-neutral-900"
        :class="sidebarOpen ? 'w-64' : 'w-20'">
        
        <div>
            <!-- Header Brand -->
            <div class="mb-10 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#00a86b] to-emerald-400 flex items-center justify-center text-white font-bold shrink-0 shadow-lg shadow-emerald-900/30">
                        <i class="fa-solid fa-route text-sm"></i>
                    </div>
                    <div class="transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 pointer-events-none'">
                        <span class="text-white font-bold tracking-wider text-lg">DITRAS</span>
                        <div class="text-[9px] text-emerald-500 uppercase tracking-widest font-bold -mt-1">Premium</div>
                    </div>
                </div>
                
                <!-- Toggle Button (Desktop) -->
                <button @click="sidebarOpen = !sidebarOpen" class="hidden md:flex text-gray-500 hover:text-white p-1.5 rounded-lg hover:bg-neutral-800 transition-colors">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-angle-left' : 'fa-angle-right'"></i>
                </button>
            </div>

            <!-- Navigasi -->
            <div class="text-[10px] text-neutral-600 font-bold uppercase tracking-widest mb-4 px-2 overflow-hidden whitespace-nowrap" :class="!sidebarOpen && 'opacity-0'">
                Main Navigation
            </div>
            
            <nav class="space-y-1.5">
                <a href="dashboard.php" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl bg-gradient-to-r from-emerald-950/50 to-transparent text-white border-l-4 border-[#00a86b] font-medium transition-all group">
                    <i class="fa-solid fa-table-columns text-base text-[#00a86b] group-hover:scale-110 transition-transform"></i> 
                    <span class="text-sm transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 block pointer-events-none'">Dashboard</span>
                </a>
                
                <a href="manifest.php" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl hover:bg-neutral-900 hover:text-gray-200 transition-all text-gray-500 group">
                    <i class="fa-solid fa-clipboard-list text-base group-hover:text-gray-300 transition-colors"></i> 
                    <span class="text-sm transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 block pointer-events-none'">Manifest Penumpang</span>
                </a>
                
                <a href="konfirmasi-cod.php" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl hover:bg-neutral-900 hover:text-gray-200 transition-all text-gray-500 group">
                    <i class="fa-solid fa-wallet text-base group-hover:text-gray-300 transition-colors"></i> 
                    <span class="text-sm transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 block pointer-events-none'">Konfirmasi COD</span>
                </a>
            </nav>
        </div>

        <!-- Profil Bagian Bawah -->
        <div class="border-t border-neutral-900 pt-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-neutral-800 to-neutral-700 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-inner">
                        <?= strtoupper(substr(trim($nama_supir), 0, 1)); ?>
                    </div>
                    <div class="text-xs transition-opacity duration-300 whitespace-nowrap" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 pointer-events-none'">
                        <p class="text-gray-200 font-semibold truncate w-28"><?= htmlspecialchars($nama_supir); ?></p>
                        <p class="text-neutral-600 text-[10px] font-medium">Driver On Duty</p>
                    </div>
                </div>
                <a href="../auth/logout.php" class="text-neutral-600 hover:text-red-400 p-2 rounded-lg hover:bg-neutral-900/50 transition-all shrink-0" title="Keluar Aplikasi">
                    <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-6 md:p-10 overflow-y-auto max-w-7xl mx-auto w-full">
        
        <!-- TOPBAR UTK LIVE CLOCK & NOTIFIKASI -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 pb-6 border-b border-slate-200/60 gap-4"
             x-data="{ 
                 time: '', 
                 date: '',
                 notifOpen: false,
                 notifications: [],
                 
                 /* Fungsi Inisialisasi: Mengambil memori penyimpanan dari browser agar tidak reset */
                 init() {
                     this.updateClock();
                     setInterval(() => this.updateClock(), 1000);
                     
                     const savedNotifs = localStorage.getItem('supir_notif_store');
                     if (savedNotifs) {
                         this.notifications = JSON.parse(savedNotifs);
                     } else {
                         // Default dummy data jika pertama kali dibuka
                         this.notifications = [
                             { id: 1, type: 'travel', title: 'Tiket Travel Reguler Dipesan', desc: 'User baru saja memesan rute terjadwal Anda.', time: 'Baru saja', read: false },
                             { id: 2, type: 'rental', title: 'Booking Rental + Supir', desc: 'Anda ditugaskan pada order sewa mobil baru.', time: '10 menit yang lalu', read: false }
                         ];
                         this.saveToStorage();
                     }
                 },
                 saveToStorage() {
                     localStorage.setItem('supir_notif_store', JSON.stringify(this.notifications));
                 },
                 get unreadCount() {
                     return this.notifications.filter(n => !n.read).length;
                 },
                 markAsRead(id) {
                     let notif = this.notifications.find(n => n.id === id);
                     if (notif) notif.read = true;
                     this.saveToStorage();
                 },
                 markAllAsRead() {
                     this.notifications.forEach(n => n.read = true);
                     this.saveToStorage();
                 },
                 updateClock() {
                     const now = new Date();
                     this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                     this.date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
                 }
             }">
            
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Halo, <?= htmlspecialchars($nama_supir); ?>!</h1>
                    <span class="inline-flex w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-soft" title="Sistem Aktif"></span>
                </div>
                <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">Selamat datang di <span class="text-[#00a86b] font-semibold">Sistem Kendali DITRAS Travel</span></p>
            </div>

            <!-- Bagian Kanan Topbar (Lonceng Notifikasi & Jam) -->
            <div class="flex items-center gap-4 shrink-0 self-start sm:self-auto relative">
                
                <!-- TOMBOL LONCENG NOTIFIKASI -->
                <div class="relative">
                    <button @click="notifOpen = !notifOpen" @click.away="notifOpen = false" class="w-11 h-11 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-600 hover:text-[#00a86b] hover:border-emerald-100 shadow-xs/50 transition-all relative group">
                        <i class="fa-regular fa-bell text-lg group-hover:rotate-12 transition-transform"></i>
                        
                        <!-- Badge Jumlah Notifikasi Aktif -->
                        <template x-if="unreadCount > 0">
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-xl flex items-center justify-center border-2 border-white shadow-xs" x-text="unreadCount"></span>
                        </template>
                    </button>

                    <!-- DROPDOWN LIST NOTIFIKASI (Diperlebar menjadi w-88 md:w-96 agar teks tidak menumpuk kaku) -->
                    <div x-show="notifOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl border border-slate-100 shadow-xl overflow-hidden z-30"
                         style="display: none;">
                        
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <span class="font-bold text-xs text-slate-700">Notifikasi Pesanan</span>
                            
                            <!-- Tombol Tandai Semua Telah Dibaca -->
                            <button x-show="unreadCount > 0" @click="markAllAsRead()" class="text-[10px] text-emerald-600 hover:text-emerald-700 font-bold bg-emerald-50 hover:bg-emerald-100/70 px-2.5 py-1 rounded-lg transition-colors">
                                <i class="fa-solid fa-check-double mr-1"></i> Baca Semua
                            </button>
                        </div>
                        
                        <!-- List Container -->
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 custom-scroll">
                            <template x-for="item in notifications" :key="item.id">
                                <div class="p-3.5 flex gap-3 transition-all relative group items-start"
                                     :class="item.read ? 'bg-white opacity-50' : 'bg-emerald-50/20'">
                                    
                                    <!-- Icon Rute / Layanan -->
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs shrink-0 mt-0.5"
                                         :class="item.type === 'travel' ? 'bg-emerald-50 text-[#00a86b]' : 'bg-blue-50 text-blue-600'">
                                        <i class="fa-solid" :class="item.type === 'travel' ? 'fa-bus' : 'fa-car-side'"></i>
                                    </div>
                                    
                                    <!-- Konten Teks (Ditambahkan pr-8 agar tidak menabrak tombol centang) -->
                                    <div class="flex-1 pr-8">
                                        <p class="text-xs font-bold text-slate-800 leading-tight" x-text="item.title"></p>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed" x-text="item.desc"></p>
                                        <span class="text-[9px] font-medium text-slate-400 block mt-1" x-text="item.time"></span>
                                    </div>

                                    <!-- Tombol Aksi Tanda Centang Satuan -->
                                    <template x-if="!item.read">
                                        <button @click="markAsRead(item.id)" 
                                                class="absolute right-3.5 top-4 w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 shadow-2xs flex items-center justify-center transition-all"
                                                title="Tandai telah dibaca">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                        </button>
                                    </template>
                                </div>
                            </template>

                            <!-- State jika kosong -->
                            <template x-if="notifications.length === 0">
                                <div class="p-6 text-center text-slate-400">
                                    <i class="fa-regular fa-bell-slash text-xl mb-2 block text-slate-300"></i>
                                    <p class="text-xs font-medium">Tidak ada notifikasi masuk</p>
                                </div>
                            </template>
                        </div>
                        
                        <a href="manifest.php" class="block py-2.5 text-center text-[11px] font-bold text-[#00a86b] bg-slate-50 hover:bg-slate-100 transition-colors border-t border-slate-100">
                            Lihat Semua Manifest
                        </a>
                    </div>
                </div>

                <!-- Jam Dinamis -->
                <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl border border-slate-100 shadow-xs/50">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                        <i class="fa-regular fa-clock text-sm"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-800 tracking-wide font-mono" x-text="time">--:--:--</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="date">Memuat...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID MENU UTAMA -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- CARD 1: MANIFEST -->
            <a href="manifest.php" class="group bg-white p-6 rounded-2xl border border-slate-100 shadow-xs hover:shadow-xl hover:shadow-emerald-900/5 hover:border-emerald-500/30 transition-all duration-300 hover:-translate-y-1.5 flex flex-col justify-between min-h-[180px]">
                <div>
                    <div class="p-3 bg-emerald-50 text-[#00a86b] rounded-xl w-12 h-12 flex items-center justify-center group-hover:bg-[#00a86b] group-hover:text-white transition-all duration-300 shadow-sm shadow-emerald-100">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mt-5 group-hover:text-[#00a86b] transition-colors">Lihat Manifest</h3>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed font-medium">Cek daftar lengkap manifes penumpang beserta titik jemput pemesan.</p>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#00a86b] uppercase tracking-wider mt-4 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                    Buka Manifest <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>

            <!-- CARD 2: UPDATE LOKASI (DARK CARD STYLE) -->
            <a href="update-lokasi.php" class="group bg-neutral-950 p-6 rounded-2xl shadow-md hover:shadow-xl hover:shadow-neutral-950/20 transition-all duration-300 hover:-translate-y-1.5 flex flex-col justify-between min-h-[180px] relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(#262626_1px,transparent_1px)] [background-size:16px_16px] opacity-20"></div>
                
                <div class="relative z-10">
                    <div class="p-3 bg-neutral-900 text-emerald-400 border border-neutral-800 rounded-xl w-12 h-12 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white group-hover:border-transparent transition-all duration-300">
                        <i class="fa-solid fa-location-crosshairs text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mt-5">Update Lokasi</h3>
                    <p class="text-xs text-neutral-400 mt-1.5 leading-relaxed font-medium">Laporkan posisi unit secara berkala (Rest Area, Tol, atau Kota Transit).</p>
                </div>
                <div class="relative z-10 flex items-center gap-1.5 text-[11px] font-bold text-emerald-400 uppercase tracking-wider mt-4 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                    Kirim Lokasi <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>

            <!-- CARD 3: COD -->
            <a href="konfirmasi-cod.php" class="group bg-white p-6 rounded-2xl border border-slate-100 shadow-xs hover:shadow-xl hover:shadow-emerald-900/5 hover:border-emerald-500/30 transition-all duration-300 hover:-translate-y-1.5 flex flex-col justify-between min-h-[180px]">
                <div>
                    <div class="p-3 bg-emerald-50 text-[#00a86b] rounded-xl w-12 h-12 flex items-center justify-center group-hover:bg-[#00a86b] group-hover:text-white transition-all duration-300 shadow-sm shadow-emerald-100">
                        <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mt-5 group-hover:text-[#00a86b] transition-colors">Konfirmasi COD</h3>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed font-medium">Validasi pelunasan tunai di tempat langsung dari tangan penumpang.</p>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#00a86b] uppercase tracking-wider mt-4 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                    Verifikasi Keuangan <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>
            
        </div>
    </main>
</body>
</html>