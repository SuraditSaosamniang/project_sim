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
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
        
        <!-- ฟอร์ม Login -->
        <form action="login.php" method="POST">
            <div class="mb-3 mt-3">
                <label for="username" class="form-label">User Name:</label>
                <input type="text" class="form-control" id="username" placeholder="Enter Username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
            </div>
            <div class="form-check mb-3">
                <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" name="remember"> Remember me
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


