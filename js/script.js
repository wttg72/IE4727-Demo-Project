// StyleHub - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    // Form validation function
    function validateForm(form) {
        let isValid = true;
        
        // Validate name
        const nameInput = form.querySelector('#name');
        if (nameInput) {
            if (nameInput.value.trim() === '') {
                showError(nameInput, 'Name is required');
                isValid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(nameInput.value.trim())) {
                showError(nameInput, 'Name should contain only letters');
                isValid = false;
            } else {
                clearError(nameInput);
            }
        }
        
        // Validate email
        const emailInput = form.querySelector('#email');
        if (emailInput) {
            if (emailInput.value.trim() === '') {
                showError(emailInput, 'Email is required');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
                showError(emailInput, 'Please enter a valid email address');
                isValid = false;
            } else {
                clearError(emailInput);
            }
        }
        
        // Validate phone
        const phoneInput = form.querySelector('#phone');
        if (phoneInput) {
            if (phoneInput.value.trim() === '') {
                showError(phoneInput, 'Phone number is required');
                isValid = false;
            } else if (!/^\d{8,12}$/.test(phoneInput.value.replace(/[\s-]/g, ''))) {
                showError(phoneInput, 'Please enter a valid phone number');
                isValid = false;
            } else {
                clearError(phoneInput);
            }
        }
        
        // Validate address
        const addressInput = form.querySelector('#address');
        if (addressInput) {
            if (addressInput.value.trim() === '') {
                showError(addressInput, 'Address is required');
                isValid = false;
            } else if (addressInput.value.trim().length < 10) {
                showError(addressInput, 'Please enter a complete address');
                isValid = false;
            } else {
                clearError(addressInput);
            }
        }
        
        // Validate password
        const passwordInput = form.querySelector('#password');
        if (passwordInput) {
            if (passwordInput.value === '') {
                showError(passwordInput, 'Password is required');
                isValid = false;
            } else if (passwordInput.value.length < 8) {
                showError(passwordInput, 'Password must be at least 8 characters');
                isValid = false;
            } else {
                clearError(passwordInput);
            }
        }
        
        // Validate confirm password
        const confirmPasswordInput = form.querySelector('#confirm-password');
        if (confirmPasswordInput && passwordInput) {
            if (confirmPasswordInput.value === '') {
                showError(confirmPasswordInput, 'Please confirm your password');
                isValid = false;
            } else if (confirmPasswordInput.value !== passwordInput.value) {
                showError(confirmPasswordInput, 'Passwords do not match');
                isValid = false;
            } else {
                clearError(confirmPasswordInput);
            }
        }
        
        return isValid;
    }
    
    // Show error message
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        
        input.classList.add('is-invalid');
        
        if (errorElement) {
            errorElement.textContent = message;
        } else {
            const error = document.createElement('div');
            error.className = 'error-message';
            error.textContent = message;
            formGroup.appendChild(error);
        }
    }
    
    // Clear error message
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        
        input.classList.remove('is-invalid');
        
        if (errorElement) {
            errorElement.textContent = '';
        }
    }
    
    // Add submit event listeners to forms
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!validateForm(this)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
    
    // Product quantity increment/decrement
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        const decrementBtn = input.previousElementSibling;
        const incrementBtn = input.nextElementSibling;
        
        if (decrementBtn && incrementBtn) {
            decrementBtn.addEventListener('click', () => {
                if (input.value > 1) {
                    input.value = parseInt(input.value) - 1;
                    updateCartTotal();
                }
            });
            
            incrementBtn.addEventListener('click', () => {
                input.value = parseInt(input.value) + 1;
                updateCartTotal();
            });
            
            input.addEventListener('change', () => {
                if (input.value < 1 || isNaN(input.value)) {
                    input.value = 1;
                }
                updateCartTotal();
            });
        }
    });
    
    // Update cart total
    function updateCartTotal() {
        const cartItems = document.querySelectorAll('.cart-item');
        let total = 0;
        
        cartItems.forEach(item => {
            const price = parseFloat(item.querySelector('.item-price').getAttribute('data-price'));
            const quantity = parseInt(item.querySelector('.quantity-input').value);
            const itemTotal = price * quantity;
            
            item.querySelector('.item-total').textContent = '$' + itemTotal.toFixed(2);
            total += itemTotal;
        });
        
        const cartTotal = document.querySelector('.cart-total');
        if (cartTotal) {
            cartTotal.textContent = '$' + total.toFixed(2);
        }
    }
    
    // Initialize cart total on page load
    updateCartTotal();
    
    // Add to cart functionality for product cards (not for the form submit button)
    const addToCartButtons = document.querySelectorAll('.product-card .add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            const productPrice = this.getAttribute('data-product-price');
            
            if (productName) {
                // Only show alert if product name exists (for quick add buttons, not form submit)
                alert(`Added ${productName} to cart!`);
            }
        });
    });
    
    // Check for added parameter in URL to show success message
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('added') === '1') {
        // Get product name from page title or data attribute
        const productName = document.querySelector('.product-title')?.textContent || 'Product';
        alert(`Added ${productName} to cart!`);
    }
});
