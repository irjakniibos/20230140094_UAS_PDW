<?php
if (session_status() === PHP_SESSION_NONE) session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$activePage = 'modul';
$pageTitle = 'Manajemen Modul';
require_once '../config.php';

// Ambil daftar praktikum untuk dropdown
$praktikumList = mysqli_query($conn, "SELECT id, nama FROM praktikum ORDER BY nama ASC");

// --- Proses Tambah ---
if (isset($_POST['tambah'])) {
    $id_praktikum = intval($_POST['id_praktikum']);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $file = '';

    // Cek file wajib diisi
    if (empty($_FILES['file']['name'])) {
        $_SESSION['pesan'] = "File modul wajib diupload!";
        $_SESSION['pesan_tipe'] = "error";
        header("Location: modul.php");
        exit();
    }

    // Upload file jika ada
    $targetDir = "../uploads/modul/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $file = uniqid() . '_' . basename($_FILES['file']['name']);
    $targetFile = $targetDir . $file;
    move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);

    if ($judul && $id_praktikum) {
        $judul = mysqli_real_escape_string($conn, $judul);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $file = mysqli_real_escape_string($conn, $file);
        mysqli_query($conn, "INSERT INTO modul (id_praktikum, judul, deskripsi, file) VALUES ($id_praktikum, '$judul', '$deskripsi', '$file')");
        $_SESSION['pesan'] = "Modul berhasil ditambah!";
        $_SESSION['pesan_tipe'] = "success";
    }
    header("Location: modul.php");
    exit();
}

// --- Proses Edit ---
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $id_praktikum = intval($_POST['id_praktikum']);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $file = $_POST['file_lama'];
    // Upload file baru jika ada
    if (!empty($_FILES['file']['name'])) {
        $targetDir = "../uploads/modul/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $file = uniqid() . '_' . basename($_FILES['file']['name']);
        $targetFile = $targetDir . $file;
        move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
    }
    if ($id && $judul && $id_praktikum) {
        $judul = mysqli_real_escape_string($conn, $judul);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $file = mysqli_real_escape_string($conn, $file);
        mysqli_query($conn, "UPDATE modul SET id_praktikum=$id_praktikum, judul='$judul', deskripsi='$deskripsi', file='$file' WHERE id=$id");
        $_SESSION['pesan'] = "Modul berhasil diupdate!";
        $_SESSION['pesan_tipe'] = "success";
    }
    header("Location: modul.php");
    exit();
}

// --- Proses Hapus ---
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id) {
        // Hapus file dari server
        $q = mysqli_query($conn, "SELECT file FROM modul WHERE id=$id");
        $data = mysqli_fetch_assoc($q);
        if ($data && $data['file'] && file_exists("../uploads/modul/" . $data['file'])) {
            unlink("../uploads/modul/" . $data['file']);
        }
        mysqli_query($conn, "DELETE FROM modul WHERE id=$id");
        $_SESSION['pesan'] = "Modul berhasil dihapus!";
        $_SESSION['pesan_tipe'] = "success";
    }
    header("Location: modul.php");
    exit();
}

// --- Ambil Data Modul (join praktikum untuk tampilkan nama praktikum) ---
$result = mysqli_query($conn, "SELECT m.*, p.nama AS nama_praktikum FROM modul m LEFT JOIN praktikum p ON m.id_praktikum = p.id ORDER BY m.id DESC");

// --- Ambil Data Untuk Edit ---
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM modul WHERE id=$id");
    $edit = mysqli_fetch_assoc($q);
    // Ambil ulang daftar praktikum agar dropdown tetap muncul saat edit
    $praktikumList = mysqli_query($conn, "SELECT id, nama FROM praktikum ORDER BY nama ASC");
}

require_once 'templates/header.php';
?>

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

<div class="bg-white p-6 rounded-lg shadow-md mb-8 mt-8">
    <h2 class="text-xl font-bold mb-4"><?php echo $edit ? 'Edit Modul' : 'Tambah Modul'; ?></h2>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
            <input type="hidden" name="file_lama" value="<?php echo $edit['file']; ?>">
        <?php endif; ?>
        <div>
            <label class="block mb-1 font-semibold">Praktikum</label>
            <select name="id_praktikum" required class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih Praktikum --</option>
                <?php while($p = mysqli_fetch_assoc($praktikumList)): ?>
                    <option value="<?php echo $p['id']; ?>"
                        <?php if(isset($edit['id_praktikum']) && $edit['id_praktikum'] == $p['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($p['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block mb-1 font-semibold">Judul Modul</label>
            <input type="text" name="judul" required class="w-full border rounded px-3 py-2" value="<?php echo $edit['judul'] ?? ''; ?>">
        </div>
        <div>
            <label class="block mb-1 font-semibold">Deskripsi</label>
            <textarea name="deskripsi" class="w-full border rounded px-3 py-2"><?php echo $edit['deskripsi'] ?? ''; ?></textarea>
        </div>
        <div>
            <label class="block mb-1 font-semibold">File Modul (PDF)</label>
            <input type="file" name="file" accept="application/pdf" class="block" <?php echo $edit ? '' : 'required'; ?>>
            <?php if ($edit && $edit['file']): ?>
                <div class="mt-2 text-sm">File saat ini: <a href="../uploads/modul/<?php echo $edit['file']; ?>" target="_blank" class="text-blue-600 underline"><?php echo $edit['file']; ?></a></div>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($edit): ?>
                <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update</button>
                <a href="modul.php" class="ml-2 text-gray-600 hover:underline">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Modul</h2>
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">No</th>
                <th class="px-4 py-2 text-left">Praktikum</th>
                <th class="px-4 py-2 text-left">Judul</th>
                <th class="px-4 py-2 text-left">Deskripsi</th>
                <th class="px-4 py-2 text-left">File</th>
                <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
            <tr class="border-b">
                <td class="px-4 py-2 align-top text-left"><?php echo $no++; ?></td>
                <td class="px-4 py-2 align-top text-left"><?php echo htmlspecialchars($row['nama_praktikum'] ?? '-'); ?></td>
                <td class="px-4 py-2 align-top text-left"><?php echo htmlspecialchars($row['judul']); ?></td>
                <td class="px-4 py-2 align-top text-left break-words max-w-xs"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                <td class="px-4 py-2 align-top text-left">
                    <?php if ($row['file']): ?>
                        <a href="../uploads/modul/<?php echo $row['file']; ?>" target="_blank" class="text-blue-600 underline">Lihat File</a>
                    <?php else: ?>
                        <span class="text-gray-400">-</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-2 align-top text-left">
                    <a href="modul.php?edit=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">Edit</a> | 
                    <a href="modul.php?hapus=<?php echo $row['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin hapus?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php';