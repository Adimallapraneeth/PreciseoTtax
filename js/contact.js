// Form validation and handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    // Phone number formatting
    const phoneInput = form.querySelector('input[name="phone"]');
    phoneInput.addEventListener('input', function(e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });

    // Date validation
    const dateInput = form.querySelector('input[name="date"]');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    dateInput.min = today.toISOString().split('T')[0];
    
    // Time validation based on business hours (9 AM - 5 PM)
    const timeInput = form.querySelector('input[name="time"]');
    timeInput.addEventListener('change', function() {
        const selectedTime = this.value;
        const hour = parseInt(selectedTime.split(':')[0]);
        if (hour < 9 || hour >= 17) {
            this.setCustomValidity('Please select a time between 9 AM and 5 PM');
        } else {
            this.setCustomValidity('');
        }
    });

    // Real-time validation
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';

        // Gather form data
        const formData = new FormData(form);
        const data = {
            fullName: formData.get('fullName'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            date: formData.get('date'),
            timezone: formData.get('timezone'),
            time: formData.get('time'),
            comments: formData.get('comments'),
            submissionTime: new Date().toISOString()
        };

        try {
            // Here you would typically send this data to your server
            const response = await fetch('/api/submit-appointment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                showNotification('success', 'Thank you! Your appointment request has been sent. We will contact you shortly to confirm the appointment.');
                form.reset();
                form.classList.remove('was-validated');
            } else {
                if (result.errors) {
                    const errorMessages = result.errors
                        .map(error => error.msg)
                        .join('\n');
                    showNotification('error', errorMessages);
                } else {
                    showNotification('error', result.message || 'Something went wrong. Please try again.');
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            showNotification('error', 'Unable to submit form. Please try again later or contact us directly at info@preciseotax.com');
        } finally {
            // Restore button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    });
});

// Input validation
function validateInput(input) {
    if (input.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value)) {
            input.setCustomValidity('Please enter a valid email address');
        } else {
            input.setCustomValidity('');
        }
    }
    
    if (input.name === 'phone') {
        const phoneRegex = /^\(\d{3}\) \d{3}-\d{4}$/;
        if (!phoneRegex.test(input.value)) {
            input.setCustomValidity('Please enter a valid phone number: (XXX) XXX-XXXX');
        } else {
            input.setCustomValidity('');
        }
    }
}

// Show notification
function showNotification(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4`;
    alertDiv.style.zIndex = '1050';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);

    // Remove the alert after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Simulate server request (remove this in production)
function simulateServerRequest(data) {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            // Simulate 90% success rate
            if (Math.random() < 0.9) {
                resolve();
            } else {
                reject(new Error('Server error'));
            }
        }, 1500);
    });
}

// Time zone handling
const timezoneSelect = document.querySelector('select[name="timezone"]');
if (timezoneSelect) {
    // Try to detect user's timezone
    try {
        const userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (userTimeZone.includes('America')) {
            if (userTimeZone.includes('New_York')) timezoneSelect.value = 'ET';
            else if (userTimeZone.includes('Chicago')) timezoneSelect.value = 'CT';
            else if (userTimeZone.includes('Denver')) timezoneSelect.value = 'MT';
            else if (userTimeZone.includes('Los_Angeles')) timezoneSelect.value = 'PT';
        }
    } catch (e) {
        console.log('Unable to detect timezone');
    }
}

// Google Maps interaction
const mapContainer = document.querySelector('.map-container');
if (mapContainer) {
    mapContainer.addEventListener('click', function() {
        const iframe = this.querySelector('iframe');
        iframe.style.pointerEvents = 'auto';
    });
    
    mapContainer.addEventListener('mouseleave', function() {
        const iframe = this.querySelector('iframe');
        iframe.style.pointerEvents = 'none';
    });
}

// Add to existing navigation links
const navLinks = `
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <a class="nav-link" href="index.html">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="about.html">About</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="faq.html">FAQs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="contact.html">Contact</a>
        </li>
    </ul>
`;
