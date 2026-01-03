/**
 * Contact Form - AJAX Handler
 * Handles form submission with validation and SweetAlert2 notifications
 */

$(document).ready(function() {
    // Set up CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle contact form submission
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous validation errors
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Get form data
        const formData = new FormData(this);
        
        // Disable submit button and show loading
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');
        
        // Submit form via AJAX
        $.ajax({
            url: '/contact-us',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#1cc88a',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reset form
                        $('#contactForm')[0].reset();
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Something went wrong. Please try again.',
                        confirmButtonColor: '#e74a3b'
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = [];
                    
                    // Display validation errors
                    $.each(errors, function(field, messages) {
                        const $field = $(`[name="${field}"]`);
                        
                        if ($field.length) {
                            $field.addClass('is-invalid');
                            $field.siblings('.invalid-feedback').text(messages[0]);
                            
                            // For radio buttons
                            if ($field.attr('type') === 'radio') {
                                $field.closest('.mb-3').find('.invalid-feedback').text(messages[0]).show();
                            }
                        }
                        
                        // Collect error messages
                        errorMessages.push(messages[0]);
                    });
                    
                    // Show validation error alert with specific message
                    let alertMessage = 'Please check the form and correct the errors.';
                    
                    // Highlight duplicate email/phone errors
                    if (errorMessages.some(msg => msg.includes('email'))) {
                        alertMessage = errorMessages.find(msg => msg.includes('email'));
                    } else if (errorMessages.some(msg => msg.includes('phone'))) {
                        alertMessage = errorMessages.find(msg => msg.includes('phone'));
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: alertMessage,
                        confirmButtonColor: '#e74a3b'
                    });
                } else {
                    // Server error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'An error occurred. Please try again.',
                        confirmButtonColor: '#e74a3b'
                    });
                }
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
    
    // Remove validation error on input change
    $('.form-control, .form-select, .form-check-input').on('change input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
        
        // For radio buttons
        if ($(this).attr('type') === 'radio') {
            $(this).closest('.mb-3').find('.invalid-feedback').hide();
        }
    });
    
    // File input preview (optional enhancement)
    $('#profile_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            if (file.size > 2048 * 1024) { // 2 MB
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: 'Profile image must be less than 2MB',
                    confirmButtonColor: '#f6c23e'
                });
                $(this).val('');
            }
        }
    });
    
    $('#additional_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            if (file.size > 5120 * 1024) { // 5 MB
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: 'Additional file must be less than 5MB',
                    confirmButtonColor: '#f6c23e'
                });
                $(this).val('');
            }
        }
    });
});