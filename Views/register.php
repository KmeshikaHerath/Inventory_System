<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>

    <!-- Tailwind CSS -->
    <!-- <script src="../node_modules/tailwindcss/dist/tailwind.min.js"></script> -->
         <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery Validation -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <!-- CDN Link -->
    <script src="https://jsdelivr.net"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <style>
        .error {
            color: #ef4444; 
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        input.error {
            border-color: #ef4444;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">


<div class="flex justify-center items-center min-h-screen">
    <form id="registerForm" class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">

        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Create Account</h2>

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" id="name" name="name" placeholder="John Doe" 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" id="password" name="password" placeholder="" 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <div class="mb-6">
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="" 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Role</label>
            <select id="role_id" name="role_id" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 outline-none transition">
                <option value="">Select role</option>
                     <?php if (!empty($roles)): ?>
        <?php foreach ($roles as $role): ?>
           <option value="<?= htmlspecialchars($role['id']) ?>" 
                    <?= ($role['id'] == ($old_role_id ?? '')) ? 'selected' : '' ?>>
                <?= htmlspecialchars($role['role_name']) ?>
            </option>
        <?php endforeach; ?>
    
        <?php endif; ?>

            </select>

        </div>

        <button type="button" onclick="submitForm()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded w-full transition duration-200">
            Register
        </button>
        
        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account? <a href="#" class="text-blue-500 hover:underline">Log in</a>
        </p>
    </form>
</div>

<script>
$(document).ready(function() {
    $.validator.addMethod("passwordStrength", function(value, element) {

        return this.optional(element) || 
            (/[0-9]/.test(value) &&           
             /[a-zA-Z]/.test(value) &&        
             /[!@#$%^&*(),.?":{}|<>]/.test(value)); 
    }, "Password must contain at number least one letter, and one special character");

    $.validator.addMethod("confirmPassword", function(value, element) {
        return value === $("#password").val();
    }, "Passwords do not match");


    $("#registerForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 2
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 10,
                passwordStrength: true 
            },
            confirm_password: { 
                required: true,
                confirmPassword: true 
            },
            role_id: {
                required: true,
                min: 1
            }
            
        },
        messages: {
            name: {
                required: "Please enter your full name",
                minlength: "Name must be at least 2 characters"
            },
            email: {
                required: "Please enter your email address",
                email: "Please enter a valid email address"
            },
            password: {
                required: "Please provide a password",
                minlength: "Password must be at least 6 characters",
                maxlength: "Password must not exceed 10 characters",
                passwordStrength: "Password must contain number at least one letter, and one special character"
            },
            confirm_password: {
                required: "Please confirm your password",
                confirmPassword: "Passwords do not match"
            },

            role_id: {
                required: "Please select a role"
            } 
    },
    
    });

});


    function submitForm() {
        if ($("#registerForm").valid()) {
            $.ajax({
                url: "/register-user", 
                type: "POST",
                data: $("#registerForm").serialize(),

                success: function(response) {
                   // Success Popup
                Swal.fire({
                    icon: 'success',
                    title: 'Registration Successful!',
                    text: response, // Your "User Registered Successfully!" message
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $("#registerForm")[0].reset();
                    // Optional: window.location.href = "/login";
                });
            },
            error: function(xhr) {
                let errorMsg = "Something went wrong";
                
                // Try to parse JSON error from Controller
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch (e) {
                    errorMsg = xhr.responseText;
                }

                // Error Popup
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: errorMsg
                });
            }
        });
    }
}


</script>

</body>
</html>