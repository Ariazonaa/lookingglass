<?php declare(strict_types=1);
/**
 * Hybula Looking Glass
 *
 * Provides UI and input for the looking glass backend.
 *
 * @copyright 2024 Ariazonaa
 * @license Mozilla Public License 2.0
 * @version 0.1
 * @since File available since release 0.1
 * @link https://github.com/ariazonaa/looking-glass
 */

require __DIR__.'/bootstrap.php';

use Ariazonaa\LookingGlass;

$errorMessage = null;
$output = null;
if (!empty($_POST)) {
    if (!isset($_POST['csrfToken']) || !isset($_SESSION[LookingGlass::SESSION_CSRF]) || ($_POST['csrfToken'] !== $_SESSION[LookingGlass::SESSION_CSRF])) {
        $errorMessage = 'Missing or incorrect CSRF token.';
    } elseif (!isset($_POST['backendMethod']) || !isset($_POST['targetHost'])) {
        $errorMessage = 'Unsupported POST received.';
    } elseif (!isset($_POST['checkTerms'])) {
        $errorMessage = 'You must agree with the Terms of Service.';
    } elseif (!in_array($_POST['backendMethod'], ['ping', 'traceroute', 'mtr', 'ping6', 'traceroute6', 'mtr6'])) {
        $errorMessage = 'Unsupported backend method. Please ensure you are using a valid command (ping, traceroute, mtr, ping6, traceroute6, mtr6).';
    } else {
        $_SESSION[LookingGlass::SESSION_TARGET_METHOD] = $_POST['backendMethod'];
        $_SESSION[LookingGlass::SESSION_TARGET_HOST]   = $_POST['targetHost'];
        $targetHost = $_POST['targetHost'];
        if (in_array($_POST['backendMethod'], ['ping', 'mtr', 'traceroute'])) {
            if (!LookingGlass::isValidIpv4($_POST['targetHost']) &&
                !$targetHost = LookingGlass::isValidHost($_POST['targetHost'], LookingGlass::IPV4)
            ) {
                $errorMessage = 'No valid IPv4 provided.';
            }
        }

        if (in_array($_POST['backendMethod'], ['ping6', 'mtr6', 'traceroute6'])) {
            if (!LookingGlass::isValidIpv6($_POST['targetHost']) &&
                !$targetHost = LookingGlass::isValidHost($_POST['targetHost'], LookingGlass::IPV6)
            ) {
                $errorMessage = 'No valid IPv6 provided.';
            }
        }

        if (!$errorMessage) {
            $_SESSION[LookingGlass::SESSION_TARGET_HOST]  = $targetHost;
            $_SESSION[LookingGlass::SESSION_TOS_CHECKED]  = true;
            $_SESSION[LookingGlass::SESSION_CALL_BACKEND] = true;

            // Execute the command and capture the output
            $command = '';
            switch ($_POST['backendMethod']) {
                case 'ping':
                    $command = escapeshellcmd("ping -c 4 " . $targetHost);
                    break;
                case 'traceroute':
                    $command = escapeshellcmd("traceroute " . $targetHost);
                    break;
                case 'mtr':
                    $command = escapeshellcmd("mtr -r " . $targetHost);
                    break;
                case 'ping6':
                    $command = escapeshellcmd("ping6 -c 4 " . $targetHost);
                    break;
                case 'traceroute6':
                    $command = escapeshellcmd("traceroute6 " . $targetHost);
                    break;
                case 'mtr6':
                    $command = escapeshellcmd("mtr -6 -r " . $targetHost);
                    break;
            }
            $output = shell_exec($command);
        }
    }
}

$templateData['session_target']       = $_SESSION[LookingGlass::SESSION_TARGET_HOST] ?? '';
$templateData['session_method']       = $_SESSION[LookingGlass::SESSION_TARGET_METHOD] ?? '';
$templateData['session_call_backend'] = $_SESSION[LookingGlass::SESSION_CALL_BACKEND] ?? false;
$templateData['session_tos_checked']  = isset($_SESSION[LookingGlass::SESSION_TOS_CHECKED]) ? ' checked' : '';
$templateData['error_message']        = $errorMessage;
$templateData['output']               = $output;

