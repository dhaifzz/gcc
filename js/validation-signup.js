function initializeFormValidation() {
       // Initialize the stepper first
       if (typeof initializeStepper === 'function') {
           initializeStepper();
       }
   
       // Handle form submission
       const form = document.getElementById('multiStepForm');
       if (form) {
           form.addEventListener('submit', function(e) {
               // Validate all steps before submission
               let allValid = true;
               
               // Check all steps for errors
               const formSteps = document.querySelectorAll('.form-step');
               for (let i = 0; i < formSteps.length; i++) {
                   if (!validateStep(i)) {
                       allValid = false;
                       // Show the first step with errors
                       showStep(i);
                       break;
                   }
               }
               
               if (!allValid) {
                   e.preventDefault(); // Prevent form submission
                   // Find and show the first step with errors
                   const errorStep = findErrorStep();
                   if (errorStep !== -1) {
                       showStep(errorStep);
                   }
               }
           });
       }
   
       // Real-time validation for fields
       const firstNameField = document.getElementById('first-name');
       if (firstNameField) {
           firstNameField.addEventListener('input', function() {
               this.classList.remove('invalid');
               const errorMsg = this.parentElement.querySelector('.error-message');
               if (errorMsg) errorMsg.textContent = '';
           });
       }
   
       const lastNameField = document.getElementById('last-name');
       if (lastNameField) {
           lastNameField.addEventListener('input', function() {
               this.classList.remove('invalid');
               const errorMsg = this.parentElement.querySelector('.error-message');
               if (errorMsg) errorMsg.textContent = '';
           });
       }
   }
   
   function updateProgressStepper(activeIndex) {
       const steps = document.querySelectorAll('.progress-stepper .step');
       if (steps) {
           steps.forEach((step, index) => {
               if (index <= activeIndex) {
                   step.classList.add('active');
               } else {
                   step.classList.remove('active');
               }
           });
       }
   }
   
   function showStep(step) {
       const formSteps = document.querySelectorAll('.form-step');
       if (formSteps) {
           formSteps.forEach((formStep, index) => {
               formStep.classList.toggle('active', index === step);
           });
       }
       
       updateProgressStepper(step);
   }
   
   function findErrorStep() {
       const errorFields = document.querySelectorAll('.invalid');
       const formSteps = document.querySelectorAll('.form-step');
       
       if (errorFields.length > 0 && formSteps.length > 0) {
           for (let i = 0; i < formSteps.length; i++) {
               if (formSteps[i].querySelector('.invalid')) {
                   return i;
               }
           }
       }
       return -1;
   }
   
   function validateStep(step) {
       const formSteps = document.querySelectorAll('.form-step');
       if (!formSteps || step >= formSteps.length) return false;
   
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
                   const password = document.getElementById('password')?.value;
                   if (password && field.value !== password) {
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
                       errorMsg.textContent = 'Must be at least 12+ to sign up';
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
   
   function ensureErrorMessageElement(field) {
       let errorMsg = field.parentElement.querySelector('.error-message');
       if (!errorMsg) {
           errorMsg = document.createElement('span');
           errorMsg.className = 'error-message';
           field.parentElement.appendChild(errorMsg);
       }
       return errorMsg;
   }
   
   // Initialize when DOM is loaded
   document.addEventListener('DOMContentLoaded', function() {
       initializeFormValidation();
       
       // Process PHP errors if they exist
       if (typeof errorMessages !== 'undefined') {
           // Process each error
           Object.keys(errorMessages).forEach(field => {
               if (field !== 'database') {
                   const htmlId = field.replace(/_/g, '-');
                   const fieldElement = document.getElementById(htmlId);
                   
                   if (fieldElement) {
                       fieldElement.classList.add('invalid');
                       
                       // Find or create error message element
                       let errorSpan = fieldElement.parentElement.querySelector('.error-message');
                       if (!errorSpan) {
                           errorSpan = document.createElement('span');
                           errorSpan.className = 'error-message';
                           fieldElement.parentElement.appendChild(errorSpan);
                       }
                       
                       errorSpan.textContent = errorMessages[field];
                       
                       // Show the step containing this field
                       const step = fieldElement.closest('.form-step');
                       if (step) {
                           // Hide all steps
                           document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
                           // Show this step
                           step.classList.add('active');
                           
                           // Update progress stepper
                           const stepIndex = Array.from(document.querySelectorAll('.form-step')).indexOf(step);
                           updateProgressStepper(stepIndex);
                       }
                   }
               }
           });
       }
   });