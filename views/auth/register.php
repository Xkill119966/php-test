<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Register
                </h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <form method="POST" action="/register" id="registerForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        <div class="invalid-feedback" id="username-error"></div>
                        <div class="form-text">Username must be at least 3 characters long and can only contain letters, numbers, and underscores.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback" id="password-error"></div>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback" id="confirm_password-error"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>
                            Register
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0">Already have an account?</p>
                    <a href="/login" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sign-in-alt me-1"></i>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
    
    function validateForm() {
        let isValid = true;
        
        // Reset previous errors
        clearErrors();
        
        // Validate username
        const username = document.getElementById('username').value.trim();
        if (!username) {
            showError('username', 'Username is required');
            isValid = false;
        } else if (username.length < 3) {
            showError('username', 'Username must be at least 3 characters long');
            isValid = false;
        } else if (username.length > 100) {
            showError('username', 'Username must not exceed 100 characters');
            isValid = false;
        } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            showError('username', 'Username can only contain letters, numbers, and underscores');
            isValid = false;
        }
        
        // Validate email
        const email = document.getElementById('email').value.trim();
        if (!email) {
            showError('email', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('email', 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate password
        const password = document.getElementById('password').value;
        if (!password) {
            showError('password', 'Password is required');
            isValid = false;
        } else if (password.length < 6) {
            showError('password', 'Password must be at least 6 characters long');
            isValid = false;
        }
        
        // Validate confirm password
        const confirmPassword = document.getElementById('confirm_password').value;
        if (!confirmPassword) {
            showError('confirm_password', 'Password confirmation is required');
            isValid = false;
        } else if (password !== confirmPassword) {
            showError('confirm_password', 'Passwords do not match');
            isValid = false;
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + '-error');
        
        field.classList.add('is-invalid');
        errorDiv.textContent = message;
    }
    
    function clearErrors() {
        const fields = ['username', 'email', 'password', 'confirm_password'];
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            const errorDiv = document.getElementById(fieldId + '-error');
            
            field.classList.remove('is-invalid');
            errorDiv.textContent = '';
        });
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
