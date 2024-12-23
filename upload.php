<?php
session_start();
require_once(__DIR__ . "/includes/db_connect.php");

// สร้าง CSRF Token หากยังไม่มี
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ตรวจสอบว่ามีการอัปโหลดไฟล์
        if (isset($_FILES['uploads']) && !empty($_FILES['uploads']['name'])) {
            $fileTmpName = $_FILES['uploads']['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES['uploads']['name'], PATHINFO_EXTENSION));

             // ตรวจสอบประเภทไฟล์
            if ($fileExt !== 'csv') {
                throw new Exception('Please upload a valid CSV file.');
            }

            // ตรวจสอบขนาดไฟล์ (ไม่เกิน 10MB) //
            if ($_FILES['uploads']['size'] > 10 * 1024 * 1024) {
                throw new Exception('File size exceeds 10MB limit.');
            }

            // สร้างชื่อไฟล์ใหม่
            $customFileName = isset($_POST['fileName']) ? trim($_POST['fileName']) : 'default_filename';
            $newFileName = $customFileName . '.' . $fileExt;
            $uploadDir = 'C:/laragon/www/uploads/';

            // ตรวจสอบและสร้างไดเรกทอรีหากไม่มี
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // ตรวจสอบการเขียนทับ
            $newFilePath = $uploadDir . $newFileName;
            if (file_exists($newFilePath) && !isset($_POST['overwrite'])) {
                throw new Exception('A file with the same name already exists. You can choose to overwrite it.');
            }

            // ย้ายไฟล์ที่อัปโหลด
            if (!move_uploaded_file($fileTmpName, $newFilePath)) {
                throw new Exception('Failed to upload the file.');
            }

            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'File uploaded successfully.';
            header('Location: upload.php');
            exit;
        } else {
            throw new Exception('No file uploaded.');
        }
    } catch (Exception $e) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = $e->getMessage();
        header('Location: upload.php');
        exit;
    }
}

// ดึงรายการไฟล์ที่อัปโหลด
$uploadedFiles = [];
$uploadDir = 'C:/laragon/www/uploads/';
if (is_dir($uploadDir)) {
    foreach (array_diff(scandir($uploadDir), ['.', '..']) as $file) {
        $filePath = $uploadDir . $file;
        $uploadedFiles[] = [
            'name' => $file,
            'date' => filemtime($filePath),
            'size' => filesize($filePath),
            'path' => $filePath
        ];
    }
}
// ตรวจสอบค่า 'sort' ที่ส่งมา
$sortOption = $_GET['sort'] ?? 'name';

