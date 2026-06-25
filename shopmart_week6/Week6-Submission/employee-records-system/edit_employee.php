<?php
// edit_employee.php - Update operation
require_once 'config.php';
require_once 'includes/auth.php';

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int) ($_POST['id'] ?? $_GET['id']);
$errors = [];

// Load existing record first (used to pre-fill the form on GET, and as fallback values)
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) {
    header("Location: index.php");
    exit();
}

$full_name  = $employee['full_name'];
$email      = $employee['email'];
$phone      = $employee['phone'];
$department = $employee['department'];
$position   = $employee['position'];
$salary     = $employee['salary'];
$date_hired = $employee['date_hired'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $position   = trim($_POST['position'] ?? '');
    $salary     = trim($_POST['salary'] ?? '');
    $date_hired = trim($_POST['date_hired'] ?? '');

    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($department)) {
        $errors[] = "Department is required.";
    }
    if (empty($position)) {
        $errors[] = "Position is required.";
    }
    if ($salary !== '' && !is_numeric($salary)) {
        $errors[] = "Salary must be a number.";
    }
    if (empty($date_hired)) {
        $errors[] = "Date hired is required.";
    }

    if (empty($errors)) {
        $salaryValue = $salary === '' ? 0 : (float) $salary;
        $stmt = $conn->prepare(
            "UPDATE employees
             SET full_name = ?, email = ?, phone = ?, department = ?, position = ?, salary = ?, date_hired = ?
             WHERE id = ?"
        );
        $stmt->bind_param("sssssdsi", $full_name, $email, $phone, $department, $position, $salaryValue, $date_hired, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?status=updated");
            exit();
        } else {
            $errors[] = "Database error: could not update the employee record.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Employee Records System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-6">Edit Employee Record</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3 mb-6">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_employee.php" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>"
                       class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                       class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>"
                       class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                    <input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>"
                           class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Position</label>
                    <input type="text" name="position" value="<?php echo htmlspecialchars($position); ?>"
                           class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Salary (KES)</label>
                    <input type="text" name="salary" value="<?php echo htmlspecialchars($salary); ?>"
                           class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date Hired</label>
                    <input type="date" name="date_hired" value="<?php echo htmlspecialchars($date_hired); ?>"
                           class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-md transition">
                    Update Employee
                </button>
                <a href="index.php" class="text-slate-500 hover:text-slate-700 text-sm">Cancel</a>
            </div>

        </form>
    </main>

</body>
</html>
