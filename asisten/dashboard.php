<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Ambil id asisten dari session
$id_asisten = $_SESSION['user_id'];

// 1. Total Modul Diajarkan (modul yang diupload asisten ini)
$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM modul");
$total_modul = mysqli_fetch_assoc($q1)['total'] ?? 0;

// 2. Total Laporan Masuk (semua laporan)
$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan");
$total_laporan = mysqli_fetch_assoc($q2)['total'] ?? 0;

// 3. Laporan Belum Dinilai (status dikirim)
$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan WHERE status='dikirim'");
$belum_dinilai = mysqli_fetch_assoc($q3)['total'] ?? 0;

// 4. Aktivitas Laporan Terbaru (ambil 5 terbaru)
$q4 = mysqli_query($conn, "
    SELECT l.*, u.nama as mahasiswa_nama, m.judul as modul_judul, p.nama as praktikum_nama
    FROM laporan l
    JOIN users u ON l.id_mahasiswa = u.id
    JOIN modul m ON l.id_modul = m.id
    JOIN praktikum p ON m.id_praktikum = p.id
    ORDER BY l.created_at DESC
    LIMIT 5
");

require_once 'templates/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_modul; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php while($log = mysqli_fetch_assoc($q4)): ?>
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                <span class="font-bold text-gray-500">
                    <?php echo strtoupper(substr($log['mahasiswa_nama'],0,1)); ?>
                </span>
            </div>
            <div>
                <p class="text-gray-800">
                    <strong><?php echo htmlspecialchars($log['mahasiswa_nama']); ?></strong>
                    telah mengumpulkan praktikum
                    <strong><?php echo htmlspecialchars($log['praktikum_nama']); ?></strong>
                    modul
                    <strong><?php echo htmlspecialchars($log['modul_judul']); ?></strong>
                </p>
                <p class="text-sm text-gray-500"><?php echo date('d M Y H:i', strtotime($log['created_at'])); ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>


<?php
// 3. Panggil Footer
require_once 'templates/footer.php';
?>