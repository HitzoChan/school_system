<?php
require_once '../config.php';
requireLogin();

// Database operations for payments
$payments = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['add_payment'])) {
    try {
      $stmt = $pdo->prepare('INSERT INTO payments (student_id, amount_due, amount_paid, payment_date, remarks) VALUES (?, ?, ?, ?, ?)');
      $stmt->execute([
        $_POST['student_id'],
        $_POST['amount_due'],
        $_POST['amount_paid'],
        $_POST['payment_date'],
        $_POST['remarks'] ?: null
      ]);
    } catch (Exception $e) {
      $error = 'Error adding payment: ' . $e->getMessage();
    }
  } elseif (isset($_POST['delete_payment'])) {
    try {
      $stmt = $pdo->prepare('DELETE FROM payments WHERE payment_id = ?');
      $stmt->execute([$_POST['id']]);
    } catch (Exception $e) {
      $error = 'Error deleting payment: ' . $e->getMessage();
    }
  }
  header("Location: cashier.php");
  exit();
}

// Load payments from database with student names
try {
  $stmt = $pdo->query('
    SELECT p.*, s.fullname as student_name
    FROM payments p
    LEFT JOIN students s ON p.student_id = s.student_id
    ORDER BY p.payment_date DESC
  ');
  $payments = $stmt->fetchAll();
} catch (Exception $e) {
  $error = 'Error loading payments: ' . $e->getMessage();
}

// Load students for dropdown
try {
  $stmt = $pdo->query('SELECT student_id, fullname FROM students ORDER BY fullname');
  $students = $stmt->fetchAll();
} catch (Exception $e) {
  $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cashier Module - SchoolSync</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 sidebar">
        <h4>🏫 SchoolSync</h4>
        <a href="../index.php"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a href="registrar.php"><i class="fas fa-user-graduate me-2"></i>Registrar</a>
        <a href="cashier.php" class="active"><i class="fas fa-cash-register me-2"></i>Cashier</a>
        <a href="teacher.php"><i class="fas fa-book me-2"></i>Teacher</a>
        <a href="admin.php"><i class="fas fa-chart-bar me-2"></i>Admin Reports</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="user_management.php"><i class="fas fa-users-cog me-2"></i>User Management</a>
        <?php endif; ?>
        <hr>
        <a href="../logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
      </div>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10 main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="text-primary fw-bold"><i class="fas fa-cash-register me-2"></i>Cashier Module - Payment Records</h3>
          <div class="d-flex align-items-center">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                 alt="User Avatar" width="45" class="rounded-circle me-2">
            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?> (<?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>)</span>
          </div>
        </div>

        <!-- Add Payment Form -->
        <div class="card mb-4 p-3">
          <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2 text-success"></i>Add New Payment</h5>
          <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
          <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-3">
              <select name="student_id" class="form-select" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $student): ?>
                  <option value="<?= $student['student_id']; ?>"><?= htmlspecialchars($student['fullname']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <input type="number" name="amount_due" class="form-control" placeholder="Amount Due (₱)" step="0.01" required>
            </div>
            <div class="col-md-2">
              <input type="number" name="amount_paid" class="form-control" placeholder="Amount Paid (₱)" step="0.01" required>
            </div>
            <div class="col-md-2">
              <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-2">
              <input type="text" name="remarks" class="form-control" placeholder="Remarks (Optional)">
            </div>
            <div class="col-md-1 text-end">
              <button type="submit" name="add_payment" class="btn btn-success w-100">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </form>
        </div>

        <!-- Payments List -->
        <div class="card p-3">
          <h5 class="fw-bold mb-3"><i class="fas fa-list me-2 text-success"></i>Payment Records</h5>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Student Name</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Remarks</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $payment): ?>
              <tr>
                <td><?php echo htmlspecialchars($payment['student_name'] ?: 'Unknown Student'); ?></td>
                <td>₱<?php echo number_format($payment['amount_paid'], 2); ?> / ₱<?php echo number_format($payment['amount_due'], 2); ?></td>
                <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                <td><?php echo htmlspecialchars($payment['remarks'] ?: 'N/A'); ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $payment['payment_id']; ?>">
                    <button type="submit" name="delete_payment" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this payment?')">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
