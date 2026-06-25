<?php
// activity3_edit_delete.php
// Class Activity 3: Edit and Delete Records
// Requirements: Update student course, delete selected student.
// Expected Skills: Record management, CRUD operations.

require_once 'db_connect.php';

$errors = [];
$status = $_GET['status'] ?? '';

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: activity3_edit_delete.php?status=deleted");
    exit();
}

// Handle course update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id     = (int) $_POST['id'];
    $course = trim($_POST['course'] ?? '');

    if (empty($course)) {
        $errors[] = "Course is required.";
    } else {
        $stmt = $conn->prepare("UPDATE students SET course = ? WHERE id = ?");
        $stmt->bind_param("si", $course, $id);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: activity3_edit_delete.php?status=updated");
            exit();
        } else {
            $errors[] = "Could not update the record.";
        }
        $stmt->close();
    }
}

// Load a record into the edit form if requested
$editing = null;
if (isset($_GET['edit_id'])) {
    $id = (int) $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$result = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");

$statusMessages = [
    'updated' => 'Student course updated successfully.',
    'deleted' => 'Student record deleted successfully.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Activity 3 — Edit and Delete Records</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Class Activity 3</p>
        <h1 class="text-xl font-bold text-slate-800 mb-1">Edit and Delete Records</h1>
        <p class="text-sm text-slate-500 mb-6">Record management &middot; CRUD operations</p>

        <?php if (isset($statusMessages[$status])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-2 mb-6">
                <?php echo htmlspecialchars($statusMessages[$status]); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-2 mb-6">
                <?php foreach ($errors as $err): ?>
                    <div><?php echo htmlspecialchars($err); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($editing): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-sm font-semibold text-slate-700 mb-3">
                    Update course for <?php echo htmlspecialchars($editing['fullname']); ?>
                </h2>
                <form method="POST" action="activity3_edit_delete.php" class="flex flex-col sm:flex-row gap-3">
                    <input type="hidden" name="id" value="<?php echo (int) $editing['id']; ?>">
                    <input type="text" name="course" value="<?php echo htmlspecialchars($editing['course']); ?>"
                           class="flex-1 border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md transition">
                        Update Course
                    </button>
                    <a href="activity3_edit_delete.php" class="text-sm text-slate-500 hover:text-slate-700 self-center">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Course</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-500"><?php echo (int) $row['id']; ?></td>
                                    <td class="px-4 py-3 font-medium text-slate-800"><?php echo htmlspecialchars($row['fullname']); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['course']); ?></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="activity3_edit_delete.php?edit_id=<?php echo (int) $row['id']; ?>"
                                           class="text-blue-600 hover:text-blue-800 font-medium mr-3">Edit</a>
                                        <a href="activity3_edit_delete.php?delete_id=<?php echo (int) $row['id']; ?>"
                                           onclick="return confirm('Delete this student record?');"
                                           class="text-red-600 hover:text-red-800 font-medium">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    No student records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="activity2_display.php" class="block mt-6 text-sm text-slate-500 hover:text-slate-700">
            &larr; Back to all records
        </a>

    </main>

</body>
</html>
