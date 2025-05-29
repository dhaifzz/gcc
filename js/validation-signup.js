// function initializeFormValidation() {
//     // Initialize the stepper first
//     if (typeof initializeStepper === 'function') {
//         initializeStepper();
//     }

//     // Handle form submission
//     const form = document.getElementById('multiStepForm');
//     if (form) {
//         form.addEventListener('submit', function(e) {
//             // Validate all steps before submission
//             let allValid = true;
//             let errorMessages = [];
            
//             // Check all steps for errors
//             const formSteps = document.querySelectorAll('.form-step');
//             for (let i = 0; i < formSteps.length; i++) {
//                 const stepErrors = validateStep(i);
//                 if (stepErrors.length > 0) {
//                     allValid = false;
//                     errorMessages = errorMessages.concat(stepErrors);
//                     // Show the first step with errors
//                     showStep(i);
//                     break;
//                 }
//             }
            
//             if (!allValid) {
//                 e.preventDefault(); // Prevent form submission
//                 // Show error popup
//                 showErrorPopup(errorMessages);
//                 // Find and show the first step with errors
//                 const errorStep = findErrorStep();
//                 if (errorStep !== -1) {
//                     showStep(errorStep);
//                 }
//             }
//         });
//     }

//     // Real-time validation for fields
//     const firstNameField = document.getElementById('first-name');
//     if (firstNameField) {
//         firstNameField.addEventListener('input', function() {
//             this.classList.remove('invalid');
//         });
//     }

//     const lastNameField = document.getElementById('last-name');
//     if (lastNameField) {
//         lastNameField.addEventListener('input', function() {
//             this.classList.remove('invalid');
//         });
//     }
// }

// function showErrorPopup(messages) {
//     // Remove existing popup if any
//     const existingPopup = document.getElementById('errorPopup');
//     if (existingPopup) {
//         existingPopup.remove();
//     }
    
//     // Create new popup
//     const popup = document.createElement('div');
//     popup.id = 'errorPopup';
//     popup.className = 'error-popup';
//     popup.innerHTML = messages.join('<br>');
    
//     document.body.appendChild(popup);
    
//     // Show the popup
//     setTimeout(() => {
//         popup.classList.add('show');
//     }, 100);
    
//     // Hide after 2 seconds
//     setTimeout(() => {
//         popup.classList.remove('show');
//         setTimeout(() => {
//             popup.remove();
//         }, 300);
//     }, 2000);
// }

// function updateProgressStepper(activeIndex) {
//     const steps = document.querySelectorAll('.progress-stepper .step');
//     if (steps) {
//         steps.forEach((step, index) => {
//             if (index <= activeIndex) {
//                 step.classList.add('active');
//             } else {
//                 step.classList.remove('active');
//             }
//         });
//     }
// }

// function showStep(step) {
//     const formSteps = document.querySelectorAll('.form-step');
//     if (formSteps) {
//         formSteps.forEach((formStep, index) => {
//             formStep.classList.toggle('active', index === step);
//         });
//     }
    
//     updateProgressStepper(step);
// }

// function findErrorStep() {
//     const errorFields = document.querySelectorAll('.invalid');
//     const formSteps = document.querySelectorAll('.form-step');
    
//     if (errorFields.length > 0 && formSteps.length > 0) {
//         for (let i = 0; i < formSteps.length; i++) {
//             if (formSteps[i].querySelector('.invalid')) {
//                 return i;
//             }
//         }
//     }
//     return -1;
// }

// function validateStep(step) {
//     const formSteps = document.querySelectorAll('.form-step');
//     if (!formSteps || step >= formSteps.length) return [];
    
//     const currentStepFields = formSteps[step].querySelectorAll('[required]');
//     let errorMessages = [];
    
//     currentStepFields.forEach(field => {
//         // Clear previous error state
//         field.classList.remove('invalid');
        
//         // Check if field is empty
//         if (!field.value.trim()) {
//             field.classList.add('invalid');
//             errorMessages.push(`${field.labels[0].textContent} is required`);
//             return;
//         }
        
//         // Field-specific validations
//         switch(field.id) {
//             case 'email':
//                 if (!validateEmail(field.value)) {
//                     field.classList.add('invalid');
//                     errorMessages.push('Please enter a valid email address');
//                 }
//                 break;
            
