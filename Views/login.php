<!DOCTYPE html>
<html>
    <head>
    <title>Login</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery Validation -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    </head>

    <body class="bg-gray-100">

        <!-- Header -->
        <div class="bg-blue-600 text-white p-4">
            <h1 class="text-3xl font-bold"><center>Inventory Management System</center></h1>
        </div>

        <div class="flex justify-center mt-20">
            <form id="loginForm" class="bg-white p-6 rounded shadow-md w-96">

                <h2 class="text-xl font-bold mb-4 text-center">Login</h2>

                <!-- Message Box -->
                <div id="messageBox" class="hidden mb-3 p-2 rounded text-center"></div>

                <input type="email" name="email" id="email" placeholder="Email"
                    class="w-full mb-3 p-2 border rounded">

                <input type="password" name="password" placeholder="Password"
                    class="w-full mb-3 p-2 border rounded">

                <label class="flex items-center mb-3">
                    <input type="checkbox" name="remember" class="mr-2">
                    Remember Me
                </label>

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded w-full transition duration-200">
                    Login
                </button>
            </form>
        </div>

        <script>
        $(document).ready(function() {

            // Autofill email from cookie
            let cookies = document.cookie.split(';');
            cookies.forEach(function(c) {
                if (c.trim().startsWith("remember_email=")) {
                    let email = c.split("=")[1];
                    $("#email").val(email);
                }
            });

            $("#loginForm").validate({
                rules: {
                    email: { required: true, email: true },
                    password: "required"
                },

                messages: {
                    email: {
                        required: "Please enter your email",
                        email: "Please enter a valid email address"
                    },
                    password: "Please enter your password"
                },

                submitHandler: function(form) {

                    $.ajax({
                        url: "/login_user",  // ✅ FIXED: Changed from "/login" to "/login_user"
                        type: "POST",
                        data: $(form).serialize(),
                        dataType: "json",

                        success: function(res) {
                            let box = $("#messageBox");
                            box.removeClass("hidden");

                            if (res.status === "success") {
                                box.removeClass("bg-red-100 text-red-700")
                                .addClass("bg-green-100 text-green-700")
                                .text(res.message);

                                // Save email to cookie if remember me is checked
                                if ($("input[name='remember']").is(":checked")) {
                                    document.cookie = "remember_email=" + $("#email").val() + "; path=/; max-age=" + (30 * 24 * 60 * 60);
                                } else {
                                    document.cookie = "remember_email=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC";
                                }

                                // Redirect after short delay
                                setTimeout(function() {
                                    if (res.role === "admin") {
                                        window.location.href = "/admin/dashboard";
                                    } else {
                                        window.location.href = "/user/dashboard";
                                    }
                                }, 1000);

                            } else {
                                box.removeClass("bg-green-100 text-green-700")
                                .addClass("bg-red-100 text-red-700")
                                .text(res.message);
                            }
                        },

                        error: function(xhr, status, error) {
                            let box = $("#messageBox");
                            box.removeClass("hidden")
                            .addClass("bg-red-100 text-red-700");
                            
                            // Try to get detailed error message from server response
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                box.text(xhr.responseJSON.message);
                            } else {
                                box.text("Server error. Please try again later.");
                            }
                            console.error("Login error:", error, xhr.responseText);
                        }
                    });
                }
            });
        });
        </script>

    </body>
</html>