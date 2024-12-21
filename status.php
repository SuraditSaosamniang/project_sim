<?php
session_start();
require_once(__DIR__ . "/includes/db_connect.php");

try {
    // ดึงข้อมูลจากฐานข้อมูลพร้อมสถานะที่กำหนด
    $sql = "SELECT IdSlab, 
                   CASE 
                       WHEN status = 'Available' THEN 'Available'
                       WHEN status = 'Reserved' THEN 'Reserved'
                       ELSE 'Unknown'
                   END AS status
            FROM slab1";
    
    $stmt = $conn->query($sql);
    $slabStatuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn = null;
}

// กำหนดจำนวนแถวต่อหน้า
$rowsPerPage = 25;

// คำนวณจำนวนหน้า
$totalRows = count($slabStatuses);
$totalPages = ceil($totalRows / $rowsPerPage);

// หน้าปัจจุบัน
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); // ตรวจสอบหน้าให้ไม่เกินจำนวนหน้า

// คำนวณแถวที่เริ่มต้นและข้อมูลที่แสดงในหน้าปัจจุบัน
$startRow = ($currentPage - 1) * $rowsPerPage;
$currentData = array_slice($slabStatuses, $startRow, $rowsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slab Status Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/Professional Stylesheet.css" rel="stylesheet">
    <style>
        .table-container {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table-header {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        .table {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .pagination {
            margin-top: 20px;
        }
        .page-link {
            color: #007bff;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            transition: background-color 0.3s, transform 0.2s;
        }
        .page-link:hover {
            background-color: #e9ecef;
            transform: scale(1.05);
        }
        .page-item.active .page-link {
            background-color: #007bff;
            color: white;
        }
        .table tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }
        .text-success {
            font-weight: bold;
            color: #28a745;
        }
        .text-danger {
            font-weight: bold;
            color: #dc3545;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .card-body {
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container table-container">
        <h2 class="text-center mb-4">Slab ID Status Table</h2>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-header">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">IdSlab</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($currentData)) : ?>
                            <?php $counter = 1; // ตัวแปรนับลำดับ ?>
                            <?php foreach ($currentData as $row) : ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td> <!-- แสดงหมายเลขลำดับ -->
                                    <td><?php echo htmlspecialchars($row['IdSlab']); ?></td>
                                    <td class="<?php echo ($row['status'] === 'Available') ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="text-center">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo htmlspecialchars($currentPage - 1); ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?php echo htmlspecialchars($i); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo htmlspecialchars($currentPage + 1); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



