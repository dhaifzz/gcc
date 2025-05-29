document.addEventListener('DOMContentLoaded', function() {
    const schoolField = document.getElementById('school');
    const emailField = document.getElementById('email');
    const wmsuIdField = document.getElementById('wmsu-id');
    
    // Function to check if email is a WMSU email
    function isWmsuEmail(email) {
        return email.toLowerCase().endsWith('@wmsu.edu.ph');
    }
    
    // Function to check if school is WMSU
    function isWmsuSchool(school) {
        return school === 'Western Mindanao State University';
    }
    
    // Function to update form state based on email and school selection
    function updateFormState() {
        const school = schoolField.value.trim();
        const email = emailField.value.trim();
        
        // Remove existing error messages
        clearWmsuErrors();
        
        // Email-based validation
        if (email && !isWmsuEmail(email)) {
            // If email is not WMSU, don't allow selecting WMSU as school
            if (isWmsuSchool(school)) {
                // If they've selected WMSU with non-WMSU email, show error and reset school
                schoolField.classList.add('invalid');
                emailField.classList.add('invalid');
                addErrorMessage(emailField, 'Non-WMSU email addresses cannot select Western Mindanao State University');
                
                // Reset school field
                schoolField.value = '';
                
                // Shake the email field to indicate it's the issue
                emailField.classList.add('error-shake');
                setTimeout(() => {
                    emailField.classList.remove('error-shake');
                }, 600);
                
                // Re-run validation after resetting
                setTimeout(updateFormState, 100);
                return;
            }
        }
        
        // WMSU Email Validation: If using WMSU email, must select WMSU as school
        if (email && isWmsuEmail(email)) {
            if (!isWmsuSchool(school) && school) {
                // Error: WMSU email but non-WMSU school
                schoolField.classList.add('invalid');
                emailField.classList.add('invalid');
                addErrorMessage(emailField, 'With WMSU email, you must select Western Mindanao State University as your school');
                
                // Highlight school field to show it needs to be WMSU
                schoolField.classList.add('error-shake');
                setTimeout(() => {
                    schoolField.classList.remove('error-shake');
                }, 600);
                
                // Auto-correct: Set school to WMSU
                schoolField.value = 'Western Mindanao State University';
                setTimeout(updateFormState, 100); // Re-run validation after auto-correction
                return;
            } else if (!school) {
                // Suggest WMSU as school
                addInfoMessage(schoolField, 'For WMSU email accounts, please select Western Mindanao State University');
            } else {
                // Enable WMSU ID field because they selected WMSU with WMSU email
                wmsuIdField.disabled = false;
                wmsuIdField.placeholder = "Enter your School ID";
            }
        } 
        
        // School-based validation
        if (isWmsuSchool(school)) {
            // WMSU selected
            if (email && !isWmsuEmail(email)) {
                // Error: WMSU selected but email is not WMSU
                emailField.classList.add('invalid');
                addErrorMessage(emailField, 'Western Mindanao State University requires WMSU email (@wmsu.edu.ph)');
                
                // Disable WMSU ID field
                wmsuIdField.disabled = true;
                wmsuIdField.placeholder = "Requires WMSU email";
                
                // Reset school selection since non-WMSU email can't select WMSU
                schoolField.value = '';
                schoolField.classList.add('error-shake');
                setTimeout(() => {
                    schoolField.classList.remove('error-shake');
                }, 600);
                
                // Re-run validation after resetting
                setTimeout(updateFormState, 100);
                return;
            } else {
                // Valid: WMSU school with WMSU email or empty email
                wmsuIdField.disabled = false;
                wmsuIdField.placeholder = "Enter your School ID";
            }
        } else if (school && !isWmsuSchool(school)) {
            // Non-WMSU school selected
            
            // If ID field has a value, show error
            if (wmsuIdField.value.trim()) {
                wmsuIdField.classList.add('invalid');
                addErrorMessage(wmsuIdField, 'Only WMSU students/staff can input a School ID');
            }
            
            // Disable WMSU ID field for non-WMSU schools
            wmsuIdField.disabled = true;
            wmsuIdField.placeholder = "Only for WMSU students/staff";
            
            // If email is WMSU but school is not WMSU, show error
            if (email && isWmsuEmail(email)) {
                emailField.classList.add('invalid');
                schoolField.classList.add('invalid');
                addErrorMessage(emailField, 'WMSU email can only be used if school is Western Mindanao State University');
            }
        } else {
            // No school selected
            wmsuIdField.disabled = false;
            wmsuIdField.placeholder = "Enter your School ID";
        }
    }
    
    // Add error message after an element
    function addErrorMessage(element, message) {
        // Check if message already exists
        const existingError = element.parentNode.querySelector('.wmsu-validation-error');
        if (existingError) {
            existingError.textContent = message;
            return;
        }
        
        // Create error message
        const errorMsg = document.createElement('span');
        errorMsg.className = 'error-message wmsu-validation-error';
        errorMsg.textContent = message;
        
        // Add after the element
        element.parentNode.insertBefore(errorMsg, element.nextSibling);
    }
    
    // Add info message (not an error)
    function addInfoMessage(element, message) {
        // Check if message already exists
        const existingInfo = element.parentNode.querySelector('.wmsu-info-message');
        if (existingInfo) {
            existingInfo.textContent = message;
            return;
        }
        
        // Create info message
        const infoMsg = document.createElement('span');
        infoMsg.className = 'info-message wmsu-info-message';
        infoMsg.textContent = message;
        infoMsg.style.color = '#fff';
        infoMsg.style.fontSize = '11px';
        infoMsg.style.display = 'block';
        infoMsg.style.marginTop = '-8px';
        infoMsg.style.marginBottom = '8px';
        
        // Add after the element
        element.parentNode.insertBefore(infoMsg, element.nextSibling);
    }
    
    // Clear WMSU validation errors and info messages
    function clearWmsuErrors() {
        document.querySelectorAll('.wmsu-validation-error, .wmsu-info-message').forEach(el => el.remove());
        
        // Reset invalid class for fields
        if (!emailField.classList.contains('email-exists-error')) {
            emailField.classList.remove('invalid');
        }
        
        if (!wmsuIdField.classList.contains('wmsu-id-exists-error')) {
            wmsuIdField.classList.remove('invalid');
        }
        
        schoolField.classList.remove('invalid');
    }
    
    // Add event listeners
    if (schoolField && emailField && wmsuIdField) {
        // Listen for changes to school field
        schoolField.addEventListener('change', updateFormState);
        
        // Listen for email changes to validate WMSU email requirements
        emailField.addEventListener('input', function() {
            // Only run validation when email has a domain (contains @)
            if (this.value.includes('@')) {
                setTimeout(updateFormState, 200);
            }
        });
        
        // Listen for WMSU ID changes
        wmsuIdField.addEventListener('input', function() {
            const school = schoolField.value.trim();
            if (wmsuIdField.value.trim() && !isWmsuSchool(school)) {
                wmsuIdField.classList.add('invalid');
                addErrorMessage(wmsuIdField, 'Only WMSU students/staff can input a School ID');
            } else {
                clearWmsuErrors();
                updateFormState();
            }
        });
        
        // Initial state
        updateFormState();
    }
    
    // Add validation to the form's step navigation
    const nextBtns = document.querySelectorAll('.next-btn');
    if (nextBtns.length > 0) {
        nextBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const currentStep = btn.closest('.form-step');
                
                // Only validate if this step contains the school field
                if (currentStep && currentStep.contains(schoolField)) {
                    const school = schoolField.value.trim();
                    const email = emailField.value.trim();
                    
                    // Check if non-WMSU email is trying to select WMSU
                    if (email && !isWmsuEmail(email) && isWmsuSchool(school)) {
                        e.stopPropagation(); // Prevent the stepper's next click handler
                        
                        // Show error
                        schoolField.classList.add('invalid');
                        emailField.classList.add('invalid');
                        emailField.classList.add('error-shake');
                        
                        // Remove shake animation after it completes
                        setTimeout(() => {
                            emailField.classList.remove('error-shake');
                        }, 600);
                        
                        addErrorMessage(emailField, 'Non-WMSU email addresses cannot select Western Mindanao State University');
                        
                        // Show popup
                        if (typeof showErrorPopup === 'function') {
                            showErrorPopup('Non-WMSU email addresses cannot select Western Mindanao State University');
                        }
                        
                        // Reset school selection
                        schoolField.value = '';
                        
                        return false;
                    }
                    
                    // WMSU Email Validation: If using WMSU email, must select WMSU as school
                    if (email && isWmsuEmail(email) && !isWmsuSchool(school) && school) {
                        e.stopPropagation(); // Prevent the stepper's next click handler
                        
                        // Show error
                        schoolField.classList.add('invalid');
                        emailField.classList.add('invalid');
                        schoolField.classList.add('error-shake');
                        
                        // Remove shake animation after it completes
                        setTimeout(() => {
                            schoolField.classList.remove('error-shake');
                        }, 600);
                        
                        addErrorMessage(emailField, 'With WMSU email, you must select Western Mindanao State University as your school');
                        
                        // Show popup
                        if (typeof showErrorPopup === 'function') {
                            showErrorPopup('With WMSU email (@wmsu.edu.ph), you must select Western Mindanao State University as your school');
                        }
                        
                        return false;
                    }
                    
                    // If WMSU is selected, email must be @wmsu.edu.ph
                    if (isWmsuSchool(school) && email && !isWmsuEmail(email)) {
                        e.stopPropagation(); // Prevent the stepper's next click handler
                        
                        // Show error
                        emailField.classList.add('invalid');
                        emailField.classList.add('error-shake');
                        
                        // Remove shake animation after it completes
                        setTimeout(() => {
                            emailField.classList.remove('error-shake');
                        }, 600);
                        
                        addErrorMessage(emailField, 'Western Mindanao State University requires WMSU email (@wmsu.edu.ph)');
                        
                        // Show popup
                        if (typeof showErrorPopup === 'function') {
                            showErrorPopup('Western Mindanao State University requires WMSU email (@wmsu.edu.ph)');
                        }
                        
                        return false;
                    }
                    
                    // Check if non-WMSU school with WMSU ID
                    if (!isWmsuSchool(school) && school && wmsuIdField.value.trim()) {
                        e.stopPropagation(); // Prevent the stepper's next click handler
                        
                        // Show error
                        wmsuIdField.classList.add('invalid');
                        wmsuIdField.classList.add('error-shake');
                        
                        // Remove shake animation after it completes
                        setTimeout(() => {
                            wmsuIdField.classList.remove('error-shake');
                        }, 600);
                        
                        addErrorMessage(wmsuIdField, 'Only WMSU students/staff can input a School ID');
                        
                        // Show popup
                        if (typeof showErrorPopup === 'function') {
                            showErrorPopup('Only WMSU students/staff can input a School ID');
                        }
                        
                        return false;
                    }
                }
            }, true); // Use capturing to run before the stepper's click handler
        });
    }
}); 