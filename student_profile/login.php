<?php
session_start();
require_once("../config/database.php");

if (isset($_SESSION['student_portal_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll     = trim($_POST['roll'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($roll) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE roll = ? LIMIT 1");
        $stmt->execute([$roll]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            $is_valid = false;

            if (!empty($student['password'])) {
                if (password_verify($password, $student['password'])) {
                    $is_valid = true;
                }
            } else {
                if (!empty($student['registration']) && $password === trim($student['registration'])) {
                    $is_valid = true;
                }
            }

            if ($is_valid) {
                $_SESSION['student_portal_id'] = $student['id'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid User ID or Password!";
            }
        } else {
            $error = "Invalid User ID or Password!";
        }
    } else {
        $error = "Please enter both User ID and Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | CST - DGPI</title>
    <link rel="icon" type="image/png" href="../assets/images/dgpi_logo.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #070d12;
            --card-bg: rgba(16, 23, 31, 0.85);
            --cyan-accent: #38bdf8;
            --teal-accent: #14b8a6;
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

        /* কম্পিউটার ডিপার্টমেন্টের আধুনিক টেক-গ্রিড ব্যাকগ্রাউন্ড */
        .tech-grid {
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(56, 189, 248, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(56, 189, 248, 0.04) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(circle at center, black 40%, transparent 85%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 85%);
            z-index: 0;
            pointer-events: none;
        }

        /* অ্যানিমেটেড ব্যাকগ্রাউন্ড গ্লো অরব */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            z-index: 0;
            pointer-events: none;
            animation: floatOrb 9s ease-in-out infinite alternate;
        }
        .orb-1 {
            width: 320px;
            height: 320px;
            background: rgba(14, 165, 233, 0.18);
            top: -80px;
            left: -80px;
        }
        .orb-2 {
            width: 300px;
            height: 300px;
            background: rgba(20, 184, 166, 0.16);
            bottom: -80px;
            right: -60px;
            animation-delay: 2.5s;
        }
        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 25px) scale(1.1); }
        }

        /* মেইন গ্লাসমরফিজম লগইন কার্ড ও এন্ট্রান্স অ্যানিমেশন */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 430px;
            animation: cardSlideUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes cardSlideUp {
            0% {
                opacity: 0;
                transform: translateY(28px) scale(0.97);
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
            border: 1px solid rgba(56, 189, 248, 0.18);
            border-radius: 26px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.08);
            overflow: hidden;
        }
        .top-accent-line {
            height: 4px;
            background: linear-gradient(90deg, #0ea5e9, #14b8a6, #38bdf8);
            background-size: 200% 100%;
            animation: gradientMove 4s linear infinite;
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        /* লোগো পালস অ্যানিমেশন */
        .logo-ring {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: #ffffff;
            padding: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 6px rgba(56, 189, 248, 0.14), 0 10px 25px rgba(0, 0, 0, 0.4);
            animation: pulseRing 3s infinite ease-in-out;
        }
        @keyframes pulseRing {
            0%, 100% { box-shadow: 0 0 0 6px rgba(56, 189, 248, 0.14), 0 10px 25px rgba(0, 0, 0, 0.4); }
            50% { box-shadow: 0 0 0 11px rgba(20, 184, 166, 0.22), 0 10px 30px rgba(14, 165, 233, 0.3); }
        }

        .dept-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.25);
            color: #7dd3fc;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.6px;
            padding: 4px 12px;
            border-radius: 50px;
            text-transform: uppercase;
        }

        /* কাস্টম ইনপুট ফিল্ড */
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 7px;
        }
        .cyber-input-group {
            background: rgba(9, 14, 22, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            transition: all 0.25s ease;
            overflow: hidden;
        }
        .cyber-input-group:focus-within {
            border-color: var(--cyan-accent);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
            transform: translateY(-1px);
        }
        .cyber-input-group .input-group-text {
            background: transparent;
            border: none;
            color: #38bdf8;
            padding-left: 16px;
        }
        .cyber-input-group .form-control {
            background: transparent;
            border: none;
            color: #ffffff;
            padding: 12px 14px;
            font-size: 0.95rem;
        }
        .cyber-input-group .form-control::placeholder {
            color: #64748b;
            font-size: 0.88rem;
        }
        .cyber-input-group .form-control:focus {
            box-shadow: none;
        }
        .toggle-pass-btn {
            background: transparent;
            border: none;
            color: #64748b;
            padding-right: 16px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .toggle-pass-btn:hover {
            color: #38bdf8;
        }

        /* প্রিমিয়াম লগইন বাটন ও শাইন অ্যানিমেশন */
        .btn-cyber-login {
            background: linear-gradient(135deg, #0ea5e9 0%, #0d9488 100%);
            border: none;
            border-radius: 14px;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.96rem;
            padding: 13px 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
        }
        .btn-cyber-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(20, 184, 166, 0.45);
            color: #ffffff;
        }
        .btn-cyber-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.28), transparent);
            transition: left 0.6s ease;
        }
        .btn-cyber-login:hover::after {
            left: 130%;
        }
    </style>
</head>
<body>

<div class="tech-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="login-wrapper">
    <div class="login-card">
        <div class="top-accent-line"></div>

        <div class="p-4 p-sm-5">
            <!-- লোগো ও ডিপার্টমেন্ট হেডার -->
            <div class="text-center mb-4">
                <div class="logo-ring mb-3">
                    <img src="../assets/images/dgpi_logo.png" alt="DGPI Logo" style="width: 58px; height: 58px; object-fit: contain;">
                </div>
                <div class="mb-2">
                    <span class="dept-pill"><i class="fa-solid fa-code"></i> Department of CST</span>
                </div>
                <h4 class="fw-extrabold text-white mb-1">Student Profile Login</h4>
                <p class="text-secondary small mb-0">Dhaka Government Polytechnic Institute</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-2 px-3 rounded-3 small text-center mb-3">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <!-- User ID -->
                <div class="mb-3">
                    <label class="form-label">User ID</label>
                    <div class="input-group cyber-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user-astronaut"></i></span>
                        <input type="text" name="roll" class="form-control" placeholder="Enter your User ID" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group cyber-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter your Password" required>
                        <button type="button" class="toggle-pass-btn" onclick="togglePassword()" aria-label="Toggle Password">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-cyber-login w-100">
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
    const input = document.getElementById('passwordInput');
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