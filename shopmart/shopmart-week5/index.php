<?php
/**
 * ShopMart - Product List (READ)
 * Week 5: Database Components — CRUD Operations
 */

require_once 'auth_check.php';
require_once 'db_connect.php';

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");

$deleted = isset($_GET['deleted']);
$created = isset($_GET['created']);
$updated = isset($_GET['updated']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - ShopMart (Week 5)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
        <span class="font-bold">ShopMart — Product Management</span>
        <span class="text-sm">Logged in as <?= htmlspecialchars($_SESSION['username']) ?></span>
    </nav>

    <main class="p-8 max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Products</h1>
            <a href="create.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                + Add Product
            </a>
        </div>

        <?php if ($created): ?>
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
                Product created successfully.
            </div>
        <?php endif; ?>
        <?php if ($updated): ?>
            <div class="bg-blue-100 border border-blue-300 text-blue-700 px-4 py-3 rounded mb-4">
                Product updated successfully.
            </div>
        <?php endif; ?>
        <?php if ($deleted): ?>
            <div class="bg-yellow-100 border border-yellow-300 text-yellow-700 px-4 py-3 rounded mb-4">
                Product deleted.
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-left">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Price (KES)</th>
                        <th class="p-3">Stock</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="6" class="p-6 text-center text-gray-400">No products yet. Add one above.</td></tr>
                    <?php endif; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="p-3 text-gray-500"><?= $row['id'] ?></td>
                            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($row['category']) ?></td>
                            <td class="p-3 text-gray-600"><?= number_format($row['price'], 2) ?></td>
                            <td class="p-3 text-gray-600"><?= $row['stock'] ?></td>
                            <td class="p-3">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline mr-3">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>"
                                   onclick="return confirm('Delete this product?');"
                                   class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
