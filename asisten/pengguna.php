<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';
$pageTitle = 'Manajemen Pengguna';

// --- Proses Tambah ---
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    if ($nama && $email && $password && $role) {
        $nama = mysqli_real_escape_string($conn, $nama);
        $email = mysqli_real_escape_string($conn, $email);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, $role);
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['pesan'] = "Email sudah terdaftar!";
            $_SESSION['pesan_tipe'] = "error";
        } else {
            mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password_hash', '$role')");
            $_SESSION['pesan'] = "Pengguna berhasil ditambah!";
            $_SESSION['pesan_tipe'] = "success";
        }
    } else {
        $_SESSION['pesan'] = "Semua field wajib diisi!";
        $_SESSION['pesan_tipe'] = "error";
    }
    header("Location: pengguna.php");
    exit();
}

// --- Proses Edit ---
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);
    if ($id && $nama && $email && $role) {
        $nama = mysqli_real_escape_string($conn, $nama);
        $email = mysqli_real_escape_string($conn, $email);
        $role = mysqli_real_escape_string($conn, $role);
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET nama='$nama', email='$email', password='$password_hash', role='$role' WHERE id=$id");
        } else {
            mysqli_query($conn, "UPDATE users SET nama='$nama', email='$email', role='$role' WHERE id=$id");
        }
        $_SESSION['pesan'] = "Pengguna berhasil diupdate!";
        $_SESSION['pesan_tipe'] = "success";
    } else {
        $_SESSION['pesan'] = "Semua field wajib diisi!";
        $_SESSION['pesan_tipe'] = "error";
    }
    header("Location: pengguna.php");
    exit();
}

// --- Proses Hapus ---
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        $_SESSION['pesan'] = "Pengguna berhasil dihapus!";
        $_SESSION['pesan_tipe'] = "success";
    }
    header("Location: pengguna.php");
    exit();
}

// --- Ambil Data Semua Pengguna ---
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, nama ASC");

// --- Ambil Data Untuk Edit ---
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    $edit = mysqli_fetch_assoc($q);
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

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold mb-4"><?php echo $edit ? 'Edit Pengguna' : 'Tambah Pengguna'; ?></h2>
    <form method="post" class="space-y-4">
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
        <?php endif; ?>
        <div>
            <label class="block mb-1 font-semibold">Nama</label>
            <input type="text" name="nama" required class="w-full border rounded px-3 py-2" value="<?php echo $edit['nama'] ?? ''; ?>">
        </div>
        <div>
            <label class="block mb-1 font-semibold">Email</label>
            <input type="email" name="email" required class="w-full border rounded px-3 py-2" value="<?php echo $edit['email'] ?? ''; ?>">
        </div>
        <div>
            <label class="block mb-1 font-semibold">Role</label>
            <select name="role" required class="w-full border rounded px-3 py-2">
                <option value="">Pilih Role</option>
                <option value="mahasiswa" <?php if(($edit['role'] ?? '')=='mahasiswa') echo 'selected'; ?>>Mahasiswa</option>
                <option value="asisten" <?php if(($edit['role'] ?? '')=='asisten') echo 'selected'; ?>>Asisten</option>
            </select>
        </div>
        <div>
            <label class="block mb-1 font-semibold">Password <?php if ($edit): ?><span class="text-xs text-gray-500">(Kosongkan jika tidak diubah)</span><?php endif; ?></label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <?php if ($edit): ?>
                <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update</button>
                <a href="pengguna.php" class="ml-2 text-gray-600 hover:underline">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Pengguna</h2>
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">No</th>
                <th class="px-4 py-2 text-left">Nama</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Role</th>
                <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
            <tr class="border-b">
                <td class="px-4 py-2"><?php echo $no++; ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['nama']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['email']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['role']); ?></td>
                <td class="px-4 py-2">
                    <a href="pengguna.php?edit=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">Edit</a> | 
                    <a href="pengguna.php?hapus=<?php echo $row['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin hapus?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php'; ?>