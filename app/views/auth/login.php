<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-center align-items-center fade-in-up" style="min-height: 85vh;">
    <div class="neu-card p-5" style="max-width: 450px; width: 100%; border-radius: 30px;">
        <div class="text-center mb-5">
            <div class="mb-3 d-inline-block rounded-circle d-flex align-items-center justify-content-center mx-auto"
                style="width: 80px; height: 80px; box-shadow: var(--neumorphic-flat); color: var(--primary); font-size: 2rem;">
                <i class="fas fa-chart-line"></i>
            </div>
            <h2 class="text-gradient fw-bold mb-0">AccuBooks</h2>
            <p class="text-muted mt-2">Welcome Back!</p>
        </div>

        <!-- Session Timeout / Security Alerts -->
        <?php if (isset($_SESSION['timeout_message'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-clock me-2"></i>
                <?php echo $_SESSION['timeout_message'];
                unset($_SESSION['timeout_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['security_alert'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-shield-alt me-2"></i>
                <?php echo $_SESSION['security_alert'];
                unset($_SESSION['security_alert']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo APP_URL; ?>/auth/login" method="post">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label for="email" class="form-label ms-2 text-primary fw-bold small text-uppercase">Email
                    Address</label>
                <input type="email" name="email" id="email"
                    class="form-control-neu w-100 <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $data['email']; ?>" placeholder="name@company.com">
                <span class="invalid-feedback d-block ms-2 mt-1"><?php echo $data['email_err']; ?></span>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label ms-2 text-primary fw-bold small text-uppercase">Password</label>
                <input type="password" name="password" id="password"
                    class="form-control-neu w-100 <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $data['password']; ?>" placeholder="Enter your password">
                <span class="invalid-feedback d-block ms-2 mt-1"><?php echo $data['password_err']; ?></span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember"
                        style="box-shadow: var(--neumorphic-pressed); border:none; background-color: var(--bg-color);">
                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                </div>
                <a href="#" class="small text-decoration-none text-secondary">Forgot password?</a>
            </div>

            <button type="submit" class="btn-neu btn-neu-primary w-100 py-3 mb-3">
                Sign In <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">Don't have an account? <a href="#"
                    class="text-primary fw-bold text-decoration-none">Contact Admin</a></small>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>