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