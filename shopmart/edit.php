<?php
/**
 * ShopMart - Edit Product (UPDATE)
 * Week 5: Database Components — CRUD Operations
 */

require_once 'auth_check.php';
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$errors = [];

// --- Fetch the existing product ---
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    header('Location: index.php');
    exit;
}

// --- Handle form submission ---
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
        $query = "UPDATE products SET name = ?, category = ?, price = ?, stock = ?, description = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssdisi', $name, $category, $price, $stock, $description, $id);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: index.php?updated=1');
            exit;
        } else {
            $errors[] = 'Could not update product: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }

    // Keep submitted values visible on validation error
    $product = array_merge($product, $_POST);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - ShopMart (Week 5)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white px-6 py-4">
        <span class="font-bold">ShopMart — Product Management</span>
    </nav>

    <main class="p-8 max-w-lg mx-auto">
        <a href="index.php" class="text-sm text-blue-600 hover:underline">&larr; Back to products</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-6">Edit Product #<?= $product['id'] ?></h1>

        <?php if ($errors): ?>
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= $product['id'] ?>" class="bg-white p-6 rounded-lg shadow">
            <label class="block mb-1 text-sm font-medium text-gray-700">Product Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Price (KES)</label>
            <input type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars($product['price']) ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Stock Quantity</label>
            <input type="number" min="0" name="stock" value="<?= htmlspecialchars($product['stock']) ?>"
                   class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-1 text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border rounded px-3 py-2 mb-6"><?= htmlspecialchars($product['description']) ?></textarea>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Update Product
            </button>
        </form>
    </main>
</body>
</html>
