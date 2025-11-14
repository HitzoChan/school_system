<?php
require_once '../config.php';
requireLogin();

// Database operations for students
$students = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['add_student'])) {
    try {
      $stmt = $pdo->prepare('INSERT INTO students (student_no, fullname, grade_level, section, email) VALUES (?, ?, ?, ?, ?)');
      $stmt->execute([
        $_POST['student_no'] ?: null,
        $_POST['name'],
        $_POST['grade'],
        $_POST['section'] ?: null,
        $_POST['email']
      ]);
    } catch (Exception $e) {
      $error = 'Error adding student: ' . $e->getMessage();
    }
  } elseif (isset($_POST['delete_student'])) {
    try {
      $stmt = $pdo->prepare('DELETE FROM students WHERE student_id = ?');
      $stmt->execute([$_POST['id']]);
    } catch (Exception $e) {
      $error = 'Error deleting student: ' . $e->getMessage();
    }
  }
  header("Location: registrar.php");
  exit();
}

// Load students from database
try {
  $stmt = $pdo->query('SELECT * FROM students ORDER BY date_enrolled DESC');
  $students = $stmt->fetchAll();
} catch (Exception $e) {
  $error = 'Error loading students: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Module - SchoolSync</title>
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
        <a href="registrar.php" class="active"><i class="fas fa-user-graduate me-2"></i>Registrar</a>
        <a href="cashier.php"><i class="fas fa-cash-register me-2"></i>Cashier</a>
        <a href="teacher.php"><i class="fas fa-book me-2"></i>Teacher</a>
        <a href="admin.php"><i class="fas fa-chart-bar me-2"></i>Admin Reports</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="user_management.php"><i class="fas fa-users-cog me-2"></i>User Management</a>
        <?php endif; ?>
        <hr>
        <a href="../logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
      </div>

      <!-- Main Content -->
      <div class="col-lg-10 col-md-9 main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="text-primary fw-bold"><i class="fas fa-user-graduate me-2"></i>Registrar - Student Records</h3>
          <div class="d-flex align-items-center">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                 alt="User Avatar" width="45" class="rounded-circle me-2">
            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?> (<?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>)</span>
          </div>
        </div>

        <!-- Add Student Form -->
        <div class="card mb-4 p-3">
          <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Student</h5>
          <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
          <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-2">
              <input type="text" name="student_no" class="form-control" placeholder="Student No (Optional)">
            </div>
            <div class="col-md-3">
              <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="col-md-2">
              <select name="grade" class="form-select" required>
                <option value="">Year Level</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
              </select>
            </div>
            <div class="col-md-2">
              <input type="text" name="section" class="form-control" placeholder="Section (e.g., A, B, 1A)">
            </div>
            <div class="col-md-3">
              <input type="email" name="email" class="form-control" placeholder="Email">
            </div>
            <div class="col-md-2 text-end">
              <button type="submit" name="add_student" class="btn btn-primary w-100">
                <i class="fas fa-plus"></i> Add
              </button>
            </div>
          </form>
        </div>

        <!-- Student Records Table -->
        <div class="card p-3">
          <h5 class="fw-bold mb-3"><i class="fas fa-list me-2 text-primary"></i>Student List</h5>
          <?php if (count($students) > 0): ?>
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead class="table-primary">
                  <tr>
                    <th>Name</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Email</th>
                    <th width="100">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $student): ?>
                    <tr>
                      <td><?= htmlspecialchars($student['fullname']); ?></td>
                      <td><?= htmlspecialchars($student['grade_level']); ?></td>
                      <td><?= htmlspecialchars($student['section'] ?: 'N/A'); ?></td>
                      <td><?= htmlspecialchars($student['email'] ?: 'N/A'); ?></td>
                      <td>
                        <form method="POST" class="d-inline">
                          <input type="hidden" name="id" value="<?= $student['student_id']; ?>">
                          <button type="submit" name="delete_student" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">No student records found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
