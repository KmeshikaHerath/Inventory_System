<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery Validation -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .sidebar-item:hover {
            background-color: #e5e7eb;
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">

    <!-- HEADER -->
    <div class="bg-blue-600 text-white p-4 flex justify-between items-center">

        <!-- Logo -->
        <h1 class="text-2xl font-bold">Inventory System</h1>

        <!-- User Dropdown -->
        <div class="relative">
            <button id="userBtn" class="bg-blue-700 px-4 py-2 rounded">
                👤 <?= $user['name'] ?? 'User' ?>
            </button>

            <!-- Dropdown -->
            <div id="dropdown" class="hidden absolute right-0 mt-2 bg-white text-black rounded shadow w-32">
                <a href="/dashboard" class="block px-4 py-2 hover:bg-gray-200">Dashboard</a>
                <a href="#" id="logoutBtn" class="block px-4 py-2 hover:bg-red-200 text-red-600">Logout</a>
            </div>
        </div>
    </div>

    <!-- BODY -->
    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-white shadow-md p-4">

            <h2 class="text-lg font-bold mb-4">Menu</h2>

            <ul class="space-y-2">

                <li class="p-2 rounded sidebar-item">📊 Dashboard</li>
                <li class="p-2 rounded sidebar-item">📦 Products</li>
                <li class="p-2 rounded sidebar-item">👥 Users</li>
                <li class="p-2 rounded sidebar-item">📁 Categories</li>
                <li class="p-2 rounded sidebar-item">📈 Reports</li>

            </ul>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-6 flex items-center justify-center">

            <div class="text-center bg-white p-10 rounded shadow">
                <h2 class="text-3xl font-bold mb-2">
                    Welcome, <?= $user['name'] ?? 'User' ?> 👋
                </h2>
                <p class="text-gray-500">Glad to see you back in the Inventory System</p>
            </div>

        </main>

    </div>

    <!-- JS -->
    <script>
        $(document).ready(function() {

            const $dropdown = $("#dropdown");

            /**
             * OPEN DROPDOWN
             */
            $("#userBtn").on("click", function(e) {
                e.stopPropagation();
                $dropdown.toggle();
            });

            /**
             * CLOSE DROPDOWN ONLY WHEN CLICKING OUTSIDE
             */
            $(document).on("click", function(e) {
                // If click is NOT inside dropdown or button
                if (!$(e.target).closest("#dropdown, #userBtn").length) {
                    $dropdown.hide();
                }
            });

            /**
             * KEEP DROPDOWN CLICK SAFE
             */
            $("#dropdown").on("click", function(e) {
                e.stopPropagation();
            });

            /**
             * LOGOUT CLICK - FIXED
             * Using direct event binding instead of delegation to ensure it works
             */
            $("#logoutBtn").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

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