//             case 'confirm-password':
//                 const password = document.getElementById('password')?.value;
//                 if (password && field.value !== password) {
//                     field.classList.add('invalid');
//                     errorMessages.push('Passwords do not match');
//                 }
//                 break;
            
//             case 'first-name':
//             case 'last-name':
//                 if (!/^[a-zA-Z-' ]*$/.test(field.value)) {
//                     field.classList.add('invalid');
//                     errorMessages.push('Only letters and spaces allowed for name fields');
//                 }
//                 break;
            
//             case 'birth-date': 
//                 if (field.value === '') {
//                     field.classList.add('invalid');
//                     errorMessages.push('Birth date is required');
//                 } else {
//                     const birthDate = new Date(field.value);
//                     const today = new Date();
//                     let age = today.getFullYear() - birthDate.getFullYear();
//                     const m = today.getMonth() - birthDate.getMonth();
//                     if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
//                         age--;
//                     }
//                     if (age < 10) {
//                         field.classList.add('invalid');
//                         errorMessages.push('You must be at least 10 years old to sign up');
//                     }
//                 }
//                 break;
            
//             case 'contact-number':
//                 if (!/^[0-9]{11}$/.test(field.value)) {
//                     field.classList.add('invalid');
//                     errorMessages.push('Please enter a valid 11-digit phone number');
//                 }
//                 break;
//         }
//     });
    
//     return errorMessages;
// }

// function validateEmail(email) {
//     const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//     return re.test(email);
// }

// // Initialize when DOM is loaded
// document.addEventListener('DOMContentLoaded', function() {
//     initializeFormValidation();
    
//     // Process PHP errors if they exist
//     if (typeof errorMessages !== 'undefined') {
//         let errorList = [];
        
//         // Process each error
//         Object.keys(errorMessages).forEach(field => {
//             if (field !== 'database') {
//                 const htmlId = field.replace(/_/g, '-');
//                 const fieldElement = document.getElementById(htmlId);
                
//                 if (fieldElement) {
//                     fieldElement.classList.add('invalid');
//                     errorList.push(errorMessages[field]);
                    
//                     // Show the step containing this field
//                     const step = fieldElement.closest('.form-step');
//                     if (step) {
//                         // Hide all steps
//                         document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
//                         // Show this step
//                         step.classList.add('active');
                        
//                         // Update progress stepper
//                         const stepIndex = Array.from(document.querySelectorAll('.form-step')).indexOf(step);
//                         updateProgressStepper(stepIndex);
//                     }
//                 }
//             }
//         });
        
//         // Show error popup if there are errors
//         if (errorList.length > 0) {
//             showErrorPopup(errorList);
//         }
//     }
// });

