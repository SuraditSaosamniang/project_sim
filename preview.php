<?php
session_start();
require_once(__DIR__ . "/includes/db_connect.php");

// ตรวจสอบว่าไฟล์ CSV ที่กำหนดมีหรือไม่
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("ไม่มีการระบุไฟล์.");
}

$fileName = basename(urldecode($_GET['file']));
$uploadDir = 'C:/laragon/www/uploads/';
$filePath = $uploadDir . $fileName;

// ตรวจสอบว่าไฟล์มีอยู่จริงหรือไม่
if (!file_exists($filePath)) {
    die("ไม่พบไฟล์.");
}

// ตรวจสอบชนิดของไฟล์เป็น CSV เท่านั้น
$allowedExtensions = ['csv'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    die("ประเภทไฟล์ไม่ถูกต้อง อนุญาตเฉพาะไฟล์ CSV เท่านั้น.");
}

// โหลดข้อมูลจากไฟล์ CSV
$tableHeaders = [];
$tableData = [];
if (($handle = fopen($filePath, "r")) !== false) {
    $tableHeaders = fgetcsv($handle, 1000, ",", '"', "\\");
    while (($row = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
        $tableData[] = $row;
    }
    fclose($handle);
}


// กำหนดจำนวนแถวต่อหน้า
$rowsPerPage = 25;

// คำนวณจำนวนหน้า
$totalRows = count($tableData);
$totalPages = ceil($totalRows / $rowsPerPage);

// หน้าปัจจุบัน
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); // ตรวจสอบหน้าให้ไม่เกินจำนวนหน้า

// คำนวณแถวที่เริ่มต้นและข้อมูลที่แสดงในหน้าปัจจุบัน
$startRow = ($currentPage - 1) * $rowsPerPage;
$currentData = array_slice($tableData, $startRow, $rowsPerPage);

