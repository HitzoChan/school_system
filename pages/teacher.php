<?php
require_once '../config.php';
requireLogin();

// Load students and grades from database
$students = [];
$grades = [];

// Handle Grade Level Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_grade_level'])) {
  try {
    $stmt = $pdo->prepare('UPDATE students SET grade_level = ? WHERE student_id = ?');
    $stmt->execute([$_POST['grade_level'], $_POST['id']]);
  } catch (Exception $e) {
    $error = 'Error updating grade level: ' . $e->getMessage();
  }
  header("Location: teacher.php");
  exit();
}

// Handle Subject Grade Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_grade'])) {
  try {
    $stmt = $pdo->prepare('INSERT INTO grades (student_id, subject, grade, term, academic_year) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
      $_POST['student_id'],
      $_POST['subject'],
      $_POST['grade'],
      $_POST['term'],
      $_POST['academic_year']
    ]);
  } catch (Exception $e) {
    $error = 'Error adding grade: ' . $e->getMessage();
  }
  header("Location: teacher.php");
  exit();
}

// Handle Grade Deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_grade'])) {
  try {
    $stmt = $pdo->prepare('DELETE FROM grades WHERE grade_id = ?');
    $stmt->execute([$_POST['grade_id']]);
  } catch (Exception $e) {
    $error = 'Error deleting grade: ' . $e->getMessage();
  }
  header("Location: teacher.php");
  exit();
}

// Load students from database
try {
  $stmt = $pdo->query('SELECT * FROM students ORDER BY fullname');
  $students = $stmt->fetchAll();

  // Load grades with student names
  $stmt = $pdo->query('
    SELECT g.*, s.fullname as student_name, s.grade_level, s.section
    FROM grades g
    LEFT JOIN students s ON g.student_id = s.student_id
    ORDER BY g.grade_id DESC
  ');
  $grades = $stmt->fetchAll();
} catch (Exception $e) {
  $error = 'Error loading data: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Module - SchoolSync</title>
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
        <a href="teacher.php" class="active"><i class="fas fa-book me-2"></i>Teacher</a>
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
          <h3 class="text-primary fw-bold"><i class="fas fa-book me-2"></i>Teacher Module - Grade Management</h3>
          <div class="d-flex align-items-center">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                 alt="User Avatar" width="45" class="rounded-circle me-2">
            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?> (<?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>)</span>
          </div>
        </div>

        <!-- Add Grade Form -->
        <div class="card mb-4 p-3">
          <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Student Grade</h5>
          <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
          <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-3">
              <select name="student_id" class="form-select" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $student): ?>
                  <option value="<?= $student['student_id']; ?>">
                    <?= htmlspecialchars($student['fullname']); ?> (<?= htmlspecialchars($student['grade_level']); ?> - <?= htmlspecialchars($student['section'] ?: 'No Section'); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <input type="text" name="subject" class="form-control" placeholder="Subject" required>
            </div>
            <div class="col-md-2">
              <input type="number" name="grade" class="form-control" placeholder="Grade (0-100)" min="0" max="100" step="0.01" required>
            </div>
            <div class="col-md-2">
              <select name="term" class="form-select" required>
                <option value="">Select Semester</option>
                <option value="1st Semester">1st Semester</option>
                <option value="2nd Semester">2nd Semester</option>
                <option value="Summer">Summer</option>
              </select>
            </div>
            <div class="col-md-2">
              <input type="text" name="academic_year" class="form-control" placeholder="Academic Year (e.g., 2023-2024)" required>
            </div>
            <div class="col-md-1 text-end">
              <button type="submit" name="add_grade" class="btn btn-primary w-100">
                <i class="fas fa-plus"></i> Add
              </button>
            </div>
          </form>
        </div>

        <!-- Compact Student Overview -->
        <div class="card p-3 mb-4">
          <h5 class="fw-bold mb-3"><i class="fas fa-users me-2 text-primary"></i>Student Overview (<?= count($students); ?> students)</h5>
          <?php if (count($students) > 0): ?>
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Student ID</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $student): ?>
                    <tr>
                      <td class="fw-semibold"><?= htmlspecialchars($student['fullname']); ?></td>
                      <td><?= htmlspecialchars($student['grade_level']); ?></td>
                      <td><?= htmlspecialchars($student['section'] ?: 'N/A'); ?></td>
                      <td><small class="text-muted"><?= htmlspecialchars($student['student_no'] ?: 'N/A'); ?></small></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">No student records found. Registrar must add students first.</p>
          <?php endif; ?>
        </div>

        <!-- Grades Records Table -->
        <div class="card p-3 mt-4">
          <h5 class="fw-bold mb-3"><i class="fas fa-graduation-cap me-2 text-success"></i>Grades Records</h5>
          <?php if (count($grades) > 0): ?>
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead class="table-success">
                  <tr>
                    <th>Student Name</th>
                    <th>Year Level</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th class="text-center">Grade</th>
                    <th>Semester</th>
                    <th>Academic Year</th>
                    <th>Date Recorded</th>
                    <th width="100">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($grades as $grade): ?>
                    <tr>
                      <td><?= htmlspecialchars($grade['student_name']); ?></td>
                      <td><?= htmlspecialchars($grade['grade_level']); ?></td>
                      <td><?= htmlspecialchars($grade['section'] ?: 'N/A'); ?></td>
                      <td><?= htmlspecialchars($grade['subject']); ?></td>
                      <td class="text-center">
                        <span class="badge bg-<?= $grade['grade'] >= 75 ? 'success' : 'danger'; ?>">
                          <?= number_format($grade['grade'], 2); ?>
                        </span>
                      </td>
                      <td><?= htmlspecialchars($grade['term']); ?></td>
                      <td><?= htmlspecialchars($grade['academic_year']); ?></td>
                      <td><?= htmlspecialchars(date('M d, Y', strtotime($grade['date_recorded']))); ?></td>
                      <td>
                        <form method="POST" class="d-inline">
                          <input type="hidden" name="grade_id" value="<?= $grade['grade_id']; ?>">
                          <button type="submit" name="delete_grade" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this grade?')">
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
            <p class="text-muted mb-0">No grade records found. Add grades using the form above.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
