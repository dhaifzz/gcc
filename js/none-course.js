document.getElementById('course-grade').addEventListener('change', function() {
    const courseGrade = this.value;
    const role = document.getElementById('role').value;
    const wmsuIdField = document.getElementById('wmsu-id');
    const emailField = document.getElementById('email');

    if (courseGrade === 'None' && role === 'Outside Client') {
        wmsuIdField.value = '';
        wmsuIdField.disabled = true;
        emailField.pattern = '^[^@]+@(?!wmsu\\.edu\\.ph$)[^@]+$';
    } else {
        wmsuIdField.disabled = false;
        emailField.removeAttribute('pattern');
    }
});

document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    const courseGrade = document.getElementById('course-grade').value;
    const wmsuIdField = document.getElementById('wmsu-id');
    const emailField = document.getElementById('email');

    if (courseGrade === 'None' && role === 'Outside Client') {
        wmsuIdField.value = '';
        wmsuIdField.disabled = true;
        emailField.pattern = '^[^@]+@(?!wmsu\\.edu\\.ph$)[^@]+$';
    } else {
        wmsuIdField.disabled = false;
        emailField.removeAttribute('pattern');
    }
});