<?php
if (session_status() === PHP_SESSION_NONE) session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$activePage = 'praktikum';
$pageTitle = 'Praktikum';
require_once '../config.php';


// --- Proses Tambah ---
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $semester = trim($_POST['semester']);
    if ($nama && $semester) {
        $nama = mysqli_real_escape_string($conn, $nama);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $semester = mysqli_real_escape_string($conn, $semester);
        mysqli_query($conn, "INSERT INTO praktikum (nama, deskripsi, semester) VALUES ('$nama', '$deskripsi', '$semester')");
        $_SESSION['pesan'] = "Data berhasil ditambah!";
    }
    header("Location: praktikum.php");
    exit();
}

// --- Proses Edit ---
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $semester = trim($_POST['semester']);
    if ($id && $nama && $semester) {
        $nama = mysqli_real_escape_string($conn, $nama);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $semester = mysqli_real_escape_string($conn, $semester);
        mysqli_query($conn, "UPDATE praktikum SET nama='$nama', deskripsi='$deskripsi', semester='$semester' WHERE id=$id");
        $_SESSION['pesan'] = "Data berhasil diupdate!";
    }
    header("Location: praktikum.php");
    exit();
}

// --- Proses Hapus ---
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id) {
        mysqli_query($conn, "DELETE FROM praktikum WHERE id=$id");
        $_SESSION['pesan'] = "Data berhasil dihapus!";
    }
    header("Location: praktikum.php");
    exit();
}

// --- Ambil Data Praktikum ---
$result = mysqli_query($conn, "SELECT * FROM praktikum ORDER BY id DESC");

// --- Ambil Data Untuk Edit ---
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM praktikum WHERE id=$id");
    $edit = mysqli_fetch_assoc($q);
}

require_once 'templates/header.php';
?>

<?php if (isset($_SESSION['pesan'])): ?>
    <div id="alert-pesan" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
    </div>
    <script>
        setTimeout(function() {
            var alertBox = document.getElementById('alert-pesan');
            if(alertBox) alertBox.style.display = 'none';
        }, 5000);
    </script>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold mb-4"><?php echo $edit ? 'Edit Praktikum' : 'Tambah Praktikum'; ?></h2>
    <form method="post" class="space-y-4">
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
        <?php endif; ?>
        <div>
            <label class="block mb-1 font-semibold">Nama Praktikum</label>
            <input type="text" name="nama" required class="w-full border rounded px-3 py-2" value="<?php echo $edit['nama'] ?? ''; ?>">
        </div>
        <div>
            <label class="block mb-1 font-semibold">Deskripsi</label>
            <textarea name="deskripsi" class="w-full border rounded px-3 py-2"><?php echo $edit['deskripsi'] ?? ''; ?></textarea>
        </div>
        <div>
            <label class="block mb-1 font-semibold">Semester</label>
            <input type="text" name="semester" required class="w-full border rounded px-3 py-2" value="<?php echo $edit['semester'] ?? ''; ?>">
        </div>
        <div>
            <?php if ($edit): ?>
                <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update</button>
                <a href="praktikum.php" class="ml-2 text-gray-600 hover:underline">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Mata Praktikum</h2>
    <table class="min-w-full table-auto">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">No</th>
            <th class="px-4 py-2 text-left">Nama</th>
            <th class="px-4 py-2 text-left">Deskripsi</th>
            <th class="px-4 py-2 text-left">Semester</th>
            <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
        <tr class="border-b">
            <td class="px-4 py-2 align-top text-left"><?php echo $no++; ?></td>
            <td class="px-4 py-2 align-top text-left"><?php echo htmlspecialchars($row['nama']); ?></td>
            <td class="px-4 py-2 align-top text-left break-words max-w-xs"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
            <td class="px-4 py-2 align-top text-left"><?php echo htmlspecialchars($row['semester']); ?></td>
            <td class="px-4 py-2 align-top text-left">
                <a href="praktikum.php?edit=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">Edit</a> | 
                <a href="praktikum.php?hapus=<?php echo $row['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php require_once 'templates/footer.php'; ?>