// ฟังก์ชันเรียงลำดับไฟล์
usort($uploadedFiles, function ($a, $b) use ($sortOption) {
    switch ($sortOption) {
        case 'date':
            return $a['date'] <=> $b['date'];
        case 'size':
            return $a['size'] <=> $b['size'];
        case 'name':
        default:
            return strcmp($a['name'], $b['name']);
    }
});

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/Professional Stylesheet.css" rel="stylesheet">
</head>
<body>
<div class="container-lg my-3">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container-lg">
            <a class="navbar-brand d-flex align-items-center" href="upload.php">
                <img src="assets/css/image/gtul53k8.svg" alt="Logo" width="100" height="100" class="me-2">
                <span class="fw-bold custom-text">Form for uploading CSV files</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-user fw-bold">
                            <i class="bi bi-person-circle" style="-webkit-text-stroke: 0.7px"></i>
                            <?= htmlspecialchars($_SESSION['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-logout" href="login.php">
                            <i class="bi bi-box-arrow-right" style="-webkit-text-stroke: 0.7px"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<!-- Upload Section -->
<div class="container my-3">
    <?php if (isset($_SESSION['status']) && isset($_SESSION['message'])): ?>
        <div class="alert <?= $_SESSION['status'] === 'success' ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show mt-4" role="alert">
            <?= htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['status'], $_SESSION['message']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Upload Your File</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="fileUpload" class="form-label">Select File <span class="text-muted">(CSV only)</span></label>
                    <input type="file" class="form-control" name="uploads" id="fileUpload" accept=".csv" required>
                </div>
                <div class="mb-3">
                    <label for="fileName" class="form-label">Custom File Name</label>
                    <input type="text" class="form-control" name="fileName" id="fileName" placeholder="Enter file name" required>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="overwrite" id="overwrite">
                    <label class="form-check-label" for="overwrite">Overwrite Existing Files</label>
                </div>
                <button type="submit" class="btn btn-custom w-100 mt-3">
                    <i class="bi bi-upload" style="-webkit-text-stroke: 0.7px"></i> Upload
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Uploaded Files Section -->
<div class="container my-5">
        <div class="card shadow-sm" style="margin-top: 20px;">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Uploaded Files</h3>

                <!-- Sort Dropdown -->
                <div class="form-wrapper">
                    <form method="get">
                        <label for="sort">Sort by:</label>
                        <!-- Custom Select -->
                        <div class="custom-select" id="custom-select">
                            <div class="select-selected">Name</div>
                            <div class="select-items select-hide">
                                <div data-value="date">Date</div>
                                <div data-value="name">Name</div>
                                <div data-value="size">Size</div>
                            </div>
                        </div>
                    </form>
                </div>

                <script>
                    // JavaScript for Custom Dropdown
                    document.addEventListener("DOMContentLoaded", function () {
                        const selected = document.querySelector(".select-selected");
                        const items = document.querySelector(".select-items");
                        const options = items.querySelectorAll("div");

                        // แสดงผลการเลือกจาก URL (ค่าของ 'sort')
                        const urlParams = new URLSearchParams(window.location.search);
                        const sortValue = urlParams.get('sort') || 'name';  // กำหนด default เป็น 'name' ถ้าไม่มีค่า sort ใน URL

                        // ตั้งค่าชื่อที่เลือกใน dropdown
                        selected.textContent = optionsArray[sortValue] || 'Name'; // ใช้ map ของตัวเลือก

                        // แสดง/ซ่อน dropdown เมื่อคลิก
                        selected.addEventListener("click", function () {
                            items.classList.toggle("select-hide");
                        });

                        options.forEach(option => {
                            option.addEventListener("click", function () {
                                selected.textContent = this.textContent; // เปลี่ยนข้อความใน select
                                const value = this.getAttribute("data-value");

                                // ส่งค่าไปยัง URL
                                window.location.href = "?sort=" + value; // ส่งค่าไปยัง URL เพื่อรีเฟรช
                            });
                        });

                        // ปิด dropdown เมื่อคลิกข้างนอก
                        document.addEventListener("click", function (e) {
                            if (!e.target.closest("#custom-select")) {
                                items.classList.add("select-hide");
                            }
                        });
                    });
                </script>

                <script>
                    // Map for selected values
                    const optionsArray = {
                        'date': 'Date',
                        'name': 'Name',
                        'size': 'Size'
                    };
                </script>

                <!-- Files Table -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>No</th>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($uploadedFiles)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No files uploaded yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($uploadedFiles as $index => $file): ?>
                                        <tr>
                                            <td class="file-data"><?= $index + 1 ?></td>
                                            <td class="file-data"><?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="file-data"><?= number_format($file['size'] / 1024, 2) ?> KB</td>
                                            <td class="file-data"><?= date('d-m-Y H:i:s', $file['date']) ?></td>
                                            <td>
                                                <!-- ปุ่ม Preview -->
                                                <a href="preview.php?file=<?= urlencode($file['name']) ?>" class="btn btn-preview btn-sm">
                                                    <i class="bi-file-earmark-text"style="-webkit-text-stroke: 0.7px"></i> Preview
                                                </a>
                                                <!-- ปุ่ม Delete -->
                                                <a href="delete-file.php?delete=<?= urlencode($file['name']) ?>" class="btn btn-delete btn-sm">
                                                    <i class="bi bi-trash fw-bold"style="-webkit-text-stroke: 0.7px"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>   
                </div>
            </div>
        </div>
    </div>
</div>
        
<!-- Footer -->
<div class="container-lg my-1">
    <footer class="footer mt-5 py-4 bg-dark text-light">                
        <div class="container">              
            <div class="row"              >
                 <div class="col-md-6 text-center text-md-start">
                    <h5 class="fw-bold text-dark-custom">File Manager</h5>
                        <p class="text-dark-custom small mb-0">Manage your files efficiently and securely.</p>
                        <p class="text-dark-custom small mb-0">&copy; 2024 File Manager. All Rights Reserved.</p>
                    </div>   
                </div>
            </div>
        </div>
    </footer>                                  
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
