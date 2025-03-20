<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UMUnity</title>

    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/assets/js/script.js" defer></script>
</head>

<body class="body">
    <!-- Back Button -->
    <button class="back-btn" onclick="goBack()">
        <i class="fa-solid fa-arrow-left"></i>
    </button>

    <div class="login-wrapper">
        <!-- Left Branding Panel -->
        <div class="login-left">
            <div class="overlay"></div>
            <div class="left-content">
                <img src="/assets/images/logo1.png" alt="UMUnity Logo">
                <h2>Welcome to SchoolOrgs</h2>
                <p>Manage your organizations efficiently and collaborate with your peers.</p>
            </div>
        </div>

        <!-- Right Login Panel -->
        <div class="login-right">
            <div class="login-header">
                <h2>Sign In</h2>
                <p>Access your account</p>
            </div>

            <!-- Google Sign-In (Optional: Remove if not implemented) -->
            <button type="button" class="btn btn-google">
                <i class="fa-brands fa-google"></i>
                <span style="color:rgb(194, 194, 194); font-size:13px;">Sign in with Google</span>
            </button>

            <!-- Divider -->
            <div class="divider">
                <span style="color:#1d1d1d; margin-left:5px; margin-right:5px;">OR</span>
            </div>

            <!-- Login Form -->
            <form action="auth.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3 password-wrapper">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>

</html>
<script>
    document.querySelector("form").addEventListener("submit", function(event) {
        let loginBtn = document.querySelector("button[type='submit']");
        loginBtn.innerHTML = "<span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>";
        loginBtn.disabled = true;
    });
</script>