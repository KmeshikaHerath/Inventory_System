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
                👤 <?= $user['name'] ?? 'User' ?>
            </button>

            <div id="dropdown" class="hidden absolute right-0 mt-2 bg-white text-black rounded shadow w-32">
                <a href="/dashboard" class="block px-4 py-2 hover:bg-gray-200">Dashboard</a>
                <a href="/logout" class="block px-4 py-2 hover:bg-red-200 text-red-600">Logout</a>
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
                    <a href="/reports"
                        class="block p-2 hover:bg-gray-200 rounded">
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

    <script>
        $("#userBtn").click(function() {
            $("#dropdown").toggle();
        });

        $(document).click(function(e) {
            if (!$(e.target).closest("#userBtn").length) {
                $("#dropdown").hide();
            }
        });
    </script>

</body>

</html>