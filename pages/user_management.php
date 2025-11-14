<?php
require_once '../config.php';
requireLogin();

// Only allow admin access
if (!hasRole('admin')) {
    header("Location: ../index.php");
    exit();
}

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $fullname = trim($_POST['fullname']);

        if (empty($username) || empty($password) || empty($role) || empty($fullname)) {
            $message = '<div class="alert alert-danger">All fields are required!</div>';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, fullname, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$username, $hashed_password, $role, $fullname]);
                $message = '<div class="alert alert-success">User added successfully!</div>';
            } catch (Exception $e) {
                $message = '<div class="alert alert-danger">Error adding user: ' . $e->getMessage() . '</div>';
            }
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
            $stmt->execute([$user_id]);
            $message = '<div class="alert alert-success">User deleted successfully!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error deleting user: ' . $e->getMessage() . '</div>';
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT user_id, username, fullname, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - SchoolSync</title>
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
                <a href="cashier.php"><i class="fas fa-cash-register me-2"></i>Cashier</a>
                <a href="teacher.php"><i class="fas fa-book me-2"></i>Teacher</a>
                <a href="admin.php"><i class="fas fa-chart-bar me-2"></i>Admin Reports</a>
                <a href="#" class="active"><i class="fas fa-users-cog me-2"></i>User Management</a>
                <hr>
                <a href="../logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="text-primary fw-bold"><i class="fas fa-users-cog me-2"></i>User Management</h3>
                    <div class="d-flex align-items-center">
                        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             alt="User Avatar" width="45" class="rounded-circle me-2">
                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?> (<?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>)</span>
                    </div>
                </div>

                <?php echo $message; ?>

                <!-- Add User Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="cashier">Cashier</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="add_user" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Existing Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Role</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $user['role'] === 'admin' ? 'primary' :
                                                     ($user['role'] === 'teacher' ? 'warning' : 'success');
                                            ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
