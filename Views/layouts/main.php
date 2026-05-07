<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?></title>

    <!-- Tailwind -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans">

    <!-- 🔷 TOP NAVBAR -->
    <div class="bg-blue-600 text-white px-6 py-3 flex justify-between items-center">

        <h1 class="text-xl font-bold">Inventory System</h1>

        <div class="relative">
            <button id="userBtn" class="bg-blue-700 px-4 py-2 rounded">
                <?php $user = $_SESSION['user'] ?? null; ?>
                👤 <?= $user['name'] ?? 'User' ?>
            </button>

            <div id="dropdown" class="hidden absolute right-0 mt-2 bg-white text-black rounded shadow w-32">
                <a href="/dashboard" class="block px-4 py-2 hover:bg-gray-200">Dashboard</a>
                <a href="/logout" id="logoutBtn" class="block px-4 py-2 hover:bg-red-200 text-red-600">Logout</a>
            </div>
        </div>
    </div>

    <div class="flex">

        <!-- 📁 SIDEBAR -->
        <aside class="w-64 bg-white h-screen shadow p-4">

            <ul class="space-y-2">
                <li><a href="/dashboard" class="block p-2 hover:bg-gray-200 rounded">📊 Dashboard</a></li>
                <li><a href="/products" class="block p-2 hover:bg-gray-200 rounded">📦 Products</a></li>
                <li><a href="/users" class="block p-2 hover:bg-gray-200 rounded">👥 Users</a></li>
                <li>
                    <a href="/categories"
                        class="block p-2 hover:bg-gray-200 rounded">
                        📁 Categories
                    </a>
                </li>

                <li>
                    <a href="/report" class="block p-2 hover:bg-gray-200 rounded">
                        📈 Reports
                    </a>
                </li>
            </ul>

        </aside>

        <!-- 📊 MAIN CONTENT -->
        <main class="flex-1 p-6">
            <?= $content ?? '' ?>
        </main>

    </div>

    <!-- JS -->
    <script>
        $(document).ready(function() {

            $("#userBtn").on("click", function(e) {
                e.stopPropagation();
                $("#dropdown").toggleClass("hidden");
            });

            $(document).on("click", function() {
                $("#dropdown").addClass("hidden");
            });

            // Logout (SAFE FIX - always works)
            $(document).on("click", "#logoutBtn", function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Logout?',
                    text: "Are you sure you want to logout?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Logout',
                    cancelButtonText: 'Stay'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/logout";
                    }
                });
            });

        });
    </script>

</body>

</html>