<nav class="bg-slate-800 text-white shadow-md">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            <a href="index.php" class="text-lg font-semibold tracking-wide">
                Employee Records System
            </a>
            <div class="flex items-center gap-4 text-sm">
                <span class="hidden sm:inline text-slate-300">
                    Signed in as <span class="font-medium text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </span>
                <a href="add_employee.php" class="bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md transition">
                    + Add Employee
                </a>
                <a href="logout.php" class="bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded-md transition">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>
