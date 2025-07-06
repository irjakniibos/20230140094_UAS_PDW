<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

// Cek login mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$mahasiswa_id = $_SESSION['user_id'];
$alert = [
    'type' => 'error',
    'message' => 'Terjadi kesalahan. Silakan coba lagi.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_praktikum'])) {
    $id_praktikum = intval($_POST['id_praktikum']);

    // Cek apakah sudah pernah daftar
    $cek = mysqli_query($conn, "SELECT * FROM praktikum_peserta WHERE id_praktikum=$id_praktikum AND id_mahasiswa=$mahasiswa_id");
    if (mysqli_num_rows($cek) > 0) {
        $alert = [
            'type' => 'warning',
            'message' => 'Kamu sudah terdaftar di praktikum ini.'
        ];
    } else {
        // Daftarkan mahasiswa ke praktikum
        $insert = mysqli_query($conn, "INSERT INTO praktikum_peserta (id_praktikum, id_mahasiswa) VALUES ($id_praktikum, $mahasiswa_id)");
        if ($insert) {
            $alert = [
                'type' => 'success',
                'message' => 'Berhasil mendaftar ke praktikum!'
            ];
        } else {
            $alert = [
                'type' => 'error',
                'message' => 'Gagal mendaftar. Silakan coba lagi.'
            ];
        }
    }
} else {
    // Jika akses langsung, redirect ke katalog
    header('Location: course.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Praktikum | SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-300 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full text-center">
        <?php
            $color = [
                'success' => 'bg-green-100 border-green-400 text-green-700',
                'error' => 'bg-red-100 border-red-400 text-red-700',
                'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700'
            ][$alert['type']];
            $icon = [
                'success' => '✔️',
                'error' => '❌',
                'warning' => '⚠️'
            ][$alert['type']];
        ?>
        <div class="<?php echo $color; ?> border px-4 py-3 rounded mb-6 flex items-center justify-center text-lg font-semibold">
            <span class="mr-2 text-2xl"><?php echo $icon; ?></span>
            <?php echo $alert['message']; ?>
        </div>
        <a href="course.php" class="inline-block mt-4 bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-lg font-semibold shadow transition">Kembali ke Katalog Praktikum</a>
    </div>
</body>
</html>