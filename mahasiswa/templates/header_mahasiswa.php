<?php
// filepath: /Applications/XAMPP/xamppfiles/htdocs/20230140094_UAS_PDW/mahasiswa/templates/header_mahasiswa.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-white shadow mb-8">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-8">
                <span class="text-2xl font-extrabold text-red-600 tracking-wide">SIMPRAK</span>
                <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'text-blue-700 font-bold border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-700 font-semibold'; ?> text-lg">Dashboard</a>
                <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? 'text-blue-700 font-bold border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-700 font-semibold'; ?> text-lg">Praktikum Saya</a>
                <a href="course.php" class="<?php echo ($activePage == 'course') ? 'text-blue-700 font-bold border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-700 font-semibold'; ?> text-lg">Katalog Praktikum</a>
            </div>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded transition text-lg">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto p-6 lg:p-8">