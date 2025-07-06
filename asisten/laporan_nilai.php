<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

$id = intval($_GET['id']);
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT laporan.*, u.nama as mahasiswa_nama, m.judul as modul_judul FROM laporan 
    LEFT JOIN users u ON laporan.id_mahasiswa=u.id 
    LEFT JOIN modul m ON laporan.id_modul=m.id 
    WHERE laporan.id=$id"));

if (isset($_POST['simpan'])) {
    $nilai = trim($_POST['nilai']);
    $feedback = trim($_POST['feedback']);
    mysqli_query($conn, "UPDATE laporan SET nilai='$nilai', feedback='$feedback', status='dinilai' WHERE id=$id");
    $_SESSION['pesan'] = "Penilaian berhasil disimpan!";
    header("Location: laporan.php");
    exit();
}

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold mb-4">Detail Laporan Mahasiswa</h2>
    <p><b>Nama Mahasiswa:</b> <?php echo htmlspecialchars($row['mahasiswa_nama']); ?></p>
    <p><b>Modul:</b> <?php echo htmlspecialchars($row['modul_judul']); ?></p>
    <p><b>File:</b>
        <?php if ($row['file']): ?>
            <a href="../uploads/laporan/<?php echo $row['file']; ?>" target="_blank" class="text-blue-600 underline">Lihat File</a>
        <?php else: ?>
            <span class="text-gray-400">-</span>
        <?php endif; ?>
    </p>
    <form method="post" class="mt-4 space-y-4">
        <div>
            <label class="block mb-1">Nilai</label>
            <input type="text" name="nilai" class="border rounded px-3 py-2 w-full" value="<?php echo htmlspecialchars($row['nilai'] ?? ''); ?>">
        </div>
        <div>
            <label class="block mb-1">Feedback</label>
            <textarea name="feedback" class="border rounded px-3 py-2 w-full"><?php echo htmlspecialchars($row['feedback'] ?? ''); ?></textarea>
        </div>
        <button type="submit" name="simpan" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
        <a href="laporan.php" class="ml-2 text-gray-600 hover:underline">Kembali</a>
    </form>
</div>
<?php require_once 'templates/footer.php'; ?>