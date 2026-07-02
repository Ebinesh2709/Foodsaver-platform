<?php
session_start();
$plan = $_GET['plan'] ?? 'starter';
$plan_name = ($plan === 'pro') ? 'Business Pro' : 'Business Starter';
$plan_price = ($plan === 'pro') ? 'LKR 2,490/mo' : 'LKR 990/mo';

$page_title = "Checkout - $plan_name";
$active_page = 'about';
require_once 'includes/header.php';
?>

<section class="py-5" style="background: var(--fs-bg); min-height: 80vh;">
    <div class="container" style="max-width: 900px;">
        <div class="text-center mb-5 fade-in-up visible">
            <h1 class="fw-bold">Complete Your Purchase</h1>
            <p class="text-muted">You are subscribing to the <strong><?= htmlspecialchars($plan_name) ?></strong> plan.</p>
        </div>

        <div class="row g-4 fade-in-up visible">
            <!-- Order Summary -->
            <div class="col-md-5 order-md-2 mb-4">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Your cart</span>
                    <span class="badge rounded-pill bg-success">1</span>
                </h4>
                <ul class="list-group mb-3 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <li class="list-group-item d-flex justify-content-between lh-sm p-4">
                        <div>
                            <h6 class="my-0 fw-bold"><?= htmlspecialchars($plan_name) ?></h6>
                            <small class="text-muted">Monthly Subscription</small>
                        </div>
                        <span class="text-muted"><?= htmlspecialchars($plan_price) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between bg-light p-4">
                        <div class="text-success">
                            <h6 class="my-0">Beta Discount</h6>
                            <small>BETA_LAUNCH</small>
                        </div>
                        <span class="text-success">−LKR 0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between p-4 bg-dark text-white">
                        <span>Total (LKR)</span>
                        <strong><?= htmlspecialchars($plan_price) ?></strong>
                    </li>
                </ul>
            </div>

            <!-- Payment Form -->
            <div class="col-md-7 order-md-1">
                <div class="fs-card p-4 shadow-sm">
                    <h4 class="mb-4 fw-bold">Payment Details</h4>
                    <form id="payment-form" action="auth/register.php" method="get">
                        <input type="hidden" name="payment" value="success">
                        <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-12 mb-3">
                                <label for="cc-name" class="form-label">Name on card</label>
                                <input type="text" class="form-control form-control-lg" id="cc-name" placeholder="John Doe" required>
                                <small class="text-muted">Full name as displayed on card</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="cc-number" class="form-label">Credit card number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-credit-card"></i></span>
                                    <input type="text" class="form-control form-control-lg border-start-0" id="cc-number" placeholder="0000 0000 0000 0000" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cc-expiration" class="form-label">Expiration</label>
                                <input type="text" class="form-control form-control-lg" id="cc-expiration" placeholder="MM/YY" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="cc-cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control form-control-lg" id="cc-cvv" placeholder="123" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <button class="btn btn-fs-primary w-100 btn-lg d-flex justify-content-center align-items-center" type="submit" id="btn-pay">
                            <span id="btn-text">Pay <?= htmlspecialchars($plan_price) ?></span>
                            <div class="spinner-border spinner-border-sm text-light ms-2 d-none" id="btn-spinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-pay');
    const text = document.getElementById('btn-text');
    const spinner = document.getElementById('btn-spinner');
    
    // Simulate processing
    btn.disabled = true;
    text.textContent = 'Processing...';
    spinner.classList.remove('d-none');
    
    setTimeout(() => {
        text.textContent = 'Payment Successful! Redirecting...';
        spinner.classList.add('d-none');
        btn.classList.remove('btn-fs-primary');
        btn.classList.add('btn-success');
        
        // Redirect to register
        setTimeout(() => {
            e.target.submit();
        }, 1500);
    }, 2000);
});
</script>

<?php require_once 'includes/footer.php'; ?>
