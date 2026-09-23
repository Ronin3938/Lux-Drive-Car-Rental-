const steps = document.querySelectorAll('.step');
let currentStep = 1;
showStep(currentStep);

// Show and hide a temporary message
function showMessage(message) {
    // Create a new div for the message
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('floating-message');
    messageDiv.textContent = message;

    // Append it to the body
    document.body.appendChild(messageDiv);

    // Set a timeout to remove the message after 2 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 2000);
}

function showStep(step) {
    document.querySelectorAll('.step').forEach(el => el.style.display = 'none');
    document.getElementById('step' + step).style.display = 'block';
}

function nextStep() {
    if (currentStep === 1) {
        // Step 1 validation
        let firstName = document.querySelector('#step1 input[name="first_name"]').value.trim();
        let lastName = document.querySelector('#step1 input[name="last_name"]').value.trim();
        let email = document.querySelector('#step1 input[name="email"]').value.trim();

        if (!firstName || !lastName || !email) {
            showMessage("Please fill in all fields to continue.");
            return;
        }

        // AJAX check for email uniqueness
        fetch('check_email.php?email=' + encodeURIComponent(email))
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    showMessage("Email already exists!");
                } else {
                    currentStep++;
                    showStep(currentStep);
                }
            });
    } else if (currentStep === 2) {
        // Step 2 validation
        let gender = document.querySelector('#step2 select[name="gender"]').value;
        let birthdate = document.querySelector('#step2 input[name="birthdate"]').value.trim();
        let phone = document.querySelector('#step2 input[name="phone"]').value.trim();
        let city = document.querySelector('#step2 input[name="city"]').value.trim();
        let street = document.querySelector('#step2 input[name="street"]').value.trim();
        let homeNo = document.querySelector('#step2 input[name="home_no"]').value.trim();
        let latitude = document.querySelector('#step2 input[name="latitude"]').value.trim();

        if (!gender || !birthdate || !phone || !city || !street || !homeNo) {
            showMessage("Please fill in all personal and address fields to continue.");
            return;
        }

        // Check if user is at least 10 years old and not over 100
        const birthDate = new Date(birthdate);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 10) {
            showMessage('You need to be at least 10 years old.');
            return;
        }
        if (age > 100) {
            showMessage('Please enter a valid age.');
            return;
        }
        
        if (!latitude) {
            showMessage("Please choose your location on the map.");
            return;
        }

        currentStep++;
        showStep(currentStep);
    } else if (currentStep === 3) {
        // Step 3 (Profile Picture)
        currentStep++;
        showStep(currentStep);
    } else if (currentStep === 4) {
        // Step 4 (Password) validation
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password.length < 8) {
            showMessage("Password must be at least 8 characters long.");
            return;
        }
        if (!/[A-Z]/.test(password)) {
            showMessage("Password must contain at least one uppercase letter.");
            return;
        }
        if (!/[0-9]/.test(password)) {
            showMessage("Password must contain at least one number.");
            return;
        }
        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>/?~]/.test(password)) {
            showMessage("Password must contain at least one special character.");
            return;
        }

        if (password !== confirmPassword) {
            showMessage("Passwords do not match!");
            return;
        }
        
        // If all checks pass, submit the form
        document.getElementById('signupForm').submit();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
}

function skipStep3() {
    currentStep++;
    showStep(currentStep);
}

// Profile picture preview
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const file = e.target.files[0];
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});

// Password toggle
function togglePassword() {
    let p = document.getElementById('password');
    let c = document.getElementById('confirm_password');
    p.type = p.type === 'password' ? 'text' : 'password';
    c.type = c.type === 'password' ? 'text' : 'password';
}

// Leaflet Map
let map, marker;

function openMap() {
    document.getElementById('locationModal').style.display = 'block';
    if (!map) {
        let lat = 16.8409,
            lng = 96.1735;
        map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        marker.on('dragend', function() {
            const pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat;
            document.getElementById('longitude').value = pos.lng;
        });
    }
    // Update map size to display correctly
    setTimeout(function() {
        map.invalidateSize();
    }, 400);
}

function closeMap() {
    // Hide the modal
    document.getElementById("locationModal").style.display = "none";
}

// Close the map modal when the user clicks outside
window.onclick = function(event) {
    if (event.target == document.getElementById("locationModal")) {
        closeMap();
    }
}
