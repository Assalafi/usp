/**
 * Profile form validation script
 * Validates all required fields before form submission
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add custom styles for validation feedback
    addCustomStyles();
    
    // Get the form element
    const profileForm = document.getElementById('myform');

    // Add submit event listener to the form
    profileForm.addEventListener('submit', function(event) {
        // Prevent default form submission
        event.preventDefault();
        
        // Array to store names of unfilled or invalid fields
        const invalidFields = [];
        
        // Validate all required fields
        const requiredFields = profileForm.querySelectorAll('[required]');
        validateRequiredFields(requiredFields, invalidFields);
        
        // Validate email fields (even non-required ones that have content)
        const emailFields = profileForm.querySelectorAll('input[type="text"][id$="email"]');
        validateEmailFields(emailFields, invalidFields);
        
        // If there are invalid fields, show error message
        if (invalidFields.length > 0) {
            showValidationErrors(invalidFields, profileForm);
        } else {
            // If all validations pass, submit the form
            profileForm.submit();
        }
    });
    
    // Add input event listeners to clear validation errors when user edits fields
    addInputListeners(profileForm);
});

/**
 * Validates all required fields
 */
function validateRequiredFields(requiredFields, invalidFields) {
    requiredFields.forEach(function(field) {
        // Skip disabled fields as they can't be edited
        if (field.disabled) return;
        
        // Check if field is empty
        if (!field.value.trim()) {
            // Add field to invalid fields list with reason
            addInvalidField(field, invalidFields, 'required');
            
            // Add visual indication
            markFieldAsInvalid(field, 'This field is required');
        } else {
            // Remove error indication if field has value
            markFieldAsValid(field);
        }
    });
}

/**
 * Validates email format for email fields
 */
function validateEmailFields(emailFields, invalidFields) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    emailFields.forEach(function(field) {
        // Skip empty non-required fields
        if (!field.value.trim() && !field.hasAttribute('required')) {
            return;
        }
        
        // Check if email format is valid
        if (field.value.trim() && !emailRegex.test(field.value.trim())) {
            // Add field to invalid fields list with reason
            addInvalidField(field, invalidFields, 'invalid email');
            
            // Add visual indication
            markFieldAsInvalid(field, 'Please enter a valid email address');
        }
    });
}

/**
 * Adds a field to the invalid fields list
 */
function addInvalidField(field, invalidFields, reason) {
    // Get the field label
    let fieldLabel = '';
    const labelElement = field.closest('.form-group').querySelector('label');
    if (labelElement) {
        // Remove the asterisk from the label text
        fieldLabel = labelElement.textContent.replace(/\s*\*$/, '').trim();
    } else {
        // Use field ID if label not found
        fieldLabel = field.id;
    }
    
    invalidFields.push({
        label: fieldLabel,
        id: field.id,
        reason: reason
    });
}

/**
 * Marks a field as invalid with custom styling and feedback message
 */
function markFieldAsInvalid(field, message) {
    field.classList.add('is-invalid');
    
    // Add or update feedback message
    let feedbackDiv = field.parentNode.querySelector('.invalid-feedback');
    if (!feedbackDiv) {
        feedbackDiv = document.createElement('div');
        feedbackDiv.className = 'invalid-feedback';
        field.parentNode.appendChild(feedbackDiv);
    }
    feedbackDiv.textContent = message;
}

/**
 * Marks a field as valid by removing validation styling
 */
function markFieldAsValid(field) {
    field.classList.remove('is-invalid');
    const feedbackDiv = field.parentNode.querySelector('.invalid-feedback');
    if (feedbackDiv) {
        feedbackDiv.remove();
    }
}

/**
 * Displays validation errors to the user
 */
function showValidationErrors(invalidFields, form) {
    // Create alert message with list of unfilled fields
    let alertMessage = 'Please fix the following issues:\n\n';
    
    invalidFields.forEach(function(field, index) {
        let reasonText = field.reason === 'required' ? 'is required' : 'has invalid format';
        alertMessage += `${index + 1}. ${field.label} ${reasonText}\n`;
    });
    
    // Show alert to user
    alert(alertMessage);
    
    // Focus and activate tab for first invalid field
    navigateToFirstInvalidField(form);
}

/**
 * Navigates to the first invalid field and activates its tab
 */
function navigateToFirstInvalidField(form) {
    const firstInvalidField = form.querySelector('.is-invalid');
    if (firstInvalidField) {
        firstInvalidField.focus();
        
        // Scroll to the tab containing the first invalid field
        const tabPane = firstInvalidField.closest('.tab-pane');
        if (tabPane) {
            const tabId = tabPane.id;
            const tabLink = document.querySelector(`[href="#${tabId}"]`);
            if (tabLink) {
                tabLink.click();
            }
        }
    }
}

/**
 * Adds input event listeners to form fields
 */
function addInputListeners(form) {
    // Listen to all inputs to clear validation errors on edit
    const allFields = form.querySelectorAll('input, select, textarea');
    allFields.forEach(function(field) {
        field.addEventListener('input', function() {
            if (field.value.trim()) {
                markFieldAsValid(field);
            }
        });
    });
}

/**
 * Adds custom CSS styles for validation
 */
function addCustomStyles() {
    const styleEl = document.createElement('style');
    styleEl.textContent = `
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }
        .invalid-feedback {
            display: block !important;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        
        /* Tab styling for sections with errors */
        .has-error-tab {
            position: relative;
        }
        .has-error-tab::after {
            content: '!';
            position: absolute;
            top: -5px;
            right: -5px;
            width: 16px;
            height: 16px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    `;
    document.head.appendChild(styleEl);
}
