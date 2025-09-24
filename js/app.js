document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initializeYearSelector();
    initializeTimeSlotPicker();
    initializeFormHandler();
    initializeSmoothScrolling();
});

function initializeYearSelector() {
    const yearSelect = document.getElementById('vehicle_year');
    const currentYear = new Date().getFullYear();
    
    // Add years from current year back to 1990
    for (let year = currentYear; year >= 1990; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }
}

function initializeTimeSlotPicker() {
    const timeSlotPicker = document.getElementById('time-slot-picker');
    const preferredTimeInput = document.getElementById('preferred_time');
    
    // Generate time slots for the next 7 days
    const timeSlots = generateTimeSlots();
    
    timeSlots.forEach(slot => {
        const slotElement = document.createElement('div');
        slotElement.className = 'time-slot';
        slotElement.textContent = slot.display;
        slotElement.dataset.value = slot.value;
        
        // Mark some slots as unavailable (simulate booking system)
        if (Math.random() < 0.3) {
            slotElement.classList.add('unavailable');
        } else {
            slotElement.addEventListener('click', function() {
                // Remove selection from other slots
                document.querySelectorAll('.time-slot.selected').forEach(el => {
                    el.classList.remove('selected');
                });
                
                // Select this slot
                this.classList.add('selected');
                preferredTimeInput.value = this.dataset.value;
            });
        }
        
        timeSlotPicker.appendChild(slotElement);
    });
}

function generateTimeSlots() {
    const slots = [];
    const today = new Date();
    
    // Business hours: 8 AM to 6 PM, Monday to Friday; 8 AM to 4 PM Saturday
    const businessHours = {
        1: { start: 8, end: 18 }, // Monday
        2: { start: 8, end: 18 }, // Tuesday
        3: { start: 8, end: 18 }, // Wednesday
        4: { start: 8, end: 18 }, // Thursday
        5: { start: 8, end: 18 }, // Friday
        6: { start: 8, end: 16 }, // Saturday
        0: null // Sunday - closed
    };
    
    // Generate slots for next 7 days
    for (let dayOffset = 0; dayOffset < 7; dayOffset++) {
        const date = new Date(today);
        date.setDate(today.getDate() + dayOffset);
        date.setHours(0, 0, 0, 0);
        
        const dayOfWeek = date.getDay();
        const hours = businessHours[dayOfWeek];
        
        if (!hours) continue; // Skip Sunday
        
        // Generate hourly slots
        for (let hour = hours.start; hour < hours.end; hour++) {
            const slotDate = new Date(date);
            slotDate.setHours(hour, 0, 0, 0);
            
            // Skip past time slots for today
            if (dayOffset === 0 && slotDate <= today) continue;
            
            const display = formatTimeSlot(slotDate);
            const value = slotDate.toISOString();
            
            slots.push({ display, value });
        }
    }
    
    return slots;
}

function formatTimeSlot(date) {
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    const dayName = days[date.getDay()];
    const month = months[date.getMonth()];
    const day = date.getDate();
    const hour = date.getHours();
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    
    return `${dayName} ${month} ${day}, ${displayHour}:00 ${ampm}`;
}

function initializeFormHandler() {
    const form = document.getElementById('quote-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        submitForm();
    });
}

function validateForm() {
    const requiredFields = [
        'name', 'phone', 'vehicle_year', 'vehicle_make', 
        'vehicle_model', 'service_type', 'preferred_time'
    ];
    
    let isValid = true;
    const errors = [];
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            isValid = false;
            errors.push(`${field.previousElementSibling.textContent} is required`);
            field.style.borderColor = '#e74c3c';
        } else {
            field.style.borderColor = '#ddd';
        }
    });
    
    // Validate phone number format
    const phone = document.getElementById('phone').value;
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    if (phone && !phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''))) {
        isValid = false;
        errors.push('Please enter a valid phone number');
        document.getElementById('phone').style.borderColor = '#e74c3c';
    }
    
    // Validate email if provided
    const email = document.getElementById('email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
        isValid = false;
        errors.push('Please enter a valid email address');
        document.getElementById('email').style.borderColor = '#e74c3c';
    }
    
    if (!isValid) {
        showMessage(errors.join('<br>'), 'error');
    }
    
    return isValid;
}

function submitForm() {
    const form = document.getElementById('quote-form');
    const submitButton = form.querySelector('.submit-button');
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';
    form.classList.add('loading');
    
    // Clear any previous messages
    clearMessages();
    
    // Create FormData object
    const formData = new FormData(form);
    
    // Submit the form
    fetch('/quote/quote_intake_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || 'Your quote request has been submitted successfully! You will receive a text message with your quote shortly.', 'success');
            form.reset();
            
            // Reset time slot picker
            document.querySelectorAll('.time-slot.selected').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Scroll to top of form to show success message
            document.getElementById('quote').scrollIntoView({ behavior: 'smooth' });
        } else {
            showMessage(data.message || 'There was an error processing your request. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('There was an error processing your request. Please try again.', 'error');
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.textContent = 'Get Instant Quote';
        form.classList.remove('loading');
    });
}

function showMessage(message, type) {
    clearMessages();
    
    const messageDiv = document.createElement('div');
    messageDiv.className = type === 'error' ? 'error-message' : 'success-message';
    messageDiv.innerHTML = message;
    
    const form = document.getElementById('quote-form');
    form.insertBefore(messageDiv, form.firstChild);
}

function clearMessages() {
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(msg => msg.remove());
}

function initializeSmoothScrolling() {
    // Add smooth scrolling to navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Utility function to format phone numbers as user types
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        
        e.target.value = value;
    });
});