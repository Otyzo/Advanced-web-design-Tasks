<?php
// activity2_display.php
// Class Activity 2: Display Student Records
// Requirements: Retrieve all records, display in a table.
// Expected Skills: SQL queries, data retrieval, table formatting.

require_once 'db_connect.php';

$result = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Activity 2 — Display Student Records</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Class Activity 2</p>
        <h1 class="text-xl font-bold text-slate-800 mb-1">Display Student Records</h1>
        <p class="text-sm text-slate-500 mb-6">SQL queries &middot; Data retrieval &middot; Table formatting</p>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
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
                                    <td class="px-4 py-3 text-right">
                                        <a href="activity3_edit_delete.php?edit_id=<?php echo (int) $row['id']; ?>"
                                           class="text-blue-600 hover:text-blue-800 font-medium">Manage</a>
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

        <a href="activity1_register.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            + Register another student
        </a>

    </main>

</body>
</html>
