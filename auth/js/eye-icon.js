function togglePassword(passwordFieldId, toggleIconClass) {
       var passwordField = document.getElementById(passwordFieldId);
       var passwordFieldType = passwordField.getAttribute("type");
       var toggleIcon = document.querySelector("." + toggleIconClass + " i");
   
       if (passwordFieldType === "password") {
           passwordField.setAttribute("type", "text");
           toggleIcon.classList.remove("fa-eye-slash");
           toggleIcon.classList.add("fa-eye");
       } else {
           passwordField.setAttribute("type", "password");
           toggleIcon.classList.remove("fa-eye");
           toggleIcon.classList.add("fa-eye-slash");
       }
   }
   