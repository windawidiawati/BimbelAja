<?php
include '../config/database.php';

// --- UPDATE DATA PENGATURAN --- (PROSES DULU SEBELUM OUTPUT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_nama    = $_POST['nama'] ?? '';
    $post_alamat  = $_POST['alamat'] ?? '';
    $post_telepon = $_POST['telepon'] ?? '';
    $post_email   = $_POST['email'] ?? '';
    $post_logo_path = '';

    // Ambil logo lama
    $cek = mysqli_query($conn, "SELECT logo_path FROM pengaturan WHERE id=1 LIMIT 1");
    $row = mysqli_fetch_assoc($cek);
    if ($row) {
        $post_logo_path = $row['logo_path'];
    }

    // Upload logo baru (jika ada)
    if (isset($_FILES['logo_input']) && $_FILES['logo_input']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['logo_input']['tmp_name'];
        $file_name = time() . "_" . basename($_FILES['logo_input']['name']);
        $dest_path = '../uploads/' . $file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $post_logo_path = $dest_path;
        } else {
            header("Location: pengaturan.php?status=error&message=" . urlencode("Gagal mengunggah logo."));
            exit;
        }
    }

    // Update DB
    $update = mysqli_query($conn, "UPDATE pengaturan SET 
        nama = '".mysqli_real_escape_string($conn, $post_nama)."',
        alamat = '".mysqli_real_escape_string($conn, $post_alamat)."',
        telepon = '".mysqli_real_escape_string($conn, $post_telepon)."',
        email = '".mysqli_real_escape_string($conn, $post_email)."',
        logo_path = '".mysqli_real_escape_string($conn, $post_logo_path)."',
        updated_at = NOW()
        WHERE id = 1
    ");

    if ($update) {
        header("Location: pengaturan.php?status=success&message=" . urlencode("Data pengaturan berhasil disimpan!"));
        exit;
    } else {
        header("Location: pengaturan.php?status=error&message=" . urlencode("Gagal menyimpan data: " . mysqli_error($conn)));
        exit;
    }
}

// --- AMBIL DATA PENGATURAN (SETELAH PROSES) ---
$query = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($query);

$nama      = $data_pengaturan['nama'] ?? '';
$alamat    = $data_pengaturan['alamat'] ?? '';
$telepon   = $data_pengaturan['telepon'] ?? '';
$email     = $data_pengaturan['email'] ?? '';
$logo_path = $data_pengaturan['logo_path'] ?? '';

include '../includes/admin_header.php';
?>


<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <main class="flex-1 p-6 md:p-10 flex flex-col items-center justify-center">
        <div class="container mx-auto max-w-2xl">
            <h1 class="text-3xl font-semibold text-gray-800 mb-6 text-center">Pengaturan Admin</h1>

            <div id="messageBox" class="p-4 mb-4 text-sm rounded-lg hidden" role="alert">
                <span class="font-medium"></span>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 w-full">
                <form id="pengaturanForm" class="space-y-6" action="" method="POST" enctype="multipart/form-data">
                    
                    <!-- Nama -->
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Bimbel</label>
                        <input type="text" id="nama" name="nama" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                            value="<?php echo $nama; ?>">
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"><?php echo $alamat; ?></textarea>
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label for="telepon" class="block text-sm font-medium text-gray-700">Telepon</label>
                        <input type="tel" id="telepon" name="telepon"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                            value="<?php echo $telepon; ?>">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                            value="<?php echo $email; ?>">
                    </div>

                    <!-- Logo -->
                    <div>
                        <label for="logo_input" class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                        <div class="flex items-center">
                            <input type="file" id="logo_input" name="logo_input" accept="image/*" class="hidden">
                            <button type="button" onclick="document.getElementById('logo_input').click()" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-200">
                                Pilih Logo
                            </button>
                            <span id="logo_name" class="ml-3 text-gray-500 text-sm"></span>
                        </div>
                        <img id="logo_preview" src="<?php echo $logo_path; ?>" alt="Logo Preview"
                            class="mt-4 max-h-40 rounded-lg shadow-md border-2 border-gray-300 <?php echo empty($logo_path) ? 'hidden' : ''; ?>">
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="pt-6">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/admin_footer.php'; ?>

<script>
    const logoInput = document.getElementById('logo_input');
    const logoPreview = document.getElementById('logo_preview');
    const logoNameSpan = document.getElementById('logo_name');
    const messageBox = document.getElementById('messageBox');

    function showMessage(message, type = 'success') {
        const messageSpan = messageBox.querySelector('span');
        messageSpan.textContent = message;
        messageBox.className = p-4 mb-4 text-sm rounded-lg ${type === 'success' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'};
        messageBox.classList.remove('hidden');
        setTimeout(() => { messageBox.classList.add('hidden'); }, 5000);
    }

    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');
    if (status && message) {
        showMessage(decodeURIComponent(message), status);
    }

    logoInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            logoNameSpan.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                logoPreview.src = e.target.result;
                logoPreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            logoNameSpan.textContent = '';
            logoPreview.src = '';
            logoPreview.classList.add('hidden');
        }
    });
</script>