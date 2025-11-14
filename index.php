<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Load dashboard statistics from database
try {
  $stmt = $pdo->query('SELECT COUNT(*) as count FROM students');
  $total_students = $stmt->fetch()['count'];

  $stmt = $pdo->query('SELECT SUM(amount_paid) as total FROM payments');
  $total_payments = $stmt->fetch()['total'] ?: 0;

  // Count teachers from users table where role = 'teacher'
  $stmt = $pdo->query('SELECT COUNT(*) as count FROM users WHERE role = "teacher"');
  $total_teachers = $stmt->fetch()['count'];
} catch (Exception $e) {
  $total_students = $total_payments = $total_teachers = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SchoolSync Mini System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 sidebar">
        <h4>🏫 SchoolSync</h4>
        <a href="#" class="active"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a href="pages/registrar.php"><i class="fas fa-user-graduate me-2"></i>Registrar</a>
        <a href="pages/cashier.php"><i class="fas fa-cash-register me-2"></i>Cashier</a>
        <a href="pages/teacher.php"><i class="fas fa-book me-2"></i>Teacher</a>
        <a href="pages/admin.php"><i class="fas fa-chart-bar me-2"></i>Admin Reports</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="pages/user_management.php"><i class="fas fa-users-cog me-2"></i>User Management</a>
        <?php endif; ?>
        <hr>
        <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
      </div>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10 main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="text-primary fw-bold"><i class="fas fa-home me-2"></i>Dashboard Overview</h3>
          <div class="d-flex align-items-center">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                 alt="User Avatar" width="45" class="rounded-circle me-2">
            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?> (<?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>)</span>
          </div>
        </div>

        <!-- Simple Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="card">
              <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
              <h6>Total Students</h6>
              <p class="h4 text-primary"><?php echo $total_students; ?></p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="stat-icon text-success"><i class="fas fa-hand-holding-usd"></i></div>
              <h6>Total Payments Collected</h6>
              <p class="h4 text-success">₱<?php echo number_format($total_payments, 2); ?></p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="stat-icon text-warning"><i class="fas fa-chalkboard-teacher"></i></div>
              <h6>Teachers</h6>
              <p class="h4 text-warning"><?php echo $total_teachers; ?></p>
            </div>
          </div>
        </div>

        <!-- Simple Modules Section -->
        <div class="row g-3">
          <div class="col-md-4">
            <div class="card">
              <div class="stat-icon text-primary"><i class="fas fa-clipboard-list"></i></div>
              <h6>Registrar Module</h6>
              <p>Manage student enrollment and records.</p>
              <a href="pages/registrar.php" class="btn btn-primary btn-sm">Open</a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="stat-icon text-success"><i class="fas fa-cash-register"></i></div>
              <h6>Cashier Module</h6>
              <p>Handle tuition payments and receipts.</p>
              <a href="pages/cashier.php" class="btn btn-success btn-sm">Open</a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="stat-icon text-warning"><i class="fas fa-book-open"></i></div>
              <h6>Teacher Module</h6>
              <p>Manage attendance and grades.</p>
              <a href="pages/teacher.php" class="btn btn-warning btn-sm text-white">Open</a>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="footer mt-4">
          <p class="text-center">© 2025 SchoolSync | Mini School System</p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
