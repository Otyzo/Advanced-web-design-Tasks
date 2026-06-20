<?php
/**
 * ShopMart - Add Product (CREATE)
 * Week 5: Database Components — CRUD Operations
 */

require_once 'auth_check.php';
require_once 'db_connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name']);
    $category    = trim($_POST['category']);
    $price       = trim($_POST['price']);
    $stock       = trim($_POST['stock']);
    $description = trim($_POST['description']);

    if ($name === '' || $category === '' || $price === '' || $stock === '') {
        $errors[] = 'Name, category, price, and stock are required.';
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = 'Price must be a positive number.';
    }
    if (!ctype_digit($stock)) {
        $errors[] = 'Stock must be a whole number.';
    }

    if (empty($errors)) {
        $query = "INSERT INTO products (name, category, price, stock, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssdis', $name, $category, $price, $stock, $description);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: index.php?created=1');
            exit;
        } else {
            $errors[] = 'Could not save product: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - ShopMart (Week 5)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white px-6 py-4">
        <span class="font-bold">ShopMart — Product Management</span>
    </nav>

    <main class="p-8 max-w-lg mx-auto">
        <a href="index.php" class="text-sm text-blue-600 hover:underline">&larr; Back to products</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-6">Add New Product</h1>

        <?php if ($errors): ?>
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="create.php" class="bg-white p-6 rounded-lg shadow">
            <label class="block mb-1 text-sm font-medium text-gray-700">Product Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Price (KES)</label>
            <input type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Stock Quantity</label>
            <input type="number" min="0" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border rounded px-3 py-2 mb-6"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                Save Product
            </button>
        </form>
    </main>
</body>
</html>
