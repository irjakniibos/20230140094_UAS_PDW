<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// --- Statistik Otomatis ---
$userId = $_SESSION['user_id'];

// Praktikum diikuti
$praktikumDiikuti = 0;
$tugasSelesai = 0;
$praktikumIds = [];
$praktikumList = '0';

// Query praktikum diikuti
$q = mysqli_query($conn, "SELECT id_praktikum FROM praktikum_peserta WHERE id_mahasiswa=$userId");
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) $praktikumIds[] = $row['id_praktikum'];
    $praktikumDiikuti = count($praktikumIds);
}
$praktikumList = !empty($praktikumIds) ? implode(',', $praktikumIds) : '0';

// Tugas selesai
$q = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan WHERE id_mahasiswa=$userId AND status='dinilai'");
if ($q) {
    $tugasSelesai = mysqli_fetch_assoc($q)['total'] ?? 0;
}

// Total modul dari praktikum yang diikuti
$totalModul = 0;
if ($praktikumList !== '0') {
    $sql = "SELECT COUNT(*) as total FROM modul WHERE id_praktikum IN ($praktikumList)";
    $result = mysqli_query($conn, $sql);
    $totalModul = $result ? (mysqli_fetch_assoc($result)['total'] ?? 0) : 0;
}
$tugasMenunggu = $totalModul - $tugasSelesai;
if ($tugasMenunggu < 0) $tugasMenunggu = 0;

// Notifikasi terbaru
$notifModul = [];
if ($praktikumList !== '0') {
    $sql = "SELECT m.judul, p.nama, m.created_at FROM modul m JOIN praktikum p ON m.id_praktikum=p.id WHERE m.id_praktikum IN ($praktikumList) ORDER BY m.created_at DESC LIMIT 3";
    $q = mysqli_query($conn, $sql);
    if ($q) {
        while ($row = mysqli_fetch_assoc($q)) {
            $notifModul[] = [
                'icon' => '🆕',
                'text' => "Modul baru <b>{$row['judul']}</b> telah diupload pada praktikum <b>{$row['nama']}</b>.",
                'waktu' => $row['created_at']
            ];
        }
    }
}
$notifDaftar = [];
$q = mysqli_query($conn, "SELECT p.nama, pp.created_at FROM praktikum_peserta pp JOIN praktikum p ON pp.id_praktikum=p.id WHERE pp.id_mahasiswa=$userId ORDER BY pp.created_at DESC LIMIT 2");
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $notifDaftar[] = [
            'icon' => '✅',
            'text' => "Berhasil mendaftar pada mata praktikum <b>{$row['nama']}</b>.",
            'waktu' => $row['created_at']
        ];
    }
}
$notifikasi = array_merge($notifModul, $notifDaftar);
usort($notifikasi, function($a, $b) { return strtotime($b['waktu']) - strtotime($a['waktu']); });
$notifikasi = array_slice($notifikasi, 0, 5);

require_once 'templates/header_mahasiswa.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Hero Section -->
    <div class="bg-white rounded-xl shadow p-8 flex flex-col md:flex-row items-center mb-8">
        <div class="flex-1">
            <h1 class="text-3xl md:text-4xl font-extrabold text-blue-900 mb-2">Selamat Datang Kembali, <span class="text-red-600"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>!</h1>
            <p class="text-gray-700 mb-4">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
        </div>
        <div class="flex-shrink-0 mt-4 md:mt-0 md:ml-8">
            <img src="https://industri.unjaya.ac.id/wp-content/uploads/2024/01/image-1.png" alt="Lab" class="rounded-lg shadow-md w-40 h-28 object-cover">
        </div>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow flex flex-col items-center justify-center border border-blue-100">
            <div class="text-5xl font-extrabold text-blue-700"><?php echo $praktikumDiikuti; ?></div>
            <div class="mt-2 text-lg text-gray-600">Praktikum Diikuti</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow flex flex-col items-center justify-center border border-green-100">
            <div class="text-5xl font-extrabold text-green-600"><?php echo $tugasSelesai; ?></div>
            <div class="mt-2 text-lg text-gray-600">Tugas Selesai</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow flex flex-col items-center justify-center border border-yellow-100">
            <div class="text-5xl font-extrabold text-yellow-500"><?php echo $tugasMenunggu; ?></div>
            <div class="mt-2 text-lg text-gray-600">Tugas Menunggu</div>
        </div>
    </div>

    <!-- Notifikasi -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-2xl font-bold text-blue-900 mb-4">Notifikasi Terbaru</h3>
        <ul class="space-y-4">
            <?php if (count($notifikasi) > 0): ?>
                <?php foreach ($notifikasi as $notif): ?>
                    <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                        <span class="text-xl mr-4"><?php echo $notif['icon']; ?></span>
                        <div>
                            <span><?php echo $notif['text']; ?></span>
                            <div class="text-xs text-gray-400"><?php echo date('d M Y H:i', strtotime($notif['waktu'])); ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="text-gray-500">Belum ada notifikasi.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>