/**
 * App.js - Common Functions for Public Pages
 * Handles common functionality across public-facing pages
 */

$(document).ready(function() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Show loading indicator
    window.showLoading = function() {
        // You can add a custom loading indicator here if needed
    };

    // Hide loading indicator
    window.hideLoading = function() {
        // Hide loading indicator
    };

    // Success notification
    window.showSuccessAlert = function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            confirmButtonColor: '#1cc88a',
            timer: 3000,
            timerProgressBar: true
        });
    };

    // Error notification
    window.showErrorAlert = function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            confirmButtonColor: '#e74a3b'
        });
    };

    // Warning notification
    window.showWarningAlert = function(message) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: message,
            confirmButtonColor: '#f6c23e'
        });
    };

    // Clear form validation errors
    window.clearValidationErrors = function(formId) {
        $(`#${formId} .form-control, #${formId} .form-select`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('');
    };

    // Display validation errors
    window.displayValidationErrors = function(formId, errors) {
        $.each(errors, function(field, messages) {
            const $field = $(`#${formId} [name="${field}"]`);
            $field.addClass('is-invalid');
            $field.siblings('.invalid-feedback').text(messages[0]);
            
            // Handle radio buttons
            if ($field.attr('type') === 'radio') {
                $field.closest('.mb-3').find('.invalid-feedback').text(messages[0]).show();
            }
        });
    };

    // Remove validation error on input
    $(document).on('change input', '.form-control, .form-select, .form-check-input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
        
        // Handle radio buttons
        if ($(this).attr('type') === 'radio') {
            $(this).closest('.mb-3').find('.invalid-feedback').hide();
        }
    });

    // Validate file size
    window.validateFileSize = function(file, maxSizeInMB) {
        const maxSizeInBytes = maxSizeInMB * 1024 * 1024;
        if (file.size > maxSizeInBytes) {
            showWarningAlert(`File size must be less than ${maxSizeInMB}MB`);
            return false;
        }
        return true;
    };

    // Validate file type
    window.validateFileType = function(file, allowedTypes) {
        const fileType = file.type;
        const fileName = file.name;
        const fileExt = fileName.split('.').pop().toLowerCase();
        
        let isValid = false;
        allowedTypes.forEach(type => {
            if (type.includes('/')) {
                // MIME type
                if (fileType === type || fileType.startsWith(type.replace('*', ''))) {
                    isValid = true;
                }
            } else {
                // Extension
                if (fileExt === type.replace('.', '')) {
                    isValid = true;
                }
            }
        });
        
        if (!isValid) {
            showWarningAlert(`Invalid file type. Allowed types: ${allowedTypes.join(', ')}`);
        }
        
        return isValid;
    };

    // Format file size
    window.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };

    // Escape HTML
    window.escapeHtml = function(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    };

    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 5000);

    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Console log to confirm app.js loaded
    console.log('App.js loaded successfully');
});