// สร้าง CSRF Token ถ้าไม่มี
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// จัดเรียงข้อมูล
$sort = $_GET['sort'] ?? '';
if ($sort === 'name') {
    usort($currentData, function ($a, $b) {
        return strcmp($a[0], $b[0]);
    });
} elseif ($sort === 'date') {
    usort($currentData, function ($a, $b) {
        return strtotime($a[1]) - strtotime($b[1]);
    });
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>แสดงข้อมูลในไฟล์</title>
    <link rel="icon" type="image/x-icon" href="assets/css/image/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/Professional Stylesheet.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container-lg my-3 mb-3">
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
            <div class="container-lg">
                <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/css/image/gtul53k8.png" sizes="64x64"alt="Logo" class="me-2" style="width:40px; height:40px;">
                    <span class="fw-bold custom-text" style="font-size:1.5rem;">ระบบอัปโหลดไฟล์ข้อมูล Slab</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <span class="nav-link text-user fw-bold">
                                <i class="bi bi-person-circle" style="-webkit-text-stroke: 0.7px"></i>
                                <?= htmlspecialchars($_SESSION['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </li>

                        <!-- Dropdown Menu -->
                        <li class="nav-item dropdown" id="dropdownNav">
                            <a class="nav-link dropdown-toggle text-dark fw-bold" href="#" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-menu-button-wide me-1" style="-webkit-text-stroke: 0.7px"></i> Menu
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="showdata.php"><i class="bi bi-table me-2"
                                            style="-webkit-text-stroke: 0.7px"></i> Show Data</a></li>
                                <li><a class="dropdown-item" href="upload.php"><i class="bi bi-house-door me-2"
                                            style="-webkit-text-stroke: 0.7px"></i>Home</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-logout" href="login.php">
                                <i class="bi bi-box-arrow-right" style="-webkit-text-stroke: 0.7px"></i> ออกจากระบบ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <script>
        // เลือก Dropdown Menu
        const dropdownNav = document.getElementById('dropdownNav');
        const dropdownMenu = dropdownNav.querySelector('.dropdown-menu');
        const dropdownToggle = dropdownNav.querySelector('.dropdown-toggle');

        // ซ่อน Dropdown เมื่อเมาส์หลุดออก
        dropdownMenu.addEventListener('mouseleave', () => {
            const dropdown = bootstrap.Dropdown.getInstance(dropdownToggle); // ใช้ Bootstrap API
            dropdown.hide(); // ซ่อน Dropdown
        });

        // ปรับตำแหน่ง Dropdown ให้ตรงกับปุ่ม Menu
        dropdownToggle.addEventListener('click', () => {
            const rect = dropdownToggle.getBoundingClientRect();
            dropdownMenu.style.left = `${rect.left}px`;
            dropdownMenu.style.top = `${rect.bottom}px`;
        });
    </script>

    <div class="container-lg my-3">
        <!-- Table Section -->
        <?php if (!empty($tableHeaders) && !empty($currentData)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-gradient text-black text-center p-4">
                    <h2 class="card-title mb-2">แสดงข้อมูลของไฟล์</h2>
                    <p class="card-subtitle text-black mb-2"><?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="card-subtitle text-black">ตรวจสอบข้อมูลในไฟล์ก่อนอัปโหลด.</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <?php foreach ($tableHeaders as $header): ?>
                                        <th class="responsive-header"><?= htmlspecialchars($header) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currentData as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $cell): ?>
                                            <td><?= htmlspecialchars($cell) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Display the total number of records after the table -->
                    <div class="dataTables_info text-end" id="Size_info" role="status" aria-live="polite">
                        แสดง <?= $startRow + 1 ?> ถึง <?= min($startRow + $rowsPerPage, $totalRows) ?> จาก
                        <?= $totalRows ?> แถว
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <nav class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="dataTables_paginate paging_simple_numbers" id="Size_paginate">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="paginate_button page-item previous">
                                        <a class="page-link"
                                            href="?file=<?= urlencode($fileName) ?>&page=<?= $currentPage - 1 ?>"
                                            style="font-family: 'Kanit', sans-serif;">
                                            <i class="bi bi-chevron-left" style="-webkit-text-stroke: 0.7px"></i> ก่อนหน้า
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="paginate_button page-item previous disabled">
                                        <a class="page-link" href="#" style="font-family: 'Kanit', sans-serif;">ก่อนหน้า</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="paginate_button page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?file=<?= urlencode($fileName) ?>&page=<?= $i ?>"
                                            style="font-family: 'Kanit', sans-serif;">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="paginate_button page-item next">
                                        <a class="page-link"
                                            href="?file=<?= urlencode($fileName) ?>&page=<?= $currentPage + 1 ?>"
                                            style="font-family: 'Kanit', sans-serif;">
                                            ถัดไป <i class="bi bi-chevron-right" style="-webkit-text-stroke: 0.7px;"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="paginate_button page-item next disabled">
                                        <a class="page-link" href="#" style="font-family: 'Kanit', sans-serif;">
                                            ถัดไป <i class="bi bi-chevron-right" style="-webkit-text-stroke: 0.7px;"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Action Buttons -->
            <div class="mt-4 text-center">
                <!-- Insert Data -->
                <form action="insert.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="data" value="<?= base64_encode(serialize($tableData)) ?>">
                    <input type="hidden" name="filename" value="<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit" class="btn btn-insert">
                        <i class="bi bi-database-add me-1" style="-webkit-text-stroke: 0.2px; font-family: 'Kanit', sans-serif;"></i> Insert Data
                    </button>
                </form>
                <div class="button-group mt-3">
                    <a href="?file=<?= urlencode($fileName) ?>" class="btn btn-refresh">
                        <i class="bi bi-arrow-clockwise" style="-webkit-text-stroke: 0.7px"></i> Refresh Preview
                    </a>
                    <a href="upload.php" class="btn btn-back">
                        <i class="bi bi-arrow-left" style="-webkit-text-stroke: 0.7px"></i> Back to Upload
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning alert-dismissible fade show mt-4" role="alert">
                No data available to preview.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="container-lg my-1">
        <footer class="footer mt-5 py-4 bg-dark text-light">
            <div class="container">
                <div class="row">
                    <!-- Footer Branding -->
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <h5 class="fw-bold text-dark-custom" style="font-family: 'Kanit', sans-serif;;">Slab File Uploader</h5>
                        <p class="text-dark-custom small mb-0">ระบบจัดการและอัปโหลดไฟล์ข้อมูล Slab อย่างมีประสิทธิภาพและปลอดภัย.</p>
                        <p class="text-dark-custom small mb-0">&copy; 2024 Slab File Uploader. สงวนลิขสิทธิ์.</p>                         
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>