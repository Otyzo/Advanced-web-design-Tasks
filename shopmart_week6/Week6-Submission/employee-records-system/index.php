<?php
// index.php - View & Search employee records
require_once 'config.php';
require_once 'includes/auth.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM employees
         WHERE full_name LIKE ? OR department LIKE ? OR position LIKE ?
         ORDER BY id DESC"
    );
    $likeSearch = "%" . $search . "%";
    $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, "SELECT * FROM employees ORDER BY id DESC");
}

$status = $_GET['status'] ?? '';
$statusMessages = [
    'added'   => 'Employee record added successfully.',
    'updated' => 'Employee record updated successfully.',
    'deleted' => 'Employee record deleted successfully.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Records System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

        <?php if (isset($statusMessages[$status])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-2 mb-6">
                <?php echo htmlspecialchars($statusMessages[$status]); ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-xl font-semibold text-slate-800">Employee Records</h2>

            <form method="GET" action="index.php" class="flex gap-2">
                <input type="text" name="search" placeholder="Search name, department, position..."
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="w-full sm:w-72 border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                        class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                    Search
                </button>
                <?php if ($search !== ''): ?>
                    <a href="index.php" class="text-sm text-slate-500 hover:text-slate-700 self-center">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Position</th>
                            <th class="px-4 py-3">Salary (KES)</th>
                            <th class="px-4 py-3">Date Hired</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-500"><?php echo (int) $row['id']; ?></td>
                                    <td class="px-4 py-3 font-medium text-slate-800"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo number_format((float) $row['salary'], 2); ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['date_hired']); ?></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="edit_employee.php?id=<?php echo (int) $row['id']; ?>"
                                           class="text-blue-600 hover:text-blue-800 font-medium mr-3">Edit</a>
                                        <a href="delete_employee.php?id=<?php echo (int) $row['id']; ?>"
                                           onclick="return confirm('Delete this employee record? This cannot be undone.');"
                                           class="text-red-600 hover:text-red-800 font-medium">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                    No employee records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
