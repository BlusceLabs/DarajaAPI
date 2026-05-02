<?php
// login.php — Admin login page

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in — go straight to admin
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
    }

    $username     = trim($_POST['username'] ?? '');
    $password     = $_POST['password'] ?? '';
    $cfgUser      = defined('ADMIN_USERNAME')      ? ADMIN_USERNAME      : '';
    $cfgHash      = defined('ADMIN_PASSWORD_HASH') ? ADMIN_PASSWORD_HASH : '';
    $isPlaceholder = ($cfgHash === 'YOUR_ADMIN_PASSWORD_HASH_HERE' || $cfgHash === '');

    if ($isPlaceholder) {
        $error = 'Admin credentials are not configured. Set ADMIN_USERNAME and ADMIN_PASSWORD_HASH in config.php.';
    } elseif ($username === $cfgUser && password_verify($password, $cfgHash)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $next = $_GET['next'] ?? '/admin.php';
        // Open-redirect guard: must start with exactly one slash (not //scheme-relative)
        if (!preg_match('/^\/[a-zA-Z0-9_\-\.\/\?=&%]*$/', $next) || str_starts_with($next, '//')) {
            $next = '/admin.php';
        }
        header('Location: ' . $next);
        exit;
    } else {
        // Constant-time comparison already done inside password_verify, add a small
        // deliberate delay to blunt rapid brute-force attempts.
        usleep(300000);
        $error = 'Invalid username or password.';
    }
}

$next = $_GET['next'] ?? '/admin.php';
if (!preg_match('/^\/[a-zA-Z0-9_\-\.\/\?=&%]*$/', $next) || str_starts_with($next, '//')) {
    $next = '/admin.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <style>
        :root {
            --bg: #f0f4f0;
            --surface: #fff;
            --border: #e0e6e0;
            --text: #222;
            --text-2: #555;
            --text-3: #888;
            --shadow: rgba(0,0,0,0.07);
            --input-bg: #fafafa;
            --error-bg: #fdf2f2;
            --error-border: #f5c0c0;
            --error-text: #8b2020;
        }
        :root[data-theme="dark"] {
            --bg: #0d1117;
            --surface: #161b22;
            --border: #30363d;
            --text: #e6edf3;
            --text-2: #adbac7;
            --text-3: #768390;
            --shadow: rgba(0,0,0,0.35);
            --input-bg: #0d1117;
            --error-bg: #2d1111;
            --error-border: #5c2020;
            --error-text: #f97171;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
        }

        .card {
            background: var(--surface);
            border-radius: 18px;
            box-shadow: 0 4px 28px var(--shadow);
            padding: 40px 36px;
            width: 100%;
            max-width: 380px;
        }

        .logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo h1 {
            font-size: 20px;
            font-weight: 800;
            color: #006633;
            margin-bottom: 4px;
        }
        :root[data-theme="dark"] .logo h1 { color: #3fb950; }
        .logo p {
            font-size: 13px;
            color: var(--text-3);
        }

        .error-msg {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-2);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 11px 13px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text);
            background: var(--input-bg);
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #00a651;
        }

        .submit-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #006633, #00a651);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            transition: opacity 0.2s;
        }
        .submit-btn:hover { opacity: 0.88; }

        .theme-btn {
            position: fixed; bottom: 20px; right: 20px;
            width: 40px; height: 40px; border-radius: 50%;
            border: 1.5px solid var(--border);
            background: var(--surface); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 12px var(--shadow); z-index: 999;
            font-size: 17px; transition: all 0.2s;
        }
        .theme-btn:hover { transform: scale(1.1); }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">
        <h1>Admin Panel</h1>
        <p>Sign in to view transactions</p>
    </div>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login.php?next=<?= urlencode($next) ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                autocomplete="username"
                required
                autofocus
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            >
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >
        </div>
        <button type="submit" class="submit-btn">Sign In</button>
    </form>
</div>

<button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle dark mode"></button>
<script>
    function toggleTheme() {
        const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        updateThemeIcon();
    }
    function updateThemeIcon() {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.getElementById('themeBtn').textContent = dark ? '☀️' : '🌙';
    }
    updateThemeIcon();
</script>
</body>
</html>
