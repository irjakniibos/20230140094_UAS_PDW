<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

$isMahasiswa = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa';
$mahasiswa_id = $isMahasiswa ? $_SESSION['user_id'] : null;

// Proses pencarian
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '';
if ($search !== '') {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where = "WHERE nama LIKE '%$search_esc%' OR deskripsi LIKE '%$search_esc%'";
}
$sql = "SELECT * FROM praktikum $where ORDER BY nama ASC";
$result = mysqli_query($conn, $sql);

// Untuk mahasiswa: ambil daftar praktikum yang sudah didaftar
$praktikumTerdaftar = [];
if ($isMahasiswa) {
    $q = mysqli_query($conn, "SELECT id_praktikum FROM praktikum_peserta WHERE id_mahasiswa=$mahasiswa_id");
    while ($row = mysqli_fetch_assoc($q)) {
        $praktikumTerdaftar[] = $row['id_praktikum'];
    }
}
$pageTitle = 'Katalog Praktikum';
$activePage = 'course';

// Pakai header berbeda untuk publik dan mahasiswa
if ($isMahasiswa) {
    require_once 'templates/header_mahasiswa.php';
} else {
    // Header sederhana untuk publik
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>SIMPRAK - Katalog Praktikum</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-blue-50">
    <?php if (!$isMahasiswa): ?>
        <header class="w-full bg-white shadow mb-8">
            <div class="max-w-6xl mx-auto flex items-center justify-between px-6 py-4">
                <div class="text-2xl font-extrabold text-red-600 tracking-wide">SIMPRAK</div>
                <a href="../login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold transition">Login</a>
            </div>
        </header>
    <?php endif;
}
?>

<!-- Hero Section -->
<?php if (!$isMahasiswa): ?>
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-8 flex flex-col md:flex-row items-center mb-8">
    <div class="flex-1">
        <h1 class="text-3xl md:text-4xl font-extrabold text-blue-900 mb-2">Selamat Datang di <span class="text-red-600">SIMPRAK</span></h1>
        <p class="text-gray-700 mb-4">Ayo tingkatkan skillmu dengan mengikuti praktikum favoritmu! Temukan berbagai mata praktikum menarik di bawah ini.</p>
    </div>
    <div class="flex-shrink-0 mt-4 md:mt-0 md:ml-8">
        <img src="https://industri.unjaya.ac.id/wp-content/uploads/2024/01/image-1.png" alt="Lab" class="rounded-lg shadow-md w-48 h-32 object-cover">
    </div>
</div>
<?php endif; ?>

<!-- Search -->
<div class="max-w-2xl mx-auto mb-8">
    <form method="get" class="flex">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama atau deskripsi praktikum..." class="flex-1 px-4 py-2 rounded-l border border-gray-300 focus:outline-none">
        <button type="submit" class="<?php echo $isMahasiswa ? 'bg-gray-900' : 'bg-blue-700'; ?> text-white px-6 py-2 rounded-r">Cari</button>
    </form>
</div>

<!-- Katalog Praktikum -->
<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold text-center mb-6 <?php echo $isMahasiswa ? 'text-gray-800' : 'text-blue-900'; ?>">Katalog Praktikum</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center border <?php echo $isMahasiswa ? 'border-gray-100' : 'border-blue-100'; ?> hover:shadow-lg transition">
                <div class="w-16 h-16 <?php echo $isMahasiswa ? 'bg-gray-100' : 'bg-blue-50'; ?> rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 <?php echo $isMahasiswa ? 'text-gray-400' : 'text-blue-300'; ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12h8M12 8v8"/></svg>
                </div>
                <div class="font-bold text-lg mb-2 <?php echo $isMahasiswa ? 'text-gray-800' : 'text-blue-800'; ?>"><?php echo htmlspecialchars($row['nama']); ?></div>
                <div class="text-gray-500 text-center mb-4"><?php echo htmlspecialchars($row['deskripsi']); ?></div>
                <?php if ($isMahasiswa): ?>
                    <div class="flex w-full gap-2">
                        <?php if (in_array($row['id'], $praktikumTerdaftar)): ?>
                            <button class="flex-1 bg-gray-200 text-gray-500 font-semibold py-2 rounded cursor-not-allowed" disabled>Sudah Terdaftar</button>
                        <?php else: ?>
                            <form method="post" action="daftar_praktikum.php" class="flex-1">
                                <input type="hidden" name="id_praktikum" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded transition">Daftar</button>
                            </form>
                        <?php endif; ?>
                        <a href="my_courses.php?praktikum=<?php echo $row['id']; ?>" class="flex-1 bg-gray-900 text-white font-semibold py-2 rounded hover:bg-gray-800 text-center transition">Lihat Detail</a>
                    </div>
                <?php else: ?>
                    <a href="../login.php" class="mt-auto bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800 transition font-semibold">Lihat Detail</a>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-3 text-center text-gray-500">Tidak ada praktikum ditemukan.</div>
        <?php endif; ?>
    </div>
</div>

<div class="py-8"></div>
<?php require_once 'templates/footer_mahasiswa.php'; ?>
<?php if (!$isMahasiswa): ?>
</body>
</html>
<?php endif; ?>