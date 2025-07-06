<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

// Cek login mahasiswa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$mahasiswa_id = $_SESSION['user_id'];

// Ambil ID praktikum dari URL
if (!isset($_GET['id'])) {
    header('Location: my_courses.php');
    exit();
}
$praktikum_id = intval($_GET['id']);

// Cek apakah mahasiswa terdaftar di praktikum ini
$cek = mysqli_query($conn, "SELECT * FROM praktikum_peserta WHERE id_praktikum=$praktikum_id AND id_mahasiswa=$mahasiswa_id");
if (mysqli_num_rows($cek) == 0) {
    header('Location: my_courses.php');
    exit();
}

// Ambil detail praktikum
$praktikum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM praktikum WHERE id=$praktikum_id"));

// Ambil daftar modul
$modulList = mysqli_query($conn, "SELECT * FROM modul WHERE id_praktikum=$praktikum_id ORDER BY id ASC");

// Proses upload laporan
if (isset($_POST['kumpul_laporan'])) {
    $id_modul = intval($_POST['id_modul']);
    $file = '';
    if (!empty($_FILES['file']['name'])) {
        $targetDir = "../uploads/laporan/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $file = uniqid() . '_' . basename($_FILES['file']['name']);
        $targetFile = $targetDir . $file;
        move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
        // Simpan laporan
        mysqli_query($conn, "INSERT INTO laporan (id_mahasiswa, id_modul, file, status) VALUES ($mahasiswa_id, $id_modul, '$file', 'dikirim')
            ON DUPLICATE KEY UPDATE file='$file', status='dikirim'");
        $_SESSION['pesan'] = "Laporan berhasil dikumpulkan!";
        $_SESSION['pesan_tipe'] = "success";
    } else {
        $_SESSION['pesan'] = "File laporan wajib diupload!";
        $_SESSION['pesan_tipe'] = "error";
    }
    header("Location: detail_praktikum.php?id=$praktikum_id");
    exit();
}

$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-2xl font-bold mb-2">Praktikum Saya</h2>
    <div class="mb-2">
        <span class="text-xl font-bold"><?php echo htmlspecialchars($praktikum['nama']); ?></span><br>
        <span class="text-gray-700"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></span>
    </div>
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

    <h4 class="font-semibold mb-2 mt-4">Daftar Modul / Materi</h4>
    <div class="overflow-x-auto">
    <table class="min-w-full w-full table-auto mb-6 text-center">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-center">Modul</th>
                <th class="px-4 py-2 text-center">Materi</th>
                <th class="px-4 py-2 text-center">Laporan</th>
                <th class="px-4 py-2 text-center">Nilai</th>
                <th class="px-4 py-2 text-center">Feedback</th>
            </tr>
        </thead>
        <tbody>
            <?php while($modul = mysqli_fetch_assoc($modulList)): ?>
            <?php
                // Ambil laporan untuk modul ini dan mahasiswa ini
                $lap_query = mysqli_query($conn, "SELECT * FROM laporan WHERE id_mahasiswa=$mahasiswa_id AND id_modul={$modul['id']}");
                $lap = mysqli_fetch_assoc($lap_query);
                // DEBUG: tampilkan id_mahasiswa, id_modul, dan hasil query
                // echo "<!-- DEBUG: id_mahasiswa=$mahasiswa_id, id_modul={$modul['id']}, lap=" . print_r($lap, true) . " -->";
            ?>
            <tr class="border-b">
                <td class="px-4 py-2 align-middle"><?php echo htmlspecialchars($modul['judul']); ?></td>
                <td class="px-4 py-2 align-middle">
                    <?php if ($modul['file']): ?>
                        <a href="../uploads/modul/<?php echo $modul['file']; ?>" target="_blank" class="text-blue-600 underline">Unduh Materi</a>
                    <?php else: ?>
                        <span class="text-gray-400">-</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-2 align-middle">
                    <?php if ($lap && $lap['file']): ?>
                        <a href="../uploads/laporan/<?php echo $lap['file']; ?>" target="_blank" class="text-blue-600 underline">Lihat Laporan</a>
                        <span class="ml-2 text-xs">(<?php echo htmlspecialchars($lap['status']); ?>)</span>
                    <?php else: ?>
                        <form method="post" enctype="multipart/form-data" style="display:inline;">
                            <input type="hidden" name="id_modul" value="<?php echo $modul['id']; ?>">
                            <input type="file" name="file" accept="application/pdf" required class="inline-block">
                            <button type="submit" name="kumpul_laporan" class="bg-blue-600 text-white px-2 py-1 rounded text-sm">Kumpulkan</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-2 align-middle">
                    <?php
                    // Tampilkan nilai jika sudah dinilai, jika belum tampilkan '-'
                    if ($lap && $lap['status'] == 'dinilai' && isset($lap['nilai']) && $lap['nilai'] !== null && $lap['nilai'] !== '') {
                        echo htmlspecialchars($lap['nilai']);
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td class="px-4 py-2 align-middle">
                    <?php
                    // Tampilkan feedback jika sudah dinilai, jika belum tampilkan '-'
                    if ($lap && $lap['status'] == 'dinilai' && isset($lap['feedback']) && $lap['feedback'] !== null && $lap['feedback'] !== '') {
                        echo nl2br(htmlspecialchars($lap['feedback']));
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
    <a href="my_courses.php" class="text-blue-600 hover:underline">Kembali ke daftar praktikum</a>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>