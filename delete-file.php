<?php
session_start();

// Ensure the 'uploads' directory is correctly set
$uploadDir = 'C:/laragon/www/uploads/';

// Check if the delete parameter is set and valid
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']); // Prevent directory traversal
    $filePath = $uploadDir . $fileToDelete;

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'ไฟล์ถูกลบสําเร็จ.';
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'ลบไฟล์ไม่สําเร็จ.';
        }
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'ไม่พบไฟล์.';
    }
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'คําขอไม่ถูกต้อง.';
}

// Redirect back to the upload page
header('Location: upload.php');
exit;
