<?php
session_start();
require_once 'config.php';

// Simple login using the `users` table and password_verify
// Toggle for development only (do NOT enable on public sites)
$debug = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password!';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT user_id, username, password, fullname, role FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password!';
                if ($debug) {
                    $debug_msg = 'Lookup result: ' . ($user ? 'user found' : 'no user');
                }
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
            if ($debug) {
                $debug_msg = 'DB error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SchoolSync Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
  <!-- Left Panel -->
  <div class="left-panel">
    <i class="fas fa-school fa-4x mb-3"></i>
    <h1>SchoolSync</h1>
    <p>Welcome to your integrated school management mini system. Manage records, payments, and grades all in one place.</p>
  </div>

  <!-- Right Panel -->
  <div class="right-panel">
    <div class="login-box">
      <h4>Login</h4>
      <p>Access your dashboard</p>

      <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
      <?php if (!empty($debug_msg)) echo "<div class='alert alert-info'>" . $debug_msg . "</div>"; ?>

      <form method="POST">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <p class="text-muted mt-3" style="font-size: 0.9em; text-align: center;">Need help? Contact your administrator</p>
    </div>
  </div>
</body>
</html>
