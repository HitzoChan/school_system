<?php
require_once '../config.php';
requireLogin();

// Load data from database
$students = [];
$payments = [];
$grades = [];

// Calculate totals from database
try {
  $stmt = $pdo->query('SELECT COUNT(*) as count FROM students');
  $total_students = $stmt->fetch()['count'];

  $stmt = $pdo->query('SELECT COUNT(*) as count FROM payments');
  $total_payments = $stmt->fetch()['count'];

  $stmt = $pdo->query('SELECT SUM(amount_paid) as total FROM payments');
  $total_amount = $stmt->fetch()['total'] ?: 0;

  $stmt = $pdo->query('SELECT COUNT(*) as count FROM grades');
  $total_grades = $stmt->fetch()['count'];
} catch (Exception $e) {
  $total_students = $total_payments = $total_amount = $total_grades = 0;
  $error = 'Error loading summary data: ' . $e->getMessage();
}

// Load detailed data for tables
try {
  $stmt = $pdo->query('SELECT * FROM students ORDER BY date_enrolled DESC');
  $students = $stmt->fetchAll();

  $stmt = $pdo->query('
    SELECT p.*, s.fullname as student_name
    FROM payments p
    LEFT JOIN students s ON p.student_id = s.student_id
    ORDER BY p.payment_date DESC
  ');
  $payments = $stmt->fetchAll();

  $stmt = $pdo->query('
    SELECT g.*, s.fullname as student_name, s.grade_level, s.section
    FROM grades g
    LEFT JOIN students s ON g.student_id = s.student_id
    ORDER BY g.grade_id DESC
  ');
  $grades = $stmt->fetchAll();
} catch (Exception $e) {
  $error = 'Error loading detailed data: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Reports - SchoolSync</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-lg-2 col-md-3 sidebar">
        <h4>🏫 SchoolSync</h4>
        <a href="../index.php"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a href="registrar.php"><i class="fas fa-user-graduate me-2"></i>Registrar</a>
        <a href="cashier.php"><i class="fas fa-cash-register me-2"></i>Cashier</a>
        <a href="teacher.php"><i class="fas fa-book me-2"></i>Teacher</a>
        <a href="admin.php" class="active"><i class="fas fa-chart-bar me-2"></i>Admin Reports</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="user_management.php"><i class="fas fa-users-cog me-2"></i>User Management</a>
        <?php endif; ?>
        <hr>
        <a href="../logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
      </div>

      <!-- Main Content -->
      <div class="col-lg-10 col-md-9 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="text-primary fw-bold"><i class="fas fa-chart-bar me-2"></i>Admin Reports - System Overview</h3>
          <div class="d-flex align-items-center">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                 alt="User Avatar" width="45" class="rounded-circle me-2">
            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?> (<?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>)</span>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card stat-card text-primary">
              <div class="stat-icon"><i class="fas fa-users"></i></div>
              <h4><?php echo $total_students; ?></h4>
              <p class="mb-0">Total Students</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card text-success">
              <div class="stat-icon"><i class="fas fa-receipt"></i></div>
              <h4><?php echo $total_payments; ?></h4>
              <p class="mb-0">Total Payments</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card text-warning">
              <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
              <h4>₱<?php echo number_format($total_amount, 2); ?></h4>
              <p class="mb-0">Total Amount Collected</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card text-info">
              <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
              <h4><?php echo $total_grades; ?></h4>
              <p class="mb-0">Total Grades Recorded</p>
            </div>
          </div>
        </div>

        <!-- Students List -->
        <div class="card mb-4 p-3">
          <h5 class="fw-bold mb-3 text-center"><i class="fas fa-user-graduate me-2 text-primary"></i>Student Records</h5>
          <?php if ($total_students > 0): ?>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead class="table-primary">
                  <tr>
                    <th class="text-center">Name</th>
                    <th class="text-center">Grade</th>
                    <th class="text-center">Section</th>
                    <th class="text-center">Email</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $student): ?>
                    <tr>
                      <td class="text-center"><?php echo htmlspecialchars($student['fullname']); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($student['grade_level']); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($student['section'] ?: 'N/A'); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($student['email'] ?: 'N/A'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center">No student records found.</p>
          <?php endif; ?>
        </div>

        <!-- Grades Records -->
        <div class="card mb-4 p-3">
          <h5 class="fw-bold mb-3 text-center"><i class="fas fa-graduation-cap me-2 text-info"></i>Academic Grades Records</h5>
          <?php if ($total_grades > 0): ?>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead class="table-info">
                  <tr>
                    <th class="text-center">Student Name</th>
                    <th class="text-center">Grade Level</th>
                    <th class="text-center">Section</th>
                    <th class="text-center">Subject</th>
                    <th class="text-center">Grade</th>
                    <th class="text-center">Semester</th>
                    <th class="text-center">Academic Year</th>
                    <th class="text-center">Date Recorded</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($grades as $grade): ?>
                    <tr>
                      <td class="text-center"><?php echo htmlspecialchars($grade['student_name']); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($grade['grade_level']); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($grade['section'] ?: 'N/A'); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($grade['subject']); ?></td>
                      <td class="text-center">
                        <span class="badge bg-<?= $grade['grade'] >= 75 ? 'success' : 'danger'; ?>">
                          <?php echo number_format($grade['grade'], 2); ?>
                        </span>
                      </td>
                      <td class="text-center"><?php echo htmlspecialchars($grade['term']); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($grade['academic_year']); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars(date('M d, Y', strtotime($grade['date_recorded']))); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center">No grade records found.</p>
          <?php endif; ?>
        </div>

        <!-- Payments List -->
        <div class="card p-3">
          <h5 class="fw-bold mb-3 text-center"><i class="fas fa-cash-register me-2 text-success"></i>Payment Records</h5>
          <?php if ($total_payments > 0): ?>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead class="table-success">
                  <tr>
                    <th class="text-center">Student Name</th>
                    <th class="text-center">Amount</th>
                    <th class="text-center">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($payments as $payment): ?>
                    <tr>
                      <td class="text-center"><?php echo htmlspecialchars($payment['student_name'] ?: 'Unknown Student'); ?></td>
                      <td class="text-center">₱<?php echo number_format($payment['amount_paid'], 2); ?> / ₱<?php echo number_format($payment['amount_due'], 2); ?></td>
                      <td class="text-center"><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center">No payment records found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
