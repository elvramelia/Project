<?php
// Masukkan koneksi database
require_once '../config/database.php';

// --- LOGIC 1: MENANGANI UPLOAD ---
if (isset($_POST['upload'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $link = mysqli_real_escape_string($conn, $_POST['link_url']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $priority = (int) $_POST['priority'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    // Mapping status: UI mengirim 'active'/'inactive', DB butuh 1/0
    $active = ($_POST['status'] == 'active') ? 1 : 0;
    
    // Handle File Upload
    $target_dir = "uploads/"; // Pastikan folder ini ada!
    
    // Cek apakah folder uploads ada, jika tidak buat baru
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . '_' . basename($_FILES["banner_file"]["name"]); // Rename agar unik
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Cek apakah file benar-benar terupload
    if (!empty($_FILES["banner_file"]["name"])) {
        if (move_uploaded_file($_FILES["banner_file"]["tmp_name"], $target_file)) {
            // Query Insert
            $query = "INSERT INTO banners (title, description, image_url, link_url, active, position, priority) 
                      VALUES ('$title', '$desc', '$file_name', '$link', '$active', '$position', '$priority')";
            
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Banner berhasil diupload!'); window.location='uploadbanner.php';</script>";
            } else {
                echo "<script>alert('Gagal simpan ke database: " . mysqli_error($conn) . "');</script>";
            }
        } else {
            echo "<script>alert('Maaf, terjadi error saat mengupload file.');</script>";
        }
    } else {
        echo "<script>alert('Harap pilih gambar terlebih dahulu.');</script>";
    }
}

// --- LOGIC 2: MENANGANI DELETE ---
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    
    // Ambil nama file dulu untuk dihapus dari folder
    $q_file = mysqli_query($conn, "SELECT image_url FROM banners WHERE id='$id'");
    $data_file = mysqli_fetch_assoc($q_file);
    
    // Hapus dari database
    if (mysqli_query($conn, "DELETE FROM banners WHERE id='$id'")) {
        // Hapus file fisik jika ada
        if (file_exists("uploads/" . $data_file['image_url'])) {
            unlink("uploads/" . $data_file['image_url']);
        }
        echo "<script>alert('Banner berhasil dihapus!'); window.location='uploadbanner.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus banner.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Banner - PT Megatek Industrial Persada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS SAMA SEPERTI SEBELUMNYA (SAYA SINGKAT AGAR RAPI) */
        :root { --primary: #004080; --primary-light: #0066cc; --secondary: #333333; --accent: #e6b800; --light: #f5f5f5; --danger: #d32f2f; --success: #2e7d32; --warning: #f57c00; --info: #0288d1; --gray: #757575; --light-gray: #e0e0e0; --border-radius: 6px; --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f9f9f9; color: var(--secondary); display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--primary); color: white; padding: 20px 0; position: fixed; height: 100vh; overflow-y: auto; transition: all 0.3s; box-shadow: var(--box-shadow); z-index: 99; }
        .logo { padding: 0 20px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 20px; }
        .logo h1 { font-size: 22px; font-weight: 700; color: white; }
        .logo h2 { font-size: 14px; font-weight: 400; color: rgba(255, 255, 255, 0.8); margin-top: 5px; }
        .nav-menu { list-style: none; padding: 0 15px; }
        .nav-item { margin-bottom: 5px; }
        .nav-link { display: flex; align-items: center; padding: 12px 15px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: var(--border-radius); transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.1); color: white; }
        .nav-link i { margin-right: 12px; font-size: 18px; width: 24px; text-align: center; }
        .main-content { flex: 1; margin-left: 260px; padding: 20px; transition: all 0.3s; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--light-gray); margin-bottom: 30px; }
        .header h1 { color: var(--primary); font-size: 28px; display: flex; align-items: center; gap: 10px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-light); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .upload-section, .settings-section, .banners-section { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 25px; margin-bottom: 30px; }
        .section-title { font-size: 22px; color: var(--primary); font-weight: 600; margin-bottom: 20px; }
        .upload-area { border: 3px dashed var(--light-gray); border-radius: var(--border-radius); padding: 40px 20px; text-align: center; transition: all 0.3s; margin-bottom: 30px; cursor: pointer; }
        .upload-area:hover, .upload-area.drag-over { border-color: var(--primary); background-color: rgba(0, 64, 128, 0.02); }
        .file-input { display: none; }
        .upload-btn { display: inline-block; padding: 12px 30px; background-color: var(--primary); color: white; border-radius: var(--border-radius); font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .setting-group { margin-bottom: 20px; }
        .setting-label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--secondary); font-size: 15px; }
        .setting-control, .setting-select, .setting-textarea { width: 100%; padding: 12px 15px; border: 1px solid var(--light-gray); border-radius: var(--border-radius); font-size: 16px; }
        .setting-textarea { min-height: 100px; resize: vertical; }
        .btn { padding: 10px 20px; border: none; border-radius: var(--border-radius); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; font-size: 15px; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-danger { background-color: var(--danger); color: white; }
        .banners-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-top: 20px; }
        .banner-card { border: 1px solid var(--light-gray); border-radius: var(--border-radius); overflow: hidden; transition: all 0.3s; background: white;}
        .banner-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .banner-image { width: 100%; height: 150px; object-fit: cover; border-bottom: 1px solid var(--light-gray); }
        .banner-info { padding: 20px; }
        .banner-status { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-active { background-color: rgba(46, 125, 50, 0.15); color: var(--success); }
        .status-inactive { background-color: rgba(211, 47, 47, 0.15); color: var(--danger); }
        .banner-actions { display: flex; gap: 10px; margin-top: 15px; }
        .action-btn { padding: 8px 15px; border: none; border-radius: var(--border-radius); cursor: pointer; font-size: 14px; color: white;}
        .edit-btn { background-color: var(--primary); }
        .delete-btn { background-color: var(--danger); }
        
        /* Tambahan untuk file info & preview */
        .file-info { display: none; align-items: center; gap: 15px; padding: 15px; background-color: #f9f9f9; border-radius: var(--border-radius); margin-top: 15px; }
        .preview-container { text-align: center; }
        .preview-image { max-width: 100%; max-height: 200px; display: none; margin: 0 auto; border-radius: 6px; }
        
        @media (max-width: 992px) { .sidebar { width: 80px; } .main-content { margin-left: 80px; } .sidebar span, .sidebar h1, .sidebar h2 { display: none; } .nav-link { justify-content: center; } .nav-link i { margin: 0; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h1>Megatek</h1>
            <h2>Industrial</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="uploadbanner.php" class="nav-link active"><i class="fa-solid fa-download"></i><span>Upload Banner</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1><i class="fa-solid fa-download"></i> Upload Banner</h1>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="upload-section">
                <div class="section-header">
                    <h2 class="section-title">Upload Banner Baru</h2>
                </div>
                
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <div class="upload-text">
                        <h3>Drag & Drop file banner di sini</h3>
                        <p>atau klik untuk memilih file</p>
                        <label for="bannerFile" class="upload-btn">
                            <i class="fa-solid fa-folder-open"></i> Pilih File
                        </label>
                        <input type="file" id="bannerFile" name="banner_file" class="file-input" accept=".jpg,.jpeg,.png,.gif,.webp">
                    </div>
                    
                    <div class="file-info" id="fileInfo">
                        <i class="fa-solid fa-image" style="font-size: 24px;"></i>
                        <div>
                            <h4 id="fileName">nama_file.jpg</h4>
                            <p id="fileSize">0 KB</p>
                        </div>
                    </div>
                </div>

                <div class="banner-preview">
                    <div class="preview-container">
                        <img src="" alt="Preview" class="preview-image" id="previewImage">
                        <div id="previewPlaceholder" style="color: #999; padding: 20px;">
                            <p>Preview banner akan muncul di sini</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <div class="section-header">
                    <h2 class="section-title">Pengaturan Banner</h2>
                </div>
                <div class="settings-grid">
                    <div>
                        <div class="setting-group">
                            <label class="setting-label">Judul Banner</label>
                            <input type="text" name="title" class="setting-control" id="bannerTitle" required placeholder="Contoh: Promo Akhir Tahun">
                        </div>
                        <div class="setting-group">
                            <label class="setting-label">Link Target (Opsional)</label>
                            <input type="url" name="link_url" class="setting-control" placeholder="https://...">
                        </div>
                        <div class="setting-group">
                            <label class="setting-label">Posisi Tampilan</label>
                            <select name="position" class="setting-select">
                                <option value="homepage-top">Atas Halaman Utama</option>
                                <option value="homepage-middle">Tengah Halaman Utama</option>
                                <option value="sidebar">Sidebar</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="setting-group">
                            <label class="setting-label">Status</label>
                            <select name="status" class="setting-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="setting-group">
                            <label class="setting-label">Prioritas</label>
                            <select name="priority" class="setting-select">
                                <option value="1">Tinggi (1)</option>
                                <option value="2" selected>Normal (2)</option>
                                <option value="3">Rendah (3)</option>
                            </select>
                        </div>
                         <div class="setting-group">
                            <label class="setting-label">Deskripsi</label>
                            <textarea name="description" class="setting-textarea" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn" style="border: 1px solid #ccc; background: white;" onclick="window.location.reload()">Reset</button>
                    <button type="submit" name="upload" class="btn btn-primary">
                        <i class="fa-solid fa-upload"></i> Upload Database
                    </button>
                </div>
            </div>
        </form>

        <div class="banners-section">
            <h2 class="section-title">Banner Aktif</h2>
            <div class="banners-grid" id="bannersGrid">
                
                <?php
                // MENGAMBIL DATA DARI DATABASE
                $query = "SELECT * FROM banners ORDER BY created_at DESC";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        // Tentukan class status
                        $statusClass = ($row['active'] == 1) ? 'status-active' : 'status-inactive';
                        $statusText = ($row['active'] == 1) ? 'Aktif' : 'Tidak Aktif';
                        $imagePath = "uploads/" . $row['image_url'];
                        
                        // Cek jika gambar ada, kalau tidak pakai placeholder
                        if(!file_exists($imagePath) || empty($row['image_url'])) {
                            $imagePath = "https://via.placeholder.com/300x150?text=No+Image";
                        }
                        ?>
                        
                        <div class="banner-card">
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo $row['title']; ?>" class="banner-image">
                            <div class="banner-info">
                                <div class="banner-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div class="banner-meta" style="font-size: 12px; color: #777; margin-bottom: 10px;">
                                    <span><?php echo date('d M Y', strtotime($row['created_at'])); ?></span> | 
                                    <span>Posisi: <?php echo $row['position']; ?></span>
                                </div>
                                <div class="banner-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></div>
                                <div class="banner-actions">
                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Yakin ingin menghapus banner ini?')">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                } else {
                    echo "<p style='grid-column: 1/-1; text-align: center; color: #777;'>Belum ada banner yang diupload.</p>";
                }
                ?>

            </div>
        </div>

        <footer style="text-align: center; margin-top: 30px; color: #888; font-size: 14px;">
            <p>&copy; 2025 PT Megatek Industrial Persada</p>
        </footer>
    </main>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const bannerFile = document.getElementById('bannerFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const previewImage = document.getElementById('previewImage');
        const previewPlaceholder = document.getElementById('previewPlaceholder');
        const bannerTitleInput = document.getElementById('bannerTitle');

        // Click Area triggers Input
        uploadArea.addEventListener('click', (e) => {
            if(e.target !== bannerFile) bannerFile.click();
        });

        // Handle File Change
        bannerFile.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const file = this.files[0];
                
                // Show info
                fileInfo.style.display = 'flex';
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                
                // Show Preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                    previewPlaceholder.style.display = 'none';
                }
                reader.readAsDataURL(file);

                // Auto title (optional)
                if(bannerTitleInput.value === '') {
                    bannerTitleInput.value = file.name.split('.')[0];
                }
            }
        });
    </script>
</body>
</html>