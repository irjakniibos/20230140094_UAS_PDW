<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';
$pageTitle = 'Laporan Masuk';

// Ambil data modul dan mahasiswa untuk filter
$modulList = mysqli_query($conn, "SELECT * FROM modul ORDER BY judul ASC");
$mahasiswaList = mysqli_query($conn, "SELECT * FROM users WHERE role='mahasiswa' ORDER BY nama ASC");

// Ambil filter dari GET
$filter_modul = isset($_GET['modul']) ? intval($_GET['modul']) : '';
$filter_mahasiswa = isset($_GET['mahasiswa']) ? intval($_GET['mahasiswa']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Query laporan (tambahkan join ke praktikum)
$where = [];
if ($filter_modul) $where[] = "laporan.id_modul = $filter_modul";
if ($filter_mahasiswa) $where[] = "laporan.id_mahasiswa = $filter_mahasiswa";
if ($filter_status) $where[] = "laporan.status = '".mysqli_real_escape_string($conn, $filter_status)."'";
$where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';

$sql = "SELECT laporan.*, 
            m.judul as modul_judul, 
            u.nama as mahasiswa_nama,
            p.nama as praktikum_nama
        FROM laporan 
        LEFT JOIN modul m ON laporan.id_modul = m.id
        LEFT JOIN users u ON laporan.id_mahasiswa = u.id
        LEFT JOIN praktikum p ON m.id_praktikum = p.id
        $where_sql
        ORDER BY laporan.created_at DESC";
$laporan = mysqli_query($conn, $sql);

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-2xl font-bold mb-4">Daftar Laporan Mahasiswa</h2>
    <form method="get" class="mb-6 flex flex-wrap gap-4">
        <div>
            <label class="block mb-1">Modul</label>
            <select name="modul" class="border rounded px-2 py-1">
                <option value="">Semua</option>
                <?php mysqli_data_seek($modulList, 0); while($m = mysqli_fetch_assoc($modulList)): ?>
                    <option value="<?php echo $m['id']; ?>" <?php if($filter_modul==$m['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($m['judul']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block mb-1">Mahasiswa</label>
            <select name="mahasiswa" class="border rounded px-2 py-1">
                <option value="">Semua</option>
                <?php mysqli_data_seek($mahasiswaList, 0); while($mhs = mysqli_fetch_assoc($mahasiswaList)): ?>
                    <option value="<?php echo $mhs['id']; ?>" <?php if($filter_mahasiswa==$mhs['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($mhs['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block mb-1">Status</label>
            <select name="status" class="border rounded px-2 py-1">
                <option value="">Semua</option>
                <option value="dikirim" <?php if($filter_status=='dikirim') echo 'selected'; ?>>Belum Dinilai</option>
                <option value="dinilai" <?php if($filter_status=='dinilai') echo 'selected'; ?>>Sudah Dinilai</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
        </div>
    </form>

    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2">No</th>
                <th class="px-4 py-2">Mahasiswa</th>
                <th class="px-4 py-2">Praktikum</th>
                <th class="px-4 py-2">Modul</th>
                <th class="px-4 py-2">File</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = mysqli_fetch_assoc($laporan)): ?>
            <tr class="border-b">
                <td class="px-4 py-2"><?php echo $no++; ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['mahasiswa_nama']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['praktikum_nama']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['modul_judul']); ?></td>
                <td class="px-4 py-2">
                    <?php if ($row['file']): ?>
                        <a href="../uploads/laporan/<?php echo $row['file']; ?>" target="_blank" class="text-blue-600 underline">Lihat File</a>
                    <?php else: ?>
                        <span class="text-gray-400">-</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-2">
                    <?php
                    echo $row['status'] == 'dinilai' ? '<span class="text-green-600 font-semibold">Sudah Dinilai</span>' : '<span class="text-yellow-600 font-semibold">Belum Dinilai</span>';
                    ?>
                </td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td class="px-4 py-2">
                    <a href="laporan_nilai.php?id=<?php echo $row['id']; ?>" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Detail</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php'; ?>