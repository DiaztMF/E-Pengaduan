<?php
require_once '../config.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];
    $nama = cleanInput($_POST['nama']);
    
    if (empty($username) || empty($password) || empty($nama)) {
        $message = "Semua field harus diisi!";
    } else {
        $conn = getConnection();
        
        // Cek apakah username sudah ada
        $checkStmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $message = "Username sudah digunakan!";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin baru
            $stmt = $conn->prepare("INSERT INTO admin (username, password, nama_lengkap) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $nama);
            
            if ($stmt->execute()) {
                $success = true;
                $message = "Admin berhasil dibuat! Username: $username | Password: $password";
            } else {
                $message = "Gagal membuat admin: " . $conn->error;
            }
        }
    }
}

// Tampilkan admin yang sudah ada
$conn = getConnection();
$adminList = $conn->query("SELECT id, username, nama_lengkap, created_at FROM admin ORDER BY id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Admin Baru - e-Pengaduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Warning -->
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p class="font-bold">⚠️ PERINGATAN KEAMANAN!</p>
            <p class="text-sm">File ini hanya untuk setup awal. HAPUS file ini setelah berhasil membuat admin!</p>
        </div>

        <!-- Alert -->
        <?php if ($message): ?>
        <div class="<?php echo $success ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700'; ?> border-l-4 p-4 mb-6 rounded">
            <p class="font-semibold"><?php echo $message; ?></p>
        </div>
        <?php endif; ?>

        <!-- Form Buat Admin -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Buat Admin Baru</h2>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="text" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="text-sm text-gray-600 mt-1">Minimal 6 karakter</p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition">
                    Buat Admin
                </button>
            </form>
        </div>

        <!-- Daftar Admin -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Admin yang Ada</h2>
            
            <?php if ($adminList->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Username</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Nama Lengkap</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($admin = $adminList->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3 text-sm"><?php echo $admin['id']; ?></td>
                            <td class="px-4 py-3 text-sm font-medium"><?php echo $admin['username']; ?></td>
                            <td class="px-4 py-3 text-sm"><?php echo $admin['nama_lengkap']; ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($admin['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-gray-600">Belum ada admin di database.</p>
            <?php endif; ?>
        </div>

        <!-- Quick Links -->
        <div class="mt-6 flex space-x-4">
            <a href="index.php" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center py-3 rounded-lg font-semibold transition">
                Ke Halaman Utama
            </a>
            <a href="admin/login.php" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 rounded-lg font-semibold transition">
                Login Admin
            </a>
        </div>

        <!-- Instructions -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-bold text-blue-900 mb-2">📝 Cara Menggunakan:</h3>
            <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
                <li>Isi form di atas untuk membuat admin baru</li>
                <li>Catat username dan password yang dibuat</li>
                <li>Coba login di halaman admin dengan kredensial tersebut</li>
                <li><strong class="text-red-600">PENTING: Hapus file create_admin.php setelah selesai!</strong></li>
            </ol>
        </div>

        <!-- Troubleshooting -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="font-bold text-yellow-900 mb-2">🔧 Troubleshooting Login:</h3>
            <div class="text-sm text-yellow-800 space-y-2">
                <p><strong>Jika masih error "Username/Password salah":</strong></p>
                <ol class="list-decimal list-inside ml-4 space-y-1">
                    <li>Pastikan database <code class="bg-yellow-100 px-2 py-1 rounded">e_pengaduan</code> sudah dibuat</li>
                    <li>Pastikan tabel <code class="bg-yellow-100 px-2 py-1 rounded">admin</code> ada dan memiliki data</li>
                    <li>Gunakan file ini untuk membuat admin baru dengan password yang fresh</li>
                    <li>Pastikan tidak ada spasi di username dan password</li>
                    <li>Cek error di browser console (F12) atau PHP error log</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>