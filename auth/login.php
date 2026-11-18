<?php
session_start();
require_once("../config/database.php");

if (isset($_SESSION['admin_id'])) {
    header("Location: ../admin/dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() == 1) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];

                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                $error = "Incorrect Password.";
            }
        } else {
            $error = "Username not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | CRIMS - DGPI</title>
    <link rel="icon" type="image/png" href="../assets/images/dgpi_logo.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #090812;
            --card-bg: rgba(18, 16, 32, 0.88);
            --accent-amber: #f59e0b;
            --accent-violet: #8b5cf6;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background-color: var(--bg-dark);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* ডট-ম্যাট্রিক্স ব্যাকগ্রাউন্ড প্যাটার্ন (স্টুডেন্ট পেজের গ্রিড থেকে আলাদা) */
        .dot-pattern {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(139, 92, 246, 0.16) 1.2px, transparent 1.2px);
            background-size: 28px 28px;
            mask-image: radial-gradient(circle at center, black 38%, transparent 82%);
            -webkit-mask-image: radial-gradient(circle at center, black 38%, transparent 82%);
            z-index: 0;
            pointer-events: none;
        }

        /* অ্যানিমেটেড ভায়োলেট ও অ্যাম্বার গ্লো অরব */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(95px);
            z-index: 0;
            pointer-events: none;
            animation: floatOrb 9s ease-in-out infinite alternate;
        }
        .orb-1 {
            width: 330px;
            height: 330px;
            background: rgba(124, 58, 237, 0.22);
            top: -90px;
            right: -70px;
        }
        .orb-2 {
            width: 300px;
            height: 300px;
            background: rgba(245, 158, 11, 0.14);
            bottom: -80px;
            left: -70px;
            animation-delay: 2s;
        }
        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-25px, 25px) scale(1.08); }
        }

        /* মেইন লগইন কার্ড */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 425px;
            animation: cardSlideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes cardSlideUp {
            0% {
                opacity: 0;
                transform: translateY(26px) scale(0.97);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(139, 92, 246, 0.24);
            border-radius: 22px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.08);
            overflow: hidden;
        }
        .top-accent-line {
            height: 4px;
            background: linear-gradient(90deg, #8b5cf6, #f59e0b, #ec4899);
            background-size: 200% 100%;
            animation: gradientMove 4s linear infinite;
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        /* স্কয়ার-রাউন্ডেড লোগো ফ্রেম */
        .logo-box {
            width: 74px;
            height: 74px;
            border-radius: 20px;
            background: #ffffff;
            padding: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 5px rgba(245, 158, 11, 0.16), 0 12px 25px rgba(0, 0, 0, 0.45);
            animation: pulseLogo 3s infinite ease-in-out;
        }
        @keyframes pulseLogo {
            0%, 100% { box-shadow: 0 0 0 5px rgba(245, 158, 11, 0.16), 0 12px 25px rgba(0, 0, 0, 0.45); }
            50% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0.24), 0 12px 30px rgba(139, 92, 246, 0.35); }
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.6px;
            padding: 4px 12px;
            border-radius: 8px;
            text-transform: uppercase;
        }

        /* ইনপুট ফিল্ড ডিজাইন */
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 7px;
        }
        .royal-input-group {
            background: rgba(10, 9, 20, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            transition: all 0.25s ease;
            overflow: hidden;
        }
        .royal-input-group:focus-within {
            border-color: var(--accent-amber);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.18);
            transform: translateY(-1px);
        }
        .royal-input-group .input-group-text {
            background: transparent;
            border: none;
            color: #fbbf24;
            padding-left: 15px;
        }
        .royal-input-group .form-control {
            background: transparent;
            border: none;
            color: #ffffff;
            padding: 12px 14px;
            font-size: 0.95rem;
        }
        .royal-input-group .form-control::placeholder {
            color: #64748b;
            font-size: 0.88rem;
        }
        .royal-input-group .form-control:focus {
            box-shadow: none;
        }
        .toggle-pass-btn {
            background: transparent;
            border: none;
            color: #64748b;
            padding-right: 15px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .toggle-pass-btn:hover {
            color: #fbbf24;
        }

        /* ভায়োলেট-গোল্ডেন লগইন বাটন */
        .btn-royal-login {
            background: linear-gradient(135deg, #7c3aed 0%, #d97706 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.96rem;
            padding: 13px 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.35);
        }
        .btn-royal-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(217, 119, 6, 0.42);
            color: #ffffff;
        }
        .btn-royal-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.28), transparent);
            transition: left 0.6s ease;
        }
        .btn-royal-login:hover::after {
            left: 130%;
        }
    </style>
</head>
<body>

<div class="dot-pattern"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="login-wrapper">
    <div class="login-card">
        <div class="top-accent-line"></div>

        <div class="p-4 p-sm-5">
            <!-- লোগো ও হেডার -->
            <div class="text-center mb-4">
                <a href="../public_notice/index.php" class="text-decoration-none d-inline-block mb-3" title="Go to Student Portal">
                    <div class="logo-box">
                        <img src="../assets/images/dgpi_logo.png" alt="DGPI Logo" style="width: 56px; height: 56px; object-fit: contain;">
                    </div>
                </a>
                <div class="mb-2">
                    <span class="admin-badge"><i class="fa-solid fa-shield-halved"></i> Admin Panel</span>
                </div>
                <h4 class="fw-extrabold text-white mb-1">CRIMS Admin Login</h4>
                <p class="text-secondary small mb-0">Dhaka Government Polytechnic Institute</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-2 px-3 rounded-3 small text-center mb-3">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group royal-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group royal-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="toggle-pass-btn" onclick="togglePassword()" aria-label="Toggle Password">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-royal-login w-100">
                    <span>Sign In</span> <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- নিচে ছোট ফুটার -->
    <div class="text-center mt-3">
        <small class="text-secondary" style="font-size: 0.75rem;">
            &copy; <?= date('Y') ?> CRIMS - Dhaka Government Polytechnic Institute
        </small>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>