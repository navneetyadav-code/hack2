document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Password Visibility Toggle
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            
            // Toggle input type
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Update the SVG icon based on state
            if (isPassword) {
                // Eye Off Icon
                toggleBtn.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                `;
            } else {
                // Eye On Icon
                toggleBtn.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                `;
            }
        });
    }

    // 2. Form Submission UX Enhancement (Prevent double submit and show loading)
    const authForm = document.getElementById('authForm');
    const submitBtn = document.getElementById('submitBtn');

    if (authForm && submitBtn) {
        authForm.addEventListener('submit', (e) => {
            // Get the span inside the button
            const btnText = submitBtn.querySelector('span');
            
            // Change button state
            submitBtn.disabled = true;
            btnText.textContent = 'Authenticating...';
            
            // Remove the arrow icon and add a simple loading text effect
            const svgIcon = submitBtn.querySelector('svg');
            if (svgIcon) {
                svgIcon.style.display = 'none';
            }
            
            // Note: Form will proceed to submit normally to check-login.php
        });
    }

})