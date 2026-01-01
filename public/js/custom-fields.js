/**
 * Custom-Fields.js - Custom Fields Management
 * Handles all custom field CRUD operations
 */

$(document).ready(function() {
    // Load custom fields on page load
    loadCustomFields();

    // Load custom fields function
    function loadCustomFields() {
        showLoading();

        $.ajax({
            url: '/admin/custom-fields/list',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayCustomFields(response.data);
                } else {
                    showErrorAlert(response.message || 'Failed to load custom fields');
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, 'load custom fields');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    // Display custom fields in table
    function displayCustomFields(fields) {
        const tbody = $('#customFieldsTableBody');
        tbody.empty();

        if (fields.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-cog"></i>
                            <h4>No Custom Fields</h4>
                            <p>Create your first custom field to extend contact information</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        fields.forEach(field => {
            const statusBadge = field.is_active 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-secondary">Inactive</span>';

            const fieldType = field.field_type.charAt(0).toUpperCase() + field.field_type.slice(1);
            
            let optionsDisplay = 'N/A';
            if (field.field_type === 'select' && field.field_options) {
                if (Array.isArray(field.field_options)) {
                    optionsDisplay = field.field_options.slice(0, 3).join(', ');
                    if (field.field_options.length > 3) {
                        optionsDisplay += ` (+${field.field_options.length - 3} more)`;
                    }
                }
            }

            const row = `
                <tr>
                    <td>${field.id}</td>
                    <td><strong>${escapeHtml(field.field_label)}</strong></td>
                    <td><code>${escapeHtml(field.field_name)}</code></td>
                    <td><span class="badge bg-primary">${fieldType}</span></td>
                    <td>${statusBadge}</td>
                    <td><small class="text-muted">${formatDate(field.created_at)}</small></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-warning edit-field" data-id="${field.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-field" data-id="${field.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Show/hide options field based on field type
    function toggleOptionsField(modalPrefix) {
        const fieldType = $(`#${modalPrefix}FieldType`).val();
        const optionsContainer = $(`#${modalPrefix}OptionsContainer`);
        
        if (fieldType === 'select') {
            optionsContainer.slideDown();
            $(`#${modalPrefix}FieldOptions`).prop('required', true);
        } else {
            optionsContainer.slideUp();
            $(`#${modalPrefix}FieldOptions`).prop('required', false);
        }
    }

    // Field type change handlers
    $('#addFieldType').on('change', function() {
        toggleOptionsField('add');
    });

    $('#editFieldType').on('change', function() {
        toggleOptionsField('edit');
    });

    // Add custom field form submission
    $('#addCustomFieldForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors('addCustomFieldForm');

        const formData = {
            field_label: $('#addFieldLabel').val(),
            field_type: $('#addFieldType').val(),
            field_options: $('#addFieldOptions').val(),
            is_active: $('#addIsActive').is(':checked') ? 1 : 0,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        showLoading();

        $.ajax({
            url: '/admin/custom-fields',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#addCustomFieldModal').modal('hide');
                    $('#addCustomFieldForm')[0].reset();
                    $('#addOptionsContainer').hide();
                    showSuccessAlert(response.message);
                    loadCustomFields();
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors('addCustomFieldForm', xhr.responseJSON.errors);
                    showErrorAlert('Please check the form for errors');
                } else {
                    handleAjaxError(xhr, 'create custom field');
                }
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Edit custom field
    $(document).on('click', '.edit-field', function() {
        const fieldId = $(this).data('id');
        showLoading();

        $.ajax({
            url: '/admin/custom-fields/list',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const field = response.data.find(f => f.id === fieldId);
                    if (field) {
                        populateEditForm(field);
                        $('#editCustomFieldModal').modal('show');
                    } else {
                        showErrorAlert('Custom field not found');
                    }
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, 'load custom field');
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Populate edit form
    function populateEditForm(field) {
        $('#editCustomFieldId').val(field.id);
        $('#editFieldLabel').val(field.field_label);
        $('#editFieldType').val(field.field_type);
        $('#editIsActive').prop('checked', field.is_active);

        // Handle options
        if (field.field_type === 'select' && field.field_options) {
            const optionsString = Array.isArray(field.field_options) 
                ? field.field_options.join(', ') 
                : field.field_options;
            $('#editFieldOptions').val(optionsString);
            $('#editOptionsContainer').show();
        } else {
            $('#editFieldOptions').val('');
            $('#editOptionsContainer').hide();
        }
    }

    // Update custom field form submission
    $('#editCustomFieldForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors('editCustomFieldForm');

        const fieldId = $('#editCustomFieldId').val();
        const formData = {
            field_label: $('#editFieldLabel').val(),
            field_type: $('#editFieldType').val(),
            field_options: $('#editFieldOptions').val(),
            is_active: $('#editIsActive').is(':checked') ? 1 : 0,
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT'
        };

        showLoading();

        $.ajax({
            url: `/admin/custom-fields/${fieldId}`,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editCustomFieldModal').modal('hide');
                    showSuccessAlert(response.message);
                    loadCustomFields();
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors('editCustomFieldForm', xhr.responseJSON.errors);
                    showErrorAlert('Please check the form for errors');
                } else {
                    handleAjaxError(xhr, 'update custom field');
                }
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Delete custom field
    $(document).on('click', '.delete-field', function() {
        const fieldId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'Deleting this custom field will remove all associated data from contacts!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                $.ajax({
                    url: `/admin/custom-fields/${fieldId}`,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccessAlert(response.message);
                            loadCustomFields();
                        } else {
                            showErrorAlert(response.message);
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'delete custom field');
                    },
                    complete: function() {
                        hideLoading();
                    }
                });
            }
        });
    });

    // Reset modal forms when closed
    $('#addCustomFieldModal').on('hidden.bs.modal', function() {
        $('#addCustomFieldForm')[0].reset();
        $('#addOptionsContainer').hide();
        clearValidationErrors('addCustomFieldForm');
    });

    $('#editCustomFieldModal').on('hidden.bs.modal', function() {
        $('#editCustomFieldForm')[0].reset();
        $('#editOptionsContainer').hide();
        clearValidationErrors('editCustomFieldForm');
    });

    // Validate options format
    function validateOptionsFormat(options) {
        if (!options || options.trim() === '') {
            return { valid: false, message: 'Options are required for select type' };
        }

        const optionsArray = options.split(',').map(opt => opt.trim()).filter(opt => opt !== '');
        
        if (optionsArray.length < 2) {
            return { valid: false, message: 'Please provide at least 2 options separated by commas' };
        }

        return { valid: true, options: optionsArray };
    }

    // Add validation before form submission
    $('#addCustomFieldForm, #editCustomFieldForm').on('submit', function(e) {
        const formId = $(this).attr('id');
        const prefix = formId === 'addCustomFieldForm' ? 'add' : 'edit';
        const fieldType = $(`#${prefix}FieldType`).val();
        
        if (fieldType === 'select') {
            const options = $(`#${prefix}FieldOptions`).val();
            const validation = validateOptionsFormat(options);
            
            if (!validation.valid) {
                e.preventDefault();
                $(`#${prefix}FieldOptions`).addClass('is-invalid');
                $(`#${prefix}FieldOptions`).siblings('.invalid-feedback').text(validation.message);
                return false;
            }
        }
    });

    // Clear validation on options input
    $('#addFieldOptions, #editFieldOptions').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
    });

    // Add helper text for field types
    const fieldTypeDescriptions = {
        text: 'Single line text input',
        textarea: 'Multi-line text input',
        date: 'Date picker',
        number: 'Numeric input',
        select: 'Dropdown with predefined options'
    };

    $('#addFieldType, #editFieldType').on('change', function() {
        const selectedType = $(this).val();
        const description = fieldTypeDescriptions[selectedType] || '';
        
        // You can add a helper text element if needed
        console.log('Field type:', selectedType, '-', description);
    });

    console.log('Custom-Fields.js loaded successfully');
});