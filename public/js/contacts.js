/**
 * Contacts.js - Contact CRUD Operations
 * Handles all contact management functionality
 */

$(document).ready(function() {
    let currentPage = 1;
    let selectedContacts = [];

    // Load contacts on page load
    loadContacts();

    // Load contacts function
    function loadContacts(page = 1) {
        showLoading();

        const filters = {
            name: $('#filterName').val(),
            email: $('#filterEmail').val(),
            gender: $('#filterGender').val(),
            page: page
        };

        $.ajax({
            url: '/admin/contacts/list',
            type: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    displayContacts(response.data.data);
                    displayPagination(response.data);
                    currentPage = page;
                } else {
                    showErrorAlert(response.message || 'Failed to load contacts');
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, 'load contacts');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    // Display contacts in table
    function displayContacts(contacts) {
        const tbody = $('#contactsTableBody');
        tbody.empty();

        if (contacts.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h4>No Contacts Found</h4>
                            <p>Start by adding your first contact</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        contacts.forEach(contact => {
            const profileImage = contact.profile_image 
                ? `<img src="/storage/${contact.profile_image}" class="image-preview" alt="${escapeHtml(contact.name)}">` 
                : '<i class="fas fa-user-circle fa-2x text-muted"></i>';

            const row = `
                <tr data-contact-id="${contact.id}">
                    <td>
                        <input type="checkbox" class="contact-checkbox" value="${contact.id}">
                    </td>
                    <td>${contact.id}</td>
                    <td>${escapeHtml(contact.name)}</td>
                    <td>${escapeHtml(contact.email)}</td>
                    <td>${escapeHtml(contact.phone)}</td>
                    <td>
                        <span class="badge badge-${contact.gender === 'male' ? 'danger' : contact.gender === 'female' ? 'danger' : 'secondary'}">
                            ${contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1)}
                        </span>
                    </td>
                    <td>${profileImage}</td>
                    
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-info view-contact" data-id="${contact.id}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning edit-contact" data-id="${contact.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-contact" data-id="${contact.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-success merge-single-contact" data-id="${contact.id}" title="Select for Merge">
                                <i class="fas fa-compress-arrows-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Display pagination
    function displayPagination(data) {
        const container = $('#paginationContainer');
        container.empty();

        if (data.last_page <= 1) return;

        let html = '<nav><ul class="pagination justify-content-center">';

        // Previous button
        html += `
            <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `
                    <li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next button
        html += `
            <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a>
            </li>
        `;

        html += '</ul></nav>';
        container.html(html);
    }

    // Pagination click handler
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            loadContacts(page);
        }
    });

    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadContacts(1);
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterForm')[0].reset();
        loadContacts(1);
    });

    // Add contact form submission
    $('#addContactForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors('addContactForm');

        const formData = new FormData(this);
        showLoading();

        $.ajax({
            url: '/admin/contacts',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addContactModal').modal('hide');
                    $('#addContactForm')[0].reset();
                    showSuccessAlert(response.message);
                    loadContacts(currentPage);
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors('addContactForm', xhr.responseJSON.errors);
                    showErrorAlert('Please check the form for errors');
                } else {
                    handleAjaxError(xhr, 'create contact');
                }
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // View contact
    $(document).on('click', '.view-contact', function() {
        const contactId = $(this).data('id');
        showLoading();

        $.ajax({
            url: `/admin/contacts/${contactId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayContactDetails(response.data);
                    $('#viewContactModal').modal('show');
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, 'load contact details');
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Display contact details
    function displayContactDetails(contact) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="contact-detail-label">Name</div>
                    <div class="contact-detail-value">${escapeHtml(contact.name)}</div>
                    
                    <div class="contact-detail-label">Email</div>
                    <div class="contact-detail-value">${escapeHtml(contact.email)}</div>
                    
                    <div class="contact-detail-label">Phone</div>
                    <div class="contact-detail-value">${escapeHtml(contact.phone)}</div>
                    
                    <div class="contact-detail-label">Gender</div>
                    <div class="contact-detail-value">${contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1)}</div>
                </div>
                <div class="col-md-6">
        `;

        if (contact.profile_image) {
            html += `
                <div class="contact-detail-label">Profile Image</div>
                <div class="contact-detail-value mb-3">
                    <img src="/storage/${contact.profile_image}" class="img-fluid rounded" style="max-width: 200px;" alt="${escapeHtml(contact.name)}">
                </div>
            `;
        }

        if (contact.additional_file) {
            html += `
                <div class="contact-detail-label">Additional File</div>
                <div class="contact-detail-value">
                    <a href="/storage/${contact.additional_file}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas ${getFileIcon(contact.additional_file)}"></i> Download File
                    </a>
                </div>
            `;
        }

        html += `</div></div>`;

        // Display all emails
        if (contact.emails && contact.emails.length > 0) {
            html += `
                <hr>
                <div class="contact-detail-label">All Emails</div>
                <div class="contact-detail-value">
            `;
            contact.emails.forEach(email => {
                html += `<span class="badge bg-info me-1">${escapeHtml(email.email)} ${email.is_primary ? '(Primary)' : ''}</span>`;
            });
            html += `</div>`;
        }

        // Display all phones
        if (contact.phones && contact.phones.length > 0) {
            html += `
                <div class="contact-detail-label">All Phone Numbers</div>
                <div class="contact-detail-value">
            `;
            contact.phones.forEach(phone => {
                html += `<span class="badge bg-success me-1">${escapeHtml(phone.phone)} ${phone.is_primary ? '(Primary)' : ''}</span>`;
            });
            html += `</div>`;
        }

        // Display custom fields
        if (contact.custom_field_values && contact.custom_field_values.length > 0) {
            html += `<hr><h6>Custom Fields</h6>`;
            contact.custom_field_values.forEach(cfv => {
                html += `
                    <div class="contact-detail-label">${escapeHtml(cfv.custom_field.field_label)}</div>
                    <div class="contact-detail-value">${escapeHtml(cfv.field_value || 'N/A')}</div>
                `;
            });
        }

        html += `
            <hr>
            <small class="text-muted">Created: ${formatDateTime(contact.created_at)}</small><br>
            <small class="text-muted">Updated: ${formatDateTime(contact.updated_at)}</small>
        `;

        $('#viewContactBody').html(html);
    }

    // Edit contact
    $(document).on('click', '.edit-contact', function() {
        const contactId = $(this).data('id');
        showLoading();

        $.ajax({
            url: `/admin/contacts/${contactId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $('#editContactModal').modal('show');
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, 'load contact details');
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Populate edit form
    function populateEditForm(contact) {
        $('#editContactId').val(contact.id);
        $('#editName').val(contact.name);
        $('#editEmail').val(contact.email);
        $('#editPhone').val(contact.phone);
        $(`input[name="gender"][value="${contact.gender}"]`).prop('checked', true);

        // Show current files
        if (contact.profile_image) {
            $('#currentProfileImage').html(`
                <small class="text-muted">Current: </small>
                <img src="/storage/${contact.profile_image}" class="image-preview" alt="Current profile">
            `);
        } else {
            $('#currentProfileImage').empty();
        }

        if (contact.additional_file) {
            $('#currentAdditionalFile').html(`
                <small class="text-muted">Current: </small>
                <a href="/storage/${contact.additional_file}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fas ${getFileIcon(contact.additional_file)}"></i> View Current File
                </a>
            `);
        } else {
            $('#currentAdditionalFile').empty();
        }

        // Populate custom fields
        let customFieldsHtml = '';
        if (contact.custom_field_values && contact.custom_field_values.length > 0) {
            customFieldsHtml = '<hr><h6 class="mb-3">Additional Information</h6>';
            contact.custom_field_values.forEach(cfv => {
                const field = cfv.custom_field;
                customFieldsHtml += `<div class="mb-3">`;
                customFieldsHtml += `<label class="form-label">${escapeHtml(field.field_label)}</label>`;
                
                if (field.field_type === 'text') {
                    customFieldsHtml += `<input type="text" class="form-control" name="custom_field_${field.id}" value="${escapeHtml(cfv.field_value || '')}">`;
                } else if (field.field_type === 'textarea') {
                    customFieldsHtml += `<textarea class="form-control" name="custom_field_${field.id}" rows="3">${escapeHtml(cfv.field_value || '')}</textarea>`;
                } else if (field.field_type === 'date') {
                    customFieldsHtml += `<input type="date" class="form-control" name="custom_field_${field.id}" value="${cfv.field_value || ''}">`;
                } else if (field.field_type === 'number') {
                    customFieldsHtml += `<input type="number" class="form-control" name="custom_field_${field.id}" value="${cfv.field_value || ''}">`;
                } else if (field.field_type === 'select' && field.field_options) {
                    customFieldsHtml += `<select class="form-select" name="custom_field_${field.id}">`;
                    customFieldsHtml += `<option value="">-- Select --</option>`;
                    field.field_options.forEach(option => {
                        const selected = option === cfv.field_value ? 'selected' : '';
                        customFieldsHtml += `<option value="${escapeHtml(option)}" ${selected}>${escapeHtml(option)}</option>`;
                    });
                    customFieldsHtml += `</select>`;
                }
                
                customFieldsHtml += `</div>`;
            });
        }
        $('#editCustomFieldsContainer').html(customFieldsHtml);
    }

    // Update contact form submission
    $('#editContactForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors('editContactForm');

        const contactId = $('#editContactId').val();
        const formData = new FormData(this);
        showLoading();

        $.ajax({
            url: `/admin/contacts/${contactId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editContactModal').modal('hide');
                    showSuccessAlert(response.message);
                    loadContacts(currentPage);
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors('editContactForm', xhr.responseJSON.errors);
                    showErrorAlert('Please check the form for errors');
                } else {
                    handleAjaxError(xhr, 'update contact');
                }
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Delete contact
    $(document).on('click', '.delete-contact', function() {
        const contactId = $(this).data('id');
        
        showDeleteConfirmation('this contact').then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                $.ajax({
                    url: `/admin/contacts/${contactId}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            showSuccessAlert(response.message);
                            loadContacts(currentPage);
                        } else {
                            showErrorAlert(response.message);
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'delete contact');
                    },
                    complete: function() {
                        hideLoading();
                    }
                });
            }
        });
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.contact-checkbox').prop('checked', $(this).is(':checked'));
        updateSelectedContacts();
    });

    // Individual checkbox change
    $(document).on('change', '.contact-checkbox', function() {
        updateSelectedContacts();
    });

    // Update selected contacts
    function updateSelectedContacts() {
        selectedContacts = [];
        $('.contact-checkbox:checked').each(function() {
            selectedContacts.push($(this).val());
        });

        if (selectedContacts.length === 2) {
            $('#selectedCount').text(selectedContacts.length);
            $('#mergeButtonContainer').fadeIn();
        } else {
            $('#mergeButtonContainer').fadeOut();
        }

        // Update select all checkbox state
        const totalCheckboxes = $('.contact-checkbox').length;
        const checkedCheckboxes = $('.contact-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
    }

    // Reset modal forms when closed
    $('#addContactModal, #editContactModal').on('hidden.bs.modal', function() {
        const formId = $(this).find('form').attr('id');
        $(`#${formId}`)[0].reset();
        clearValidationErrors(formId);
    });

    // Single contact merge button click
$(document).on('click', '.merge-single-contact', function() {
    const contactId = $(this).data('id');
    const $checkbox = $(`.contact-checkbox[value="${contactId}"]`);
    
    // Toggle checkbox
    $checkbox.prop('checked', !$checkbox.is(':checked'));
    updateSelectedContacts();
    
    // Visual feedback
    if ($checkbox.is(':checked')) {
        $(this).removeClass('btn-success').addClass('btn-primary');
    } else {
        $(this).removeClass('btn-primary').addClass('btn-success');
    }
});

// Update merge button states when checkboxes change
function updateMergeButtonStates() {
    $('.merge-single-contact').each(function() {
        const contactId = $(this).data('id');
        const isChecked = $(`.contact-checkbox[value="${contactId}"]`).is(':checked');
        
        if (isChecked) {
            $(this).removeClass('btn-success').addClass('btn-primary');
        } else {
            $(this).removeClass('btn-primary').addClass('btn-success');
        }
    });
}

// Update the existing updateSelectedContacts function to include button state update
const originalUpdateSelectedContacts = updateSelectedContacts;
updateSelectedContacts = function() {
    originalUpdateSelectedContacts();
    updateMergeButtonStates();
};

    console.log('Contacts.js loaded successfully');
});