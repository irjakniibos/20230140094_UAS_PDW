<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

// Cek login mahasiswa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$mahasiswa_id = $_SESSION['user_id'];

// Ambil daftar praktikum yang diikuti mahasiswa
$sql = "SELECT p.* FROM praktikum p
        JOIN praktikum_peserta pp ON pp.id_praktikum = p.id
        WHERE pp.id_mahasiswa = $mahasiswa_id
        ORDER BY p.nama ASC";
$praktikumList = mysqli_query($conn, $sql);

if ($praktikumList === false) {
    die('Query error: ' . mysqli_error($conn));
}

$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-2xl font-bold mb-4">Praktikum Saya</h2>
    <?php if (isset($_SESSION['pesan'])): ?>
        <?php
            $warna = (isset($_SESSION['pesan_tipe']) && $_SESSION['pesan_tipe'] == 'success')
                ? 'bg-green-100 border-green-400 text-green-700'
                : 'bg-red-100 border-red-400 text-red-700';
        ?>
        <div id="alert-pesan" class="<?php echo $warna; ?> border px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['pesan']; unset($_SESSION['pesan'], $_SESSION['pesan_tipe']); ?>
        </div>
        <script>
            setTimeout(function() {
                var alertBox = document.getElementById('alert-pesan');
                if(alertBox) alertBox.style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">No</th>
                <th class="px-4 py-2 text-left">Nama Praktikum</th>
                <th class="px-4 py-2 text-left">Deskripsi</th>
                <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = mysqli_fetch_assoc($praktikumList)): ?>
            <tr class="border-b">
                <td class="px-4 py-2"><?php echo $no++; ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['nama']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                <td class="px-4 py-2">
                    <a href="detail_praktikum.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">Detail</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div class="mt-6">
        <a href="course.php" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Kembali ke Katalog Praktikum</a>
    </div>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>