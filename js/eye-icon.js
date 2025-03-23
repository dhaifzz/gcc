function togglePassword(passwordFieldId, iconContainer) {
    var passwordField = document.getElementById(passwordFieldId);
    var passwordFieldType = passwordField.getAttribute("type");
    var toggleIcon = iconContainer.querySelector("i");

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
