<?php
require_once '../config.php';
startSession();

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$conn = getConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = '';
$error = '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = cleanInput($_POST['status']);
    $catatan = cleanInput($_POST['catatan']);
    
    $updateQuery = "UPDATE pengaduan SET status = ?, catatan_admin = ?";
    $params = [$status, $catatan];
    $types = "ss";
    
    if ($status === 'diproses' && isset($_POST['set_diproses'])) {
        $updateQuery .= ", tanggal_diproses = NOW()";
    } elseif ($status === 'selesai' && isset($_POST['set_selesai'])) {
        $updateQuery .= ", tanggal_selesai = NOW()";
    }
    
    $updateQuery .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $success = "Status pengaduan berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui status!";
    }
}

// Ambil detail pengaduan
$query = "
    SELECT 
        p.*,
        s.nama_lengkap,
        s.kelas,
        s.email,
        s.no_telepon,
        k.nama_kategori
    FROM pengaduan p
    JOIN siswa s ON p.siswa_id = s.id
    JOIN kategori_pengaduan k ON p.kategori_id = k.id
    WHERE p.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('dashboard.php');
}

$pengaduan = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengaduan #<?php echo $id; ?> - e-Pengaduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-indigo-600 hover:text-indigo-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-indigo-600">Detail Pengaduan</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Halo, <strong><?php echo $_SESSION['admin_nama']; ?></strong></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 py-8">
        <!-- Alert -->
        <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Detail Pengaduan -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Pengaduan -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $pengaduan['judul']; ?></h2>
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <span>ID: #<?php echo $pengaduan['id']; ?></span>
                                <span>•</span>
                                <span><?php echo formatTanggal($pengaduan['tanggal_pengaduan']); ?></span>
                            </div>
                        </div>
                        <span class="px-4 py-2 text-sm font-semibold rounded-full <?php echo getBadgeStatus($pengaduan['status']); ?>">
                            <?php echo ucfirst($pengaduan['status']); ?>
                        </span>
                    </div>

                    <div class="mb-4">
                        <span class="inline-block bg-indigo-100 text-indigo-800 text-sm px-3 py-1 rounded-full">
                            <?php echo $pengaduan['nama_kategori']; ?>
                        </span>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Isi Pengaduan:</h3>
                        <p class="text-gray-700 whitespace-pre-line leading-relaxed"><?php echo nl2br($pengaduan['isi_pengaduan']); ?></p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Timeline</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-green-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Pengaduan Dibuat</p>
                                <p class="text-sm text-gray-600"><?php echo formatTanggal($pengaduan['tanggal_pengaduan']); ?></p>
                            </div>
                        </div>

                        <?php if ($pengaduan['tanggal_diproses']): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-blue-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Mulai Diproses</p>
                                <p class="text-sm text-gray-600"><?php echo formatTanggal($pengaduan['tanggal_diproses']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($pengaduan['tanggal_selesai']): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-green-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Selesai Ditangani</p>
                                <p class="text-sm text-gray-600"><?php echo formatTanggal($pengaduan['tanggal_selesai']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($pengaduan['catatan_admin']): ?>
                <!-- Catatan Admin -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Catatan Admin:</h3>
                    <p class="text-gray-700 whitespace-pre-line"><?php echo nl2br($pengaduan['catatan_admin']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Info Siswa -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Informasi Siswa</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Nama Lengkap</p>
                            <p class="font-medium text-gray-900"><?php echo $pengaduan['nama_lengkap']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kelas</p>
                            <p class="font-medium text-gray-900"><?php echo $pengaduan['kelas']; ?></p>
                        </div>
                        <?php if ($pengaduan['email']): ?>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium text-gray-900"><?php echo $pengaduan['email']; ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($pengaduan['no_telepon']): ?>
                        <div>
                            <p class="text-sm text-gray-600">No. Telepon</p>
                            <p class="font-medium text-gray-900"><?php echo $pengaduan['no_telepon']; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Update Status</h3>
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="pending" <?php echo $pengaduan['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="diproses" <?php echo $pengaduan['status'] === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="selesai" <?php echo $pengaduan['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="ditolak" <?php echo $pengaduan['status'] === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Admin</label>
                            <textarea name="catatan" rows="4" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="Tambahkan catatan atau keterangan..."><?php echo $pengaduan['catatan_admin']; ?></textarea>
                        </div>

                        <div class="mb-4 space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="set_diproses" class="mr-2">
                                <span class="text-sm text-gray-700">Set tanggal diproses ke sekarang</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="set_selesai" class="mr-2">
                                <span class="text-sm text-gray-700">Set tanggal selesai ke sekarang</span>
                            </label>
                        </div>

                        <button type="submit" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>