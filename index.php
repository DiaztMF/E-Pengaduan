<?php
require_once 'config.php';

$conn = getConnection();
$success = false;
$error = '';

// Ambil kategori
$kategoriQuery = "SELECT * FROM kategori_pengaduan ORDER BY nama_kategori";
$kategoriResult = $conn->query($kategoriQuery);

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = cleanInput($_POST['nama']);
    $kelas = cleanInput($_POST['kelas']);
    $email = cleanInput($_POST['email']);
    $telepon = cleanInput($_POST['telepon']);
    $kategori_id = (int)$_POST['kategori'];
    $judul = cleanInput($_POST['judul']);
    $isi = cleanInput($_POST['isi']);
    
    if (empty($nama) || empty($kelas) || empty($judul) || empty($isi)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Insert siswa
        $stmt = $conn->prepare("INSERT INTO siswa (nama_lengkap, kelas, email, no_telepon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $kelas, $email, $telepon);
        $stmt->execute();
        $siswa_id = $stmt->insert_id;
        
        // Insert pengaduan
        $stmt = $conn->prepare("INSERT INTO pengaduan (siswa_id, kategori_id, judul, isi_pengaduan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $siswa_id, $kategori_id, $judul, $isi);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Gagal mengirim pengaduan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Pengaduan - Sistem Pengaduan Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-indigo-600">e-Pengaduan</h1>
                </div>
                <a href="admin/login.php" class="text-gray-600 hover:text-indigo-600 font-medium">Login Admin</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-10">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Sampaikan Pengaduan Anda</h2>
            <p class="text-gray-600 text-lg">Kami siap mendengarkan dan menindaklanjuti setiap pengaduan Anda</p>
        </div>

        <!-- Alert Success -->
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="font-semibold">Pengaduan berhasil dikirim! Kami akan segera menindaklanjuti.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alert Error -->
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p class="font-semibold"><?php echo $error; ?></p>
        </div>
        <?php endif; ?>

        <!-- Form Pengaduan -->
        <div class="bg-white rounded-lg shadow-xl p-8">
            <form method="POST" action="">
                <!-- Data Siswa -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-indigo-500">Data Siswa</h3>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" name="nama" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kelas *</label>
                            <input type="text" name="kelas" placeholder="Contoh: XII IPA 1" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                            <input type="tel" name="telepon" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Detail Pengaduan -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-indigo-500">Detail Pengaduan</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                            <select name="kategori" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Pilih Kategori</option>
                                <?php while ($kategori = $kategoriResult->fetch_assoc()): ?>
                                <option value="<?php echo $kategori['id']; ?>">
                                    <?php echo $kategori['nama_kategori']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Judul Pengaduan *</label>
                            <input type="text" name="judul" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Ringkasan singkat pengaduan Anda">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Isi Pengaduan *</label>
                            <textarea name="isi" rows="6" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Jelaskan pengaduan Anda secara detail..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                        Kirim Pengaduan
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="font-semibold text-blue-900 mb-2">📌 Informasi Penting:</h4>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Pengaduan akan diproses dalam waktu maksimal 3x24 jam</li>
                <li>• Identitas pelapor akan dijaga kerahasiaannya</li>
                <li>• Harap mengisi data dengan lengkap dan jelas</li>
            </ul>
        </div>
    </div>
</body>
</html>