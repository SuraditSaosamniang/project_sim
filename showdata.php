<?php
session_start();
require_once(__DIR__ . "/includes/db_connect.php");

if (!isset($conn)) {
    die("Connection to the database failed.");
}

try {
    // ดึงข้อมูลจากฐานข้อมูลและเก็บไว้ในตัวแปร $tableData
    $stmt = $conn->prepare("SELECT * FROM slab1");
    $stmt->execute();
    $tableData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ตรวจสอบว่าข้อมูลที่ดึงมามีอยู่หรือไม่
    if (empty($tableData)) {
        echo "ไม่พบข้อมูลในฐานข้อมูล.";
    } else {
        // เตรียมตัวแปรสำหรับการแสดงผลใน HTML
        $tableHeaders = ['Item', 'IdSlab', 'Grade', 'Thick', 'Width', 'Length', 'Weight', 'Location', 'Lot', 'Heatsup', 'HeatLpn'];
        $currentData = $tableData; // กำหนดให้ใช้ $tableData แทน $data
    }
} catch (Exception $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

// กำหนดจำนวนแถวต่อหน้า
$rowsPerPage = 25;

// คำนวณจำนวนหน้า
$totalRows = count($tableData);
$totalPages = ceil($totalRows / $rowsPerPage);

// หน้าปัจจุบัน
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); // ตรวจสอบหน้าให้ไม่เกินจำนวนหน้า

// คำนวณแถวที่เริ่มต้นและข้อมูลที่แสดงในหน้าปัจจุบัน
$startRow = ($currentPage - 1) * $rowsPerPage;
$currentData = array_slice($tableData, $startRow, $rowsPerPage);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Available Information - Slab Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/Professional Stylesheet.css" rel="stylesheet">
</head>
<body>
<div class="container my-5 mb-3">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container-lg">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/css/image/gtul53k8.svg" alt="Logo" width="100" height="100" class="me-2">
                <span class="fw-bold custom-text">Form for uploading CSV files</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-muted fw-bold">
                            <i class="bi bi-person-circle"></i> 
                            <?= htmlspecialchars($_SESSION['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </li>
                    <!-- Dropdown Menu -->
                    <li class="nav-item dropdown" id="dropdownNav">
                            <a class="nav-link dropdown-toggle text-dark fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-menu-button-wide me-1"></i> Menu
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" >
                                <li><a class="dropdown-item" href="showdata.php"><i class="bi bi-table me-2"></i> Show Data</a></li>
                                <li><a class="dropdown-item" href="upload.php"><i class="bi bi-house-door me-2"></i> Home</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-logout" href="login.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
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
    </script>
    
    <div class="container-lg">
        <!-- Table Section -->
        <?php if (!empty($tableHeaders) && !empty($currentData)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-gradient text-black text-center p-4">
                    <h2 class="card-title mb-2">Show Available Information Slab Table</h2>
                    <p class="card-subtitle text-black">Display the data available in the Slab table.</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <?php foreach ($tableHeaders as $header): ?>
                                        <th><?= htmlspecialchars($header) ?></th>
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
                        Showing <?= $startRow + 1 ?> to <?= min($startRow + $rowsPerPage, $totalRows) ?> of <?= $totalRows ?> entries
                    </div>
                </div>
            </div>

            <!-- Pagination -->    
            <nav class="mt-4">
                <div class="row">
                    <div class="text-center">
                        <div class="dataTables_paginate paging_simple_numbers" id="Size_paginate">
                        <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="paginate_button page-item previous">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                                            <i class="bi bi-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="paginate_button page-item previous disabled">
                                        <a class="page-link" href="#">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="paginate_button page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="paginate_button page-item next">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                                            Next <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="paginate_button page-item next disabled">
                                        <a class="page-link" href="#">Next <i class="bi bi-chevron-right"></i></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Action Buttons -->
            <div class="mt-4 text-center">
                <a href="upload.php" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Upload
                </a>
            </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">No data available to preview.</div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="container-lg my-1">
            <footer class="footer mt-5 py-4 bg-dark text-light">
                <div class="container">
                    <div class="row">
                    <!-- Footer Branding -->
                    <div class="row">
                        <div class="col-md-6 text-center text-md-start">
                            <h5 class="fw-bold text-dark-custom">File Manager</h5>
                            <p class="text-dark-custom small mb-0">Manage your files efficiently and securely.</p>
                            <p class="text-dark-custom small mb-0">&copy; 2024 File Manager. All Rights Reserved.</p>
                        </div>
                    </div>
                </div>
            </footer>                                  
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

