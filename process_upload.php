<?php
session_start();
require_once(__DIR__ . "/includes/db_connect.php"); // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file'])) { // ตรวจสอบว่าไฟล์ถูกอัปโหลดมาแล้วหรือไม่
        $file = $_FILES['csv_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) { // ตรวจสอบว่าไม่มีข้อผิดพลาดในการอัปโหลด
            $uploadDir = 'uploads/'; // กำหนดไดเรกทอรีสำหรับจัดเก็บไฟล์
            $filePath = $uploadDir . basename($file['name']); // กำหนดเส้นทางของไฟล์ที่อัปโหลด

            // ย้ายไฟล์จากตำแหน่งชั่วคราวไปยังที่เก็บไฟล์จริง
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // กรณีอัปโหลดไฟล์สำเร็จ
                $_SESSION['upload_status'] = [
                    'type' => 'alert-success', 
                    'message' => 'อัปโหลดไฟล์สําเร็จแล้ว!',
                ];

                // ฟังก์ชันสำหรับบันทึกประวัติการอัปโหลด
                function log_action($conn, $file_name, $action) {
                    $historyStmt = $conn->prepare(
                        "INSERT INTO upload_logs (item, IdSlab, grade, Thick, Width, Length, Weight, Location, lot, Heatsup, HeatLpn, action)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );

                    $historyStmt->execute([
                        $file_name, 'IdSlab', 'grade', 'Thick', 'Width', 'Length', 'Weight', 'Location', 'lot', 'Heatsup', 'HeatLpn', $action
                    ]);
                }

                // บันทึกประวัติการอัปโหลด
                log_action($conn, $file['name'], 'UPLOAD');
                
            } else {
                // กรณีเกิดข้อผิดพลาดในการย้ายไฟล์
                $_SESSION['upload_status'] = [
                    'type' => 'alert-danger', 
                    'message' => 'ย้ายไฟล์ที่อัปโหลดไม่สําเร็จ.',
                ];
            }
        } else {
            // กรณีเกิดข้อผิดพลาดในการอัปโหลด
            $_SESSION['upload_status'] = [
                'type' => 'alert-danger', 
                'message' => 'เกิดข้อผิดพลาดระหว่างการอัปโหลดไฟล์.',
            ];
        }
    } else {
        // กรณีไม่มีไฟล์ถูกเลือก
        $_SESSION['upload_status'] = [
            'type' => 'alert-warning', 
            'message' => 'ไม่มีการเลือกไฟล์สําหรับการอัปโหลด.',
        ];
    }
}

// เปลี่ยนเส้นทางไปที่หน้า upload.php หลังจากการอัปโหลดเสร็จสิ้น
header('Location: upload.php');
exit;
?>

