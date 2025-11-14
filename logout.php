<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SchoolSync Logout</title>
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
    <p>You have been successfully logged out. Thank you for using our system.</p>
  </div>

  <!-- Right Panel -->
  <div class="right-panel">
    <div class="logout-box">
      <h4>Logged Out</h4>
      <p>You have been logged out successfully.</p>
      <a href="login.php" class="btn btn-primary w-100">Login Again</a>
    </div>
  </div>
</body>
</html>
