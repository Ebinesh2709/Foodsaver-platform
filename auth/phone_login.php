<?php
session_start();
define('APP_RUNNING', true);
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSaver — Phone Login</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍱</text></svg>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style_v2.css">
    <style>
        body { padding-top: 0; min-height: 100vh; display: flex; flex-direction: column; background: linear-gradient(135deg, var(--fs-bg) 0%, #f4f1ea 100%); }
        .auth-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
    </style>
</head>
<body>

<nav class="fs-navbar navbar" style="position:relative; backdrop-filter:none;">
    <div class="container">
        <a class="navbar-brand" href="../index.php">🍱 FoodSaver</a>
        <a href="login.php" class="btn-nav-outline">Back to Email Login</a>
    </div>
</nav>

<div class="auth-wrap">
    <div style="width:100%; max-width:420px;">
        <div class="fs-form-card" id="step-phone">
            <div class="text-center mb-4">
                <div style="font-size:2.5rem; margin-bottom:0.5rem;">📱</div>
                <h1 class="fs-form-heading">Login with Phone</h1>
                <p class="fs-form-sub">Enter your phone number to receive a secure code.</p>
            </div>
            
            <div id="phone-alert" class="alert alert-danger d-none"></div>

            <div class="mb-4">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" id="phone" class="form-control" placeholder="+94 77 000 0000" required autofocus>
            </div>
            <button id="btn-send-otp" class="btn btn-fs-primary w-100 py-2">
                Send OTP
            </button>
        </div>

        <div class="fs-form-card d-none" id="step-otp">
            <div class="text-center mb-4">
                <div style="font-size:2.5rem; margin-bottom:0.5rem;">🔑</div>
                <h1 class="fs-form-heading">Enter OTP</h1>
                <p class="fs-form-sub" id="otp-sub-text">We sent a 6-digit code to your phone.</p>
            </div>
            
            <div id="otp-alert" class="alert alert-danger d-none"></div>

            <div class="mb-4">
                <label for="otp" class="form-label">6-Digit Code</label>
                <input type="text" id="otp" class="form-control text-center" style="letter-spacing: 0.5rem; font-size: 1.5rem;" maxlength="6" placeholder="000000" required>
            </div>
            <button id="btn-verify-otp" class="btn btn-fs-primary w-100 py-2">
                Verify & Login
            </button>
        </div>
    </div>
</div>

<script>
let currentPhone = '';

document.getElementById('btn-send-otp').addEventListener('click', function() {
    const phoneInput = document.getElementById('phone').value.trim();
    if (!phoneInput) return alert('Enter phone number');
    
    this.disabled = true;
    this.innerText = 'Sending...';
    const alertBox = document.getElementById('phone-alert');
    alertBox.classList.add('d-none');
    
    fetch('send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phone=' + encodeURIComponent(phoneInput)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentPhone = phoneInput;
            document.getElementById('step-phone').classList.add('d-none');
            document.getElementById('step-otp').classList.remove('d-none');
            document.getElementById('otp-sub-text').innerText = 'We simulated sending an SMS to ' + phoneInput + '. (Check the alert below for your code!)';
            
            // SIMULATED SMS DISPLAY
            const otpAlertBox = document.getElementById('otp-alert');
            otpAlertBox.classList.remove('d-none', 'alert-danger');
            otpAlertBox.classList.add('alert-info');
            otpAlertBox.innerHTML = '<strong>SIMULATED SMS:</strong> Your FoodSaver login code is: <b>' + data.simulated_code + '</b>';
            
        } else {
            alertBox.classList.remove('d-none');
            alertBox.innerText = data.error || 'Failed to send OTP';
            document.getElementById('btn-send-otp').disabled = false;
            document.getElementById('btn-send-otp').innerText = 'Send OTP';
        }
    })
    .catch(err => {
        alert('Network error');
        document.getElementById('btn-send-otp').disabled = false;
        document.getElementById('btn-send-otp').innerText = 'Send OTP';
    });
});

document.getElementById('btn-verify-otp').addEventListener('click', function() {
    const otpInput = document.getElementById('otp').value.trim();
    if (otpInput.length !== 6) return alert('Enter a 6-digit code');
    
    this.disabled = true;
    this.innerText = 'Verifying...';
    
    fetch('verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phone=' + encodeURIComponent(currentPhone) + '&code=' + encodeURIComponent(otpInput)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            const otpAlertBox = document.getElementById('otp-alert');
            otpAlertBox.classList.remove('d-none', 'alert-info');
            otpAlertBox.classList.add('alert-danger');
            otpAlertBox.innerText = data.error || 'Invalid Code';
            document.getElementById('btn-verify-otp').disabled = false;
            document.getElementById('btn-verify-otp').innerText = 'Verify & Login';
        }
    })
    .catch(err => {
        alert('Network error');
        document.getElementById('btn-verify-otp').disabled = false;
        document.getElementById('btn-verify-otp').innerText = 'Verify & Login';
    });
});
</script>
</body>
</html>
