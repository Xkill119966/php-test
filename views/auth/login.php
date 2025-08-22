<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Login
                </h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <form method="POST" action="/login" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        <div class="invalid-feedback" id="username-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback" id="password-error"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0">Don't have an account?</p>
                    <a href="/register" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-user-plus me-1"></i>
                        Register
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    
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
        }
        
        // Validate password
        const password = document.getElementById('password').value;
        if (!password) {
            showError('password', 'Password is required');
            isValid = false;
        }
        
        return isValid;
    }
    
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId + '-error');
        
        field.classList.add('is-invalid');
        errorDiv.textContent = message;
    }
    
    function clearErrors() {
        const fields = ['username', 'password'];
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
