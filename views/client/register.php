<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .register-container {
            max-width: 500px;
            margin: 50px auto;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #fff;
            border-bottom: none;
            text-align: center;
            padding: 20px;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
        }

        .form-control {
            padding: 12px;
        }

        .password-strength {
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="register-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Create Account</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php
                            switch ($_GET['error']) {
                                case '1':
                                    echo "Email already exists. Please use a different email.";
                                    break;
                                case '2':
                                    echo "Registration failed. Please try again.";
                                    break;
                                case '3':
                                    echo "All fields are required.";
                                    break;
                                case '4':
                                    echo "Please enter a valid email address.";
                                    break;
                                case '5':
                                    echo "Passwords do not match.";
                                    break;
                                default:
                                    echo "An error occurred. Please try again.";
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="../../controllers/client/registerController.php" method="POST" id="registerForm" onsubmit="return validateForm()">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required 
                                pattern="[0-9]{10,}" title="Please enter a valid phone number">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                required minlength="8" onkeyup="checkPasswordStrength()">
                            <div id="passwordStrength" class="password-strength"></div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" 
                                name="confirmPassword" required minlength="8">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">I agree to the Terms and Conditions</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                        <a href="index.php">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthDiv = document.getElementById('passwordStrength');
            const strength = {
                0: "Very Weak",
                1: "Weak",
                2: "Medium",
                3: "Strong",
                4: "Very Strong"
            };

            let score = 0;
            if (password.length >= 8) score++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) score++;
            if (password.match(/\d/)) score++;
            if (password.match(/[^a-zA-Z\d]/)) score++;

            strengthDiv.innerHTML = `Password Strength: ${strength[score]}`;
            const colors = ["#ff0000", "#ff4500", "#ffa500", "#9acd32", "#008000"];
            strengthDiv.style.color = colors[score];
        }

        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>