<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found — M-Pesa</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.10);
            max-width: 400px;
            width: 100%;
            overflow: hidden;
            text-align: center;
        }
        .card-header {
            background: linear-gradient(135deg, #006633 0%, #00a651 100%);
            padding: 36px 32px 32px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: rgba(255,255,255,0.25);
            line-height: 1;
            letter-spacing: -4px;
        }
        .card-header h1 {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            margin-top: 8px;
        }
        .card-header p {
            color: rgba(255,255,255,0.75);
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.5;
        }
        .card-body {
            padding: 28px 32px 32px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: linear-gradient(135deg, #006633, #00a651);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            margin-bottom: 12px;
            width: 100%;
            justify-content: center;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: transparent;
            color: #006633;
            border: 1.5px solid #c8e6d4;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
            justify-content: center;
        }
        .btn-outline:hover { background: #f0faf5; }
        .divider {
            font-size: 11px;
            color: #ccc;
            margin: 4px 0;
        }
    </style>
</head>
<body>
<?php http_response_code(404); ?>
<div class="card">
    <div class="card-header">
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been moved.</p>
    </div>
    <div class="card-body">
        <a href="/" class="btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11l2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/>
            </svg>
            Go to Payment Page
        </a>
        <div class="divider">or</div>
        <a href="/admin.php" class="btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
            </svg>
            Transaction Log
        </a>
    </div>
</div>
</body>
</html>
