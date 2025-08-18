document.addEventListener('DOMContentLoaded', function() {
    const formSteps = document.querySelectorAll('.form-step');
    const nextBtns = document.querySelectorAll('.next-btn');
    const prevBtns = document.querySelectorAll('.prev-btn');
    const progressSteps = document.querySelectorAll('.step');
    const form = document.getElementById('multiStepForm');

    let currentStep = 0;

    // Show current step
    function showStep(step) {
        formSteps.forEach((formStep, index) => {
            formStep.classList.toggle('active', index === step);
        });

        progressSteps.forEach((progressStep, index) => {
            progressStep.classList.toggle('active', index <= step);
        });
    }

    // Create error message element if it doesn't exist
    function ensureErrorMessageElement(field) {
        let errorMsg = field.parentElement.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('span');
            errorMsg.className = 'error-message';
            field.parentElement.appendChild(errorMsg);
        }
        return errorMsg;
    }

    // Validate current step
    function validateStep(step) {
        const currentStepFields = formSteps[step].querySelectorAll('[required]');
        let isValid = true;

        currentStepFields.forEach(field => {
            // Clear previous error state
            field.classList.remove('invalid');
            const errorMsg = ensureErrorMessageElement(field);
            errorMsg.textContent = '';

            // Check if field is empty
            if (!field.value.trim()) {
                field.classList.add('invalid');
                errorMsg.textContent = 'This field is required';
                isValid = false;
                return;
            }

            // Field-specific validations
            switch(field.id) {
                case 'email':
                    if (!validateEmail(field.value)) {
                        field.classList.add('invalid');
                        errorMsg.textContent = 'Please enter a valid email address';
                        isValid = false;
                    }
                    break;

                case 'confirm-password':
                    const password = document.getElementById('password').value;
                    if (field.value !== password) {
                        field.classList.add('invalid');
                        errorMsg.textContent = 'Passwords do not match';
                        isValid = false;
                    }
                    break;

                case 'first-name':
                case 'last-name':
                    if (!/^[a-zA-Z-' ]*$/.test(field.value)) {
                        field.classList.add('invalid');
                        errorMsg.textContent = 'Only letters and spaces allowed';
                        isValid = false;
                    }
                    break;

                case 'age':
                    if (isNaN(field.value) || parseInt(field.value) < 12) {
                        field.classList.add('invalid');
                        errorMsg.textContent = 'Must be at least 12';
                        isValid = false;
                    }
                    break;

                case 'contact-number':
                    if (!/^[0-9]{11}$/.test(field.value)) {
                        field.classList.add('invalid');
                        errorMsg.textContent = 'Please enter a valid 11-digit phone number';
                        isValid = false;
                    }
                    break;
            }
        });

        return isValid;
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Next button click handler
    nextBtns.forEach((btn, idx) => {
        btn.addEventListener('click', function () {
            // If on the first step (Security), check email via AJAX before proceeding
            if (currentStep === 0) {
                const emailInput = document.getElementById("email");
                const email = emailInput.value.trim();
                if (!validateStep(currentStep)) return;

                fetch("check-email.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "email=" + encodeURIComponent(email)
                })
                .then(res => res.json())
                .then(data => {
                    let errorSpan = emailInput.parentElement.querySelector('.error-message');
                    if (!errorSpan) {
                        errorSpan = document.createElement('span');
                        errorSpan.className = 'error-message';
                        errorSpan.style.color = 'red';
                        emailInput.parentElement.appendChild(errorSpan);
                    }
                    if (data.exists) {
                        errorSpan.textContent = "This email is already registered. Please use another email.";
                    } else {
                        errorSpan.textContent = "";
                        currentStep++;
                        showStep(currentStep);
                    }
                });
            } else {
                if (validateStep(currentStep)) {
                    currentStep++;
                    if (currentStep >= formSteps.length) {
                        currentStep = formSteps.length - 1;
                    }
                    showStep(currentStep);
                }
            }
        });
    });

    // Previous button click handler
    prevBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            currentStep--;
            if (currentStep < 0) {
                currentStep = 0;
            }
            showStep(currentStep);
        });
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        let allValid = true;

        // Validate all steps
        for (let i = 0; i < formSteps.length; i++) {
            if (!validateStep(i)) {
                allValid = false;
                // Show the first step with errors
                if (allValid === false && currentStep !== i) {
                    showStep(i);
                    break;
                }
            }
        }

        if (!allValid) {
            e.preventDefault();
        }
    });

    // Initialize form
    showStep(currentStep);

    // Real-time validation for fields
    const fieldsToValidate = ['first-name', 'last-name', 'email', 'password', 'confirm-password', 'age', 'contact-number'];
    fieldsToValidate.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                this.classList.remove('invalid');
                const errorMsg = this.parentElement.querySelector('.error-message');
                if (errorMsg) errorMsg.textContent = '';
            });
        }
    });
});