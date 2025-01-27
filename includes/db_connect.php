<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$host = 'localhost';    
$dbname = 'lpnpm_sim';  
$username = 'lpnpm_aing';     
$password = ',:4_kn.LTZtwBE';         

// สร้างการเชื่อมต่อ
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connection Successful!!"; // ใช้สำหรับตรวจสอบการเชื่อมต่อ
} catch (PDOException $e) {
    die("Unable to connect to the database: " . $e->getMessage());
}

// ตั้งค่าตัวแปรการเชื่อมต่อที่ใช้ในโค้ดหลัก
$con = $conn;
?>

