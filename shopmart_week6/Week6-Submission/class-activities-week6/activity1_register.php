<?php
// activity1_register.php
// Class Activity 1: Build a Student Registration Form
// Requirements: Full Name, Email, Course — store information in MySQL.
// Expected Skills: Form creation, database connection, data insertion.

require_once 'db_connect.php';

$errors = [];
$fullname = $email = $course = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $course   = trim($_POST['course'] ?? '');

    if (empty($fullname)) {
        $errors[] = "Full name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($course)) {
        $errors[] = "Course is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO students (fullname, email, course) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $email, $course);

        if ($stmt->execute()) {
            $success = true;
            $fullname = $email = $course = "";
        } else {
            $errors[] = "Could not save the record. Please try again.";
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
    <title>Class Activity 1 — Student Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md bg-white rounded-xl shadow-sm p-8">
        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Class Activity 1</p>
        <h1 class="text-xl font-bold text-slate-800 mb-1">Student Registration Form</h1>
        <p class="text-sm text-slate-500 mb-6">Form creation &middot; Database connection &middot; Data insertion</p>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-2 mb-4">
                Record saved successfully.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3 mb-4">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="activity1_register.php" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>"
                       placeholder="Full Name"
                       class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="Email"
                       class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Course</label>
                <input type="text" name="course" value="<?php echo htmlspecialchars($course); ?>"
                       placeholder="Course"
                       class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition">
                Save
            </button>
        </form>

        <a href="activity2_display.php" class="block text-center text-sm text-slate-500 hover:text-slate-700 mt-6">
            View all registered students &rarr;
        </a>
    </div>

</body>
</html>
