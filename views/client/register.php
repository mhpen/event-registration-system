<?php include_once '../shared/header.php'; ?>

<body class="bg-background min-h-screen font-sans antialiased">
    <div class="container relative min-h-screen flex-col items-center justify-center grid lg:max-w-none lg:grid-cols-2 lg:px-0">
        <!-- Left side with image -->
        <div class="relative hidden h-full flex-col bg-muted p-10 text-white lg:flex dark:border-r">
            <div class="absolute inset-0">
                <!-- Background Image -->
                <img 
                    src="../../public/assets/pexels-adrien-olichon-1257089-2387532.jpg" 
                    alt="Background" 
                    class="h-full w-full object-cover"
                />
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-black/50"></div>
                <!-- Additional Gradient for better text readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-black/30"></div>
            </div>
            <!-- Content -->
            <div class="relative z-20 flex items-center text-lg font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" class="mr-2 h-6 w-6">
                    <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3" />
                </svg>
                Event Registration System
            </div>
            <!-- Quote Section -->
            <div class="relative z-20 mt-auto">
                <blockquote class="space-y-2">
                    <p class="text-lg">
                        "Create your account today and unlock access to exclusive events and experiences."
                    </p>
                    <footer class="text-sm text-white/60">
                        Event Registration System
                    </footer>
                </blockquote>
            </div>
        </div>

        <!-- Right side with registration form -->
        <div class="lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[400px]">
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-2xl font-semibold tracking-tight">Create an account</h1>
                    <p class="text-sm text-muted-foreground">Enter your details to get started</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="rounded-md bg-destructive/15 text-destructive px-4 py-3 text-sm">
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
                    <div class="grid gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <label class="text-sm font-medium leading-none" for="firstName">First Name</label>
                                <input type="text" id="firstName" name="firstName" required
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                            </div>
                            <div class="grid gap-2">
                                <label class="text-sm font-medium leading-none" for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="lastName" required
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <label class="text-sm font-medium leading-none" for="email">Email</label>
                            <input type="email" id="email" name="email" required
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="name@example.com">
                        </div>

                        <div class="grid gap-2">
                            <label class="text-sm font-medium leading-none" for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required pattern="[0-9]{10,}"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Enter your phone number">
                        </div>

                        <div class="grid gap-2">
                            <label class="text-sm font-medium leading-none" for="password">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required minlength="8"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    onkeyup="checkPasswordStrength()">
                                <div id="passwordStrength" class="absolute right-0 -bottom-6 text-xs"></div>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <label class="text-sm font-medium leading-none" for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                        </div>

                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="terms" required
                                class="h-4 w-4 rounded border border-input bg-background">
                            <label for="terms" class="text-sm text-muted-foreground">
                                I agree to the <a href="#" class="text-primary hover:underline">Terms and Conditions</a>
                            </label>
                        </div>

                        <button type="submit" 
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">
                            Create Account
                        </button>
                    </div>
                </form>

                <div class="text-center text-sm">
                    <span class="text-muted-foreground">Already have an account? </span>
                    <a href="login.php" class="text-primary hover:underline">Sign in</a>
                </div>

                <div class="text-center">
                    <a href="index.php" class="text-sm text-muted-foreground hover:underline">Back to Home</a>
                </div>
            </div>
        </div>
    </div>

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
            const colors = ["#dc2626", "#ea580c", "#d97706", "#65a30d", "#16a34a"];
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