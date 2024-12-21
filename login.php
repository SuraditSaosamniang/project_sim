<?php
session_start();
require_once("includes/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashed_password = md5($password); // แฮชรหัสผ่านก่อนเก็บในฐานข้อมูล

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // ตรวจสอบว่ามี username ในฐานข้อมูลหรือไม่
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // หากผู้ใช้มีอยู่แล้วในฐานข้อมูล ให้ตรวจสอบรหัสผ่าน
                if ($user['password'] === $hashed_password) {
                    $_SESSION['username'] = $user['username'];
                    header("Location: upload.php");
                    exit;
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                // หากไม่มีข้อมูลผู้ใช้ในฐานข้อมูล ให้เพิ่มใหม่
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $insert_stmt->execute([$username, $hashed_password]);

                $_SESSION['username'] = $username; // เก็บ username ใน Session
                header("Location: upload.php");
                exit;
            }
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- Add Bootstrap Icons -->
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 style="-webkit-text-stroke: 0.7px">Login</h2>
        <p style="-webkit-text-stroke: 0.7px">System for uploading Slab data files</p>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
        
        <!-- ฟอร์ม Login -->
        <form action="login.php" method="POST">
            <div class="mb-1 mt-1">
                <label for="username" class="form-label" style="-webkit-text-stroke: 0.7px">User Name:</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"style="-webkit-text-stroke: 0.5px"></i></span>
                    <input type="text" class="form-control" id="username" placeholder="Enter Username" name="username" required>
                </div>
            </div>
            <div class="mb-1">
                <label for="password" class="form-label"style="-webkit-text-stroke: 0.7px">Password:</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"style="-webkit-text-stroke: 0.5px"></i></span>
                    <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember"style="-webkit-text-stroke: 0.5px">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="-webkit-text-stroke: 0.5px">Submit</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


