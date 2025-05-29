document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
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

    // Error popup system
    function showErrorPopup(messages) {
        const existingPopup = document.getElementById('errorPopup');
        if (existingPopup) existingPopup.remove();
        
        const popup = document.createElement('div');
        popup.id = 'errorPopup';
        popup.className = 'error-popup';
        popup.innerHTML = Array.isArray(messages) ? messages.join('<br>') : messages;
        
        document.body.appendChild(popup);
        setTimeout(() => popup.classList.add('show'), 100);
        setTimeout(() => {
            popup.classList.remove('show');
            setTimeout(() => popup.remove(), 300);
        }, 3000); // Extended display time for better readability
    }

    // Validation functions
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validatePassword(password) {
        return password.length >= 8;
    }

    function validateContactNumber(number) {
        // Simple validation for non-empty phone numbers
        // You can enhance this with specific phone number format if needed
        return number.trim().length > 0;
    }

    // Check if email is already in use
    async function isEmailAlreadyUsed(email) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../auth/ajax/check_email.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        resolve(response.exists);
                    } catch(e) {
                        resolve(false);
                    }
                } else {
                    resolve(false);
                }
            };
            xhr.onerror = function() {
                resolve(false);
            };
            xhr.send('email=' + encodeURIComponent(email));
        });
    }

    // Check if contact number is already in use
    async function isContactAlreadyUsed(contactNumber) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../auth/ajax/check_contact.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        resolve(response.exists);
                    } catch(e) {
                        resolve(false);
                    }
                } else {
                    resolve(false);
                }
            };
            xhr.onerror = function() {
                resolve(false);
            };
            xhr.send('contact_number=' + encodeURIComponent(contactNumber));
        });
    }

    // Check if WMSU ID is already in use
    async function isWmsuIdAlreadyUsed(wmsuId) {
        // Skip check for empty or Guest ID
        if (!wmsuId || wmsuId === 'Guest ID') return false;
        
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../auth/ajax/check_wmsu_id.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        resolve(response.exists);
                    } catch(e) {
                        resolve(false);
                    }
                } else {
                    resolve(false);
                }
            };
            xhr.onerror = function() {
                resolve(false);
            };
            xhr.send('wmsu_id=' + encodeURIComponent(wmsuId));
        });
    }

    async function validateStep(step) {
        const currentStepFields = formSteps[step].querySelectorAll('[required]');
        let errorMessages = [];
        let isValid = true;

        // Check all required fields in current step
        for (const field of currentStepFields) {
            field.classList.remove('invalid');
            
            // Check if field is empty
            if (!field.value.trim()) {
                field.classList.add('invalid');
                errorMessages.push(`${field.previousElementSibling.textContent} is required`);
                isValid = false;
                continue;
            }

            // Field-specific validations
            switch(field.id) {
                case 'email':
                    if (!validateEmail(field.value)) {
                        field.classList.add('invalid');
                        errorMessages.push('Please enter a valid email address');
                        isValid = false;
                    } else if (step === 0) {
                        // Check if email is already used (only in step 1)
                        const emailExists = await isEmailAlreadyUsed(field.value);
                        if (emailExists) {
                            field.classList.add('invalid');
                            errorMessages.push('This email is already registered. Please use another email.');
                            isValid = false;
                        }
                    }
                    break;
                case 'password':
                    if (!validatePassword(field.value)) {
                        field.classList.add('invalid');
                        errorMessages.push('Password must be at least 8 characters long');
                        isValid = false;
                    }
                    break;
                case 'confirm-password':
                    const password = document.getElementById('password')?.value;
                    if (field.value !== password) {
                        field.classList.add('invalid');
                        errorMessages.push('Passwords do not match');
                        isValid = false;
                    }
                    break;
                case 'contact-number':
                    if (!validateContactNumber(field.value)) {
                        field.classList.add('invalid');
                        errorMessages.push('Please enter a valid contact number');
                        isValid = false;
                    } else {
                        // Check if contact number is already used
                        const contactExists = await isContactAlreadyUsed(field.value);
                        if (contactExists) {
                            field.classList.add('invalid');
                            errorMessages.push('This contact number is already registered. Please use another number.');
                            isValid = false;
                        }
                    }
                    break;
                case 'birth-date':
                    if (!field.value) {
                        field.classList.add('invalid');
                        errorMessages.push('Birth date is required');
                        isValid = false;
                    } else {
                        // Validate minimum age (10 years)
                        const birthDate = new Date(field.value);
                        const today = new Date();
                        const minAge = 10;
                        
                        // Calculate age
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const monthDiff = today.getMonth() - birthDate.getMonth();
                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        
                        if (age < minAge) {
                            field.classList.add('invalid');
                            errorMessages.push(`You must be at least ${minAge} years old to sign up`);
                            isValid = false;
                        }
                    }
                    break;
            }
        }
        
        // Check for WMSU ID validity if it's not empty (even if not required)
        const wmsuIdField = document.getElementById('wmsu-id');
        if (wmsuIdField && wmsuIdField.value.trim() && formSteps[step].contains(wmsuIdField)) {
            const wmsuId = wmsuIdField.value.trim();
            if (wmsuId !== 'Guest ID') {
                const wmsuIdExists = await isWmsuIdAlreadyUsed(wmsuId);
                if (wmsuIdExists) {
                    wmsuIdField.classList.add('invalid');
                    errorMessages.push('This School ID is already registered. Please use another ID.');
                    isValid = false;
                }
            }
        }

        if (errorMessages.length > 0) {
            showErrorPopup(errorMessages);
        }
        
        return isValid;
    }

    // Navigation handlers
    nextBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            if (await validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });

    prevBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            currentStep--;
            showStep(currentStep);
        });
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        let allValid = await validateStep(currentStep);
        
        if (allValid) {
            this.submit();
        }
    });

    // Initialize
    showStep(currentStep);

    // Real-time validation cleanup - only remove invalid class, not background color
    const allInputFields = document.querySelectorAll('input, select');
    allInputFields.forEach(field => {
        field.addEventListener('input', function() {
            // Do not automatically remove the invalid class on input
            // This ensures error styling remains until validation passes
        });
    });

    // Process PHP errors if they exist
    if (typeof errorMessages !== 'undefined' && Object.keys(errorMessages).length > 0) {
        let errors = [];
        Object.keys(errorMessages).forEach(field => {
            errors.push(errorMessages[field]);
            const fieldElement = document.getElementById(field.replace(/_/g, '-'));
            if (fieldElement) {
                fieldElement.classList.add('invalid');
            }
        });
        if (errors.length > 0) showErrorPopup(errors);
    }
});