function validateRegisterForm() {
    const id = document.getElementById('id').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const address = document.getElementById('address').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const email = document.getElementById('email').value.trim();

    // Basic validation
    if (!id || !username || !password || !address || !phone || !email) {
        alert('All fields are required.');
        return false;
    }

    // ID validation (only numbers)
    const idPattern = /^\d+$/;
    if (!idPattern.test(id)) {
        alert('ID must contain only numbers.');
        return false;
    }

    // Email validation
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert('Please enter a valid email address.');
        return false;
    }

    // Phone validation (exactly 10 digits)
    const phonePattern = /^\d{10}$/;
    if (!phonePattern.test(phone)) {
        alert('Phone number must contain exactly 10 digits.');
        return false;
    }

    // Password length
    if (password.length < 6) {
        alert('Password must be at least 6 characters.');
        return false;
    }

    return true;
}