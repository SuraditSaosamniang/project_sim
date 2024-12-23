<?php
session_start();
require_once(__DIR__ . "/includes/db_connect.php");

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Invalid CSRF token.';
        header("Location: upload.php");
        exit;
    }

    // ตรวจสอบข้อมูลที่ส่งมา
    if (!isset($_POST['data']) || empty($_POST['data'])) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'No data received for insertion.';
        header("Location: upload.php");
        exit;
    }

    $filename = isset($_POST['filename']) ? $_POST['filename'] : 'unknown_file';

    // ดึงชื่อผู้ใช้จาก session
    $username = $_SESSION['username']; // ใช้ session เพื่อดึงชื่อผู้ใช้

    // ถอดรหัสข้อมูลที่ส่งมา
    $tableData = unserialize(base64_decode($_POST['data']));
    $totalRecords = count($tableData);
    $ipAddress = $_SERVER['REMOTE_ADDR'];  // รับ IP Address ของผู้ใช้งาน

    try {
        $conn->beginTransaction();

        // ลบข้อมูลเก่าออกจากตาราง slab1
        $conn->exec("DELETE FROM slab1");

        // เตรียม statement สำหรับการแทรกข้อมูลลงใน slab1
        $insertStmt = $conn->prepare(
            "INSERT INTO slab1 (item, IdSlab, grade, Thick, Width, Length, Weight, Location, lot, Heatsup, HeatLpn)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        // แทรกข้อมูลลงในตาราง slab1
        foreach ($tableData as $row) {
            if (count($row) < 11) {
                continue; // ข้ามแถวที่ข้อมูลไม่ครบ
            }

            $insertStmt->execute([
                $row[0], $row[1], $row[2], $row[3], $row[4],
                $row[5], $row[6], $row[7], $row[8], $row[9], $row[10]
            ]);
        }

        // บันทึกข้อมูลการอัปโหลดลงใน upload_logs
        $insertLogStmt = $conn->prepare(
            "INSERT INTO upload_logs (User, Filename, Date, Records, IP_Address ) 
             VALUES (?, ?, NOW(), ?, ?)"
        );
        $insertLogStmt->execute([
            $username,   // ชื่อผู้ใช้ที่ทำการอัปโหลด
            $filename,   // ชื่อไฟล์ที่อัปโหลด
            $totalRecords,  // จำนวนข้อมูลที่อัปโหลด
            $ipAddress  // IP Address ของผู้ที่อัปโหลด
        ]);

        // ยืนยันการทำธุรกรรม
        $conn->commit();

        $_SESSION['status'] = 'success';
        $_SESSION['message'] = "Data successfully inserted into slab1. Total records: $totalRecords.";
        header("Location: upload.php");
        exit;

    } catch (Exception $e) {
        // Rollback ในกรณีเกิดข้อผิดพลาด
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        $_SESSION['status'] = 'error';
        $_SESSION['message'] = "Error while inserting into slab1: " . $e->getMessage();
        header("Location: upload.php");
        exit;
    }
} else {
    echo "Invalid request method.";
}
?>