if (LG_BLOCK_CUSTOM) {
    if (defined('LG_CUSTOM_PHP') && file_exists(LG_CUSTOM_PHP)) {
        include LG_CUSTOM_PHP;
    }

    if (defined('LG_CUSTOM_HTML') && file_exists(LG_CUSTOM_HTML)) {
        ob_start();
        include LG_CUSTOM_HTML;
        $templateData['custom_html'] = ob_get_clean();
    }

    if (defined('LG_CUSTOM_HEADER_PHP') && file_exists(LG_CUSTOM_HEADER_PHP)) {
        ob_start();
        include LG_CUSTOM_HEADER_PHP;
        $templateData['custom_header'] = ob_get_clean();
    }

    if (defined('LG_CUSTOM_FOOTER_PHP') && file_exists(LG_CUSTOM_FOOTER_PHP)) {
        ob_start();
        include LG_CUSTOM_FOOTER_PHP;
        $templateData['custom_footer'] = ob_get_clean();
    }
}

if (LG_CHECK_LATENCY) {
    $templateData['latency'] = LookingGlass::getLatency();
}

$templateData['csrfToken'] = $_SESSION[LookingGlass::SESSION_CSRF] = bin2hex(random_bytes(12));
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="LookingGlass | example.com - Services by Example" />
    <title>LookingGlass | example.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-rCsf8Z+8BJOf6d2GzN12Mr0slvCEmI7MbN7s7mnKbTFWy24LFPx9IuYpbLgA0ShqEXZPOw7yPpbu1LlC8PyyHg==" crossorigin="anonymous" />
    <style>
        body {
            background: #1f2937;
            color: #e5e7eb;
            font-family: 'Roboto', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 1200px;
            width: 100%;
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .dashboard-header img {
            height: 60px;
        }
        .dashboard-header h1 {
            font-size: 2.5rem;
            margin: 0.5rem 0 0;
            font-weight: 700;
            color: #3b82f6;
        }
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: #111827;
            color: #e5e7eb;
            border-radius: 15px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
        }
        .card h2 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .input-group {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }
        .btn-primary, .btn-secondary {
            background: #3b82f6;
            border: none;
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            border-radius: 5px;
            color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        footer {
            text-align: center;
            margin-top: 2rem;
            color: #9ca3af;
            font-size: 1rem;
            padding: 1rem;
            background: #111827;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }
        footer a {
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.3s;
        }
        footer a:hover {
            color: #2563eb;
        }
        .output-tab {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #1f2937;
            color: #3b82f6;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }
        .output-tab pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <header class="dashboard-header">
        <a href="https://example.com" target="_blank">
            <img src="https://cdn.example.com/logo.png" alt="Company Logo">
        </a>
        <h1>Looking Glass</h1>
    </header>
    <div class="dashboard">
        <div class="card">
            <h2>Network Information</h2>
            <p>Test IPv4: <span class="fw-bold">192.0.2.1</span></p>
            <p>Test IPv6: <span class="fw-bold">2001:db8::1</span></p>
            <p>Test files: <a href="#" class="text-decoration-none">100M</a> / <a href="#" class="text-decoration-none">1GB</a> / <a href="#" class="text-decoration-none">10GB</a></p>
        </div>
        <div class="card">
            <h2>Looking Glass</h2>
            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrfToken" value="<?php echo $templateData['csrfToken'] ?>">
                <div class="input-group mb-4">
                    <input type="text" class="form-control" placeholder="Enter an IP address or hostname" name="targetHost" value="<?php echo $templateData['session_target'] ?>" required>
                </div>
                <div class="form-check mb-4">
                    <input type="checkbox" id="checkTerms" name="checkTerms" class="form-check-input" required>
                    <label for="checkTerms" class="form-check-label">I agree with the <a href="term.html" target="_blank">Terms of Service</a></label>
                </div>
                <div class="button-group">
                    <button class="btn btn-primary" type="submit" name="backendMethod" value="ping">Ping</button>
                    <button class="btn btn-primary" type="submit" name="backendMethod" value="traceroute">Traceroute</button>
                    <button class="btn btn-primary" type="submit" name="backendMethod" value="mtr">MTR</button>
                    <button class="btn btn-secondary" type="submit" name="backendMethod" value="ping6">Ping6</button>
                    <button class="btn btn-secondary" type="submit" name="backendMethod" value="traceroute6">Traceroute6</button>
                    <button class="btn btn-secondary" type="submit" name="backendMethod" value="mtr6">MTR6</button>
                </div>
            </form>
            <?php if ($templateData['error_message']): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo $templateData['error_message'] ?>
                </div>
            <?php endif ?>
        </div>
    </div>
    <?php if ($templateData['output']): ?>
    <div class="output-tab">
        <h2>Command Output</h2>
        <pre><?php echo htmlspecialchars($templateData['output']); ?></pre>
    </div>
    <?php endif; ?>
</div>
<footer>
    Powered by <a href="https://example.com" target="_blank">example.com</a>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
