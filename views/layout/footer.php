    </main>

    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Vending Machine System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation helper
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                const errorDiv = input.parentNode.querySelector('.error-message');
                if (errorDiv) errorDiv.remove();

                if (!input.value.trim()) {
                    isValid = false;
                    const error = document.createElement('div');
                    error.className = 'error-message';
                    error.textContent = 'This field is required';
                    input.parentNode.appendChild(error);
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            });

            return isValid;
        }

        // Price validation
        function validatePrice(input) {
            const value = parseFloat(input.value);
            const errorDiv = input.parentNode.querySelector('.error-message');
            if (errorDiv) errorDiv.remove();

            if (isNaN(value) || value <= 0) {
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'Price must be a positive number';
                input.parentNode.appendChild(error);
                input.classList.add('is-invalid');
                return false;
            }
            
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            return true;
        }

        // Quantity validation
        function validateQuantity(input) {
            const value = parseInt(input.value);
            const errorDiv = input.parentNode.querySelector('.error-message');
            if (errorDiv) errorDiv.remove();

            if (isNaN(value) || value < 0) {
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'Quantity must be a non-negative number';
                input.parentNode.appendChild(error);
                input.classList.add('is-invalid');
                return false;
            }
            
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            return true;
        }

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
