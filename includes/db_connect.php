<?php

$server = "lpnpm.co.th";

// ตรวจสอบว่ารันอยู่บนเซิร์ฟเวอร์หรือเครื่อง local
if ($_SERVER['HTTP_HOST'] === $server) {
    // ค่าที่ใช้บนเซิร์ฟเวอร์จริง
    $host = 'lpnpm.co.th';
    $dbname = 'lpnpm_sim';
    $username = 'lpnpm_aing';
    $password = ',:4_kn.LTZtwBE';
} else {
    // ค่าที่ใช้บน localhost
    $host = 'localhost';
    $dbname = 'lpnpm_sim';
    $username = 'lpnpm_aing'; 
    $password = ',:4_kn.LTZtwBE'; 
}

// สร้างการเชื่อมต่อ
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connection Successful!!"; // ตรวจสอบการเชื่อมต่อ
} catch (PDOException $e) {
    die("Unable to connect to the database: " . $e->getMessage());
}

// ตั้งค่าตัวแปรการเชื่อมต่อที่ใช้ในโค้ดหลัก
$con = $conn;
?>


