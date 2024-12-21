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
            $_SESSION['message'] = 'File deleted successfully.';
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Failed to delete the file.';
        }
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'File not found.';
    }
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Invalid request.';
}

// Redirect back to the upload page
header('Location: upload.php');
exit;
