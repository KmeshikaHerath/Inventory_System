<!DOCTYPE html>
<html>

<head>
    <title>Login</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery Validation -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">

    <!-- ================= HEADER ================= -->
    <div class="bg-blue-600 text-white p-4">
        <h1 class="text-3xl font-bold text-center">
            Inventory Management System
        </h1>
    </div>

    <!-- ================= LOGIN FORM ================= -->
    <div class="flex justify-center mt-20">
        <form id="loginForm" class="bg-white p-6 rounded shadow-md w-96">

            <h2 class="text-xl font-bold mb-4 text-center">Login</h2>

            <!-- Email -->
            <input type="email"
                name="email"
                id="email"
                placeholder="Email"
                class="w-full mb-3 p-2 border rounded">

            <!-- Password -->
            <input type="password"
                name="password"
                placeholder="Password"
                class="w-full mb-3 p-2 border rounded">

            <!-- Remember Me -->
            <label class="flex items-center mb-3">
                <input type="checkbox" name="remember" class="mr-2">
                Remember Me
            </label>

            <!-- Submit Button -->
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded w-full transition">
                Login
            </button>
        </form>
    </div>

    <!-- ================= SCRIPT ================= -->
    <script>
        /**
         * Login Page Script
         * Handles:
         *  - Form validation
         *  - AJAX login request
         *  - SweetAlert notifications
         *  - Cookie-based email remember feature
         */
        $(document).ready(function() {

            /**
             * ===============================
             * Autofill email from cookie
             * ===============================
             */
            let cookies = document.cookie.split(';');

            cookies.forEach(function(c) {
                if (c.trim().startsWith("remember_email=")) {
                    let email = c.split("=")[1];
                    $("#email").val(email);
                }
            });

            /**
             * ===============================
             * jQuery Validation Rules
             * ===============================
             */
            $("#loginForm").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true
                    }
                },

                messages: {
                    email: {
                        required: "Please enter your email",
                        email: "Enter a valid email address"
                    },
                    password: "Please enter your password"
                },

                /**
                 * ===============================
                 * Submit Handler (AJAX Login)
                 * ===============================
                 */
                submitHandler: function(form) {

                    $.ajax({
                        url: "/login_user",
                        type: "POST",
                        data: $(form).serialize(),
                        dataType: "json",
                        contentType: "application/x-www-form-urlencoded; charset=UTF-8",

                        /**
                         * ===============================
                         * Success Response Handler
                         * ===============================
                         */
                        success: function(res) {

                            if (res.status === "success") {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Login Successful',
                                    text: res.message || 'Redirecting...',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = res.redirect;
                                });

                            } else {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Login Failed',
                                    text: res.message || 'Invalid credentials'
                                });
                            }

                        },

                        /**
                         * ===============================
                         * Error Response Handler
                         * ===============================
                         */
                        error: function(xhr) {

                            let msg = "Server error. Please try again.";

                            if (xhr.status === 422 && xhr.responseJSON) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });

                            console.error("Login Error:", xhr);
                        }
                    });
                }
            });
        });
    </script>

</body>

</html>