document.addEventListener('DOMContentLoaded', function() {
    // Get reference to form fields
    const emailField = document.getElementById('email');
    const contactNumberField = document.getElementById('contact-number');
    const wmsuIdField = document.getElementById('wmsu-id');
    
    // Email validation
    if (emailField) {
        let emailCheckTimeout;
        
        emailField.addEventListener('input', function() {
            // Clear any existing timeout
            clearTimeout(emailCheckTimeout);
            
            // Remove any existing error message related to duplicate email
            const errorElements = document.querySelectorAll('.email-exists-error');
            errorElements.forEach(el => el.remove());
            
            // Set a new timeout to check email after user stops typing
            emailCheckTimeout = setTimeout(function() {
                const email = emailField.value.trim();
                
                // Only check if email looks valid
                if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    checkEmailExists(email);
                }
            }, 500); // Wait 500ms after user stops typing
        });
    }
    
    // Contact number validation
    if (contactNumberField) {
        let contactCheckTimeout;
        
        contactNumberField.addEventListener('input', function() {
            // Clear any existing timeout
            clearTimeout(contactCheckTimeout);
            
            // Remove any existing error message related to duplicate contact
            const errorElements = document.querySelectorAll('.contact-exists-error');
            errorElements.forEach(el => el.remove());
            
            // Set a new timeout to check contact after user stops typing
            contactCheckTimeout = setTimeout(function() {
                const contactNumber = contactNumberField.value.trim();
                
                // Only check if contact number has enough digits
                if (contactNumber && contactNumber.length >= 10) {
                    checkContactExists(contactNumber);
                }
            }, 500); // Wait 500ms after user stops typing
        });
    }
    
    // WMSU ID validation
    if (wmsuIdField) {
        let wmsuIdCheckTimeout;
        
        wmsuIdField.addEventListener('input', function() {
            // Clear any existing timeout
            clearTimeout(wmsuIdCheckTimeout);
            
            // Remove any existing error message related to duplicate WMSU ID
            const errorElements = document.querySelectorAll('.wmsu-id-exists-error');
            errorElements.forEach(el => el.remove());
            
            // Set a new timeout to check WMSU ID after user stops typing
            wmsuIdCheckTimeout = setTimeout(function() {
                const wmsuId = wmsuIdField.value.trim();
                
                // Only check if WMSU ID has enough characters
                if (wmsuId && (wmsuId.length === 6 || wmsuId.length === 9)) {
                    checkWmsuIdExists(wmsuId);
                }
            }, 500); // Wait 500ms after user stops typing
        });
    }
    
    // Function to check if email exists
    function checkEmailExists(email) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../auth/ajax/check_email.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    
                    // If email exists, show error
                    if (response.exists) {
                        emailField.classList.add('invalid');
                        emailField.classList.add('error-shake');
                        
                        // Remove shake animation after it completes
                        setTimeout(() => {
                            emailField.classList.remove('error-shake');
                        }, 600);
                        
                        // Add error message if not already present
                        if (!document.querySelector('.email-exists-error')) {
                            const errorMsg = document.createElement('span');
                            errorMsg.className = 'error-message email-exists-error';
                            errorMsg.textContent = 'This email is already registered. Please use another email.';
                            
                            // Insert after email field
                            emailField.parentNode.insertBefore(errorMsg, emailField.nextSibling);
                        }
                    } else {
                        // If email doesn't exist, remove invalid class
                        emailField.classList.remove('invalid');
                    }
                } catch(e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        
        xhr.onerror = function() {
            console.error('Network error checking email');
        };
        
        xhr.send('email=' + encodeURIComponent(email));
    }
    
    // Function to check if contact number exists
    function checkContactExists(contactNumber) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../auth/ajax/check_contact.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    
                    // If contact number exists, show error
                    if (response.exists) {
                        contactNumberField.classList.add('invalid');
                        contactNumberField.classList.add('error-shake');
                        
                        // Remove shake animation after it completes
                        setTimeout(() => {
                            contactNumberField.classList.remove('error-shake');
                        }, 600);
                        
                        // Add error message if not already present
                        if (!document.querySelector('.contact-exists-error')) {
                            const errorMsg = document.createElement('span');
                            errorMsg.className = 'error-message contact-exists-error';
                            errorMsg.textContent = 'This contact number is already registered. Please use another number.';
                            
                            // Insert after contact field
                            contactNumberField.parentNode.insertBefore(errorMsg, contactNumberField.nextSibling);
                        }
                    } else {
                        // If contact doesn't exist, remove invalid class
                        contactNumberField.classList.remove('invalid');
                    }
                } catch(e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        
        xhr.onerror = function() {
            console.error('Network error checking contact number');
        };
        
        xhr.send('contact_number=' + encodeURIComponent(contactNumber));
    }
    
    // Function to check if WMSU ID exists
    function checkWmsuIdExists(wmsuId) {
        // Don't check if it's empty
        if (!wmsuId || wmsuId === 'Guest ID') return;
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../auth/ajax/check_wmsu_id.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    
                    // If WMSU ID exists, show error
                    if (response.exists) {
                        wmsuIdField.classList.add('invalid');
                        wmsuIdField.classList.add('error-shake');
                        
                        // Remove shake animation after it completes
                        setTimeout(() => {
                            wmsuIdField.classList.remove('error-shake');
                        }, 600);
                        
                        // Add error message if not already present
                        if (!document.querySelector('.wmsu-id-exists-error')) {
                            const errorMsg = document.createElement('span');
                            errorMsg.className = 'error-message wmsu-id-exists-error';
                            errorMsg.textContent = 'This School ID is already registered. Please use another ID.';
                            
                            // Insert after WMSU ID field
                            wmsuIdField.parentNode.insertBefore(errorMsg, wmsuIdField.nextSibling);
                        }
                    } else {
                        // If WMSU ID doesn't exist, remove invalid class
                        wmsuIdField.classList.remove('invalid');
                    }
                } catch(e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        
        xhr.onerror = function() {
            console.error('Network error checking WMSU ID');
        };
        
        xhr.send('wmsu_id=' + encodeURIComponent(wmsuId));
    }
});