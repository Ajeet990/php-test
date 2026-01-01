/**
 * Admin.js - Common Admin Panel Functions
 * Handles common functionality across all admin pages
 */

$(document).ready(function() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Show loading overlay
    window.showLoading = function() {
        $('#loadingOverlay').addClass('active');
    };

    // Hide loading overlay
    window.hideLoading = function() {
        $('#loadingOverlay').removeClass('active');
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

    // Format date for display
    window.formatDate = function(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    };

    // Format datetime for display
    window.formatDateTime = function(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    // Truncate text
    window.truncateText = function(text, length = 50) {
        if (!text) return '';
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    };

    // Get file icon based on extension
    window.getFileIcon = function(filename) {
        if (!filename) return 'fa-file';
        
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': 'fa-file-pdf',
            'doc': 'fa-file-word',
            'docx': 'fa-file-word',
            'txt': 'fa-file-alt',
            'jpg': 'fa-file-image',
            'jpeg': 'fa-file-image',
            'png': 'fa-file-image',
            'gif': 'fa-file-image',
        };
        
        return icons[ext] || 'fa-file';
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

    // Confirmation dialog
    window.showConfirmDialog = function(title, text, confirmButtonText = 'Yes, proceed!') {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Cancel'
        });
    };

    // Delete confirmation
    window.showDeleteConfirmation = function(itemName = 'this item') {
        return Swal.fire({
            title: 'Are you sure?',
            text: `You want to delete ${itemName}? This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        });
    };

    // Handle AJAX errors
    window.handleAjaxError = function(xhr, action = 'perform this action') {
        hideLoading();
        
        let message = `Failed to ${action}. Please try again.`;
        
        if (xhr.status === 422) {
            message = 'Validation error. Please check your input.';
        } else if (xhr.status === 404) {
            message = 'Resource not found.';
        } else if (xhr.status === 500) {
            message = 'Server error. Please try again later.';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        
        showErrorAlert(message);
    };

    // Debounce function for search
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Copy to clipboard
    window.copyToClipboard = function(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = 0;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Text copied to clipboard',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    };

    // Generate random string
    window.generateRandomString = function(length = 10) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
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

    // Format file size
    window.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };

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

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 5000);

    // Close button for alerts
    $('.alert .btn-close').on('click', function() {
        $(this).closest('.alert').fadeOut();
    });

    // Tooltip initialization (if using Bootstrap tooltips)
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Popover initialization (if using Bootstrap popovers)
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Console log to confirm admin.js loaded
    console.log('Admin.js loaded successfully');
});