/**
 * Merge.js - Contact Merge Functionality
 * Handles contact merging with preview and confirmation
 */

$(document).ready(function() {
    let selectedMasterContactId = null;
    let mergeData = null;

    // Merge contacts button click
    $('#mergeContactsBtn').on('click', function() {
        const selectedContacts = [];
        $('.contact-checkbox:checked').each(function() {
            selectedContacts.push($(this).val());
        });

        if (selectedContacts.length !== 2) {
            showWarningAlert('Please select exactly 2 contacts to merge');
            return;
        }

        initiateMerge(selectedContacts[0], selectedContacts[1]);
    });

    // Initiate merge - get preview data
    function initiateMerge(contactId1, contactId2) {
        showLoading();

        $.ajax({
            url: '/admin/contacts/merge/initiate',
            type: 'POST',
            data: {
                contact_id_1: contactId1,
                contact_id_2: contactId2,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    mergeData = response.data;
                    displayMergePreview(response.data);
                    $('#mergePreviewModal').modal('show');
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, 'initiate merge');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    // Display merge preview
    function displayMergePreview(data) {
        const container = $('#mergePreviewContainer');
        container.empty();

        // Contact 1 Card
        const contact1Card = createContactPreviewCard(data.contact1, 1);
        container.append(contact1Card);

        // Contact 2 Card
        const contact2Card = createContactPreviewCard(data.contact2, 2);
        container.append(contact2Card);
    }

    // Create contact preview card
    function createContactPreviewCard(contact, index) {
        const profileImage = contact.profile_image 
            ? `<img src="/storage/${contact.profile_image}" class="img-fluid rounded mb-3" style="max-width: 150px;" alt="${escapeHtml(contact.name)}">` 
            : '<i class="fas fa-user-circle fa-5x text-muted mb-3"></i>';

        let card = `
            <div class="col-md-6 mb-3">
                <div class="merge-preview-card" data-contact-id="${contact.id}">
                    <div class="text-center mb-3">
                        ${profileImage}
                    </div>
                    <div class="card-title">
                        <i class="fas fa-user"></i> Contact ${index}
                    </div>
                    
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Name:</td>
                            <td><strong>${escapeHtml(contact.name)}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>${escapeHtml(contact.email)}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td>${escapeHtml(contact.phone)}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Gender1:</td>
                            <td>${contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1)}</td>
                        </tr>
                    </table>
        `;

        // Additional emails
        if (contact.emails && contact.emails.length > 1) {
            card += `
                <div class="mb-2">
                    <small class="text-muted">All Emails (${contact.emails.length}):</small><br>
            `;
            contact.emails.forEach(email => {
                card += `<span class="badge bg-info me-1">${escapeHtml(email)}</span>`;
            });
            card += `</div>`;
        }

        // Additional phones
        if (contact.phones && contact.phones.length > 1) {
            card += `
                <div class="mb-2">
                    <small class="text-muted">All Phones (${contact.phones.length}):</small><br>
            `;
            contact.phones.forEach(phone => {
                card += `<span class="badge bg-success me-1">${escapeHtml(phone)}</span>`;
            });
            card += `</div>`;
        }

        // Custom fields
        if (contact.custom_fields && contact.custom_fields.length > 0) {
            card += `
                <div class="mt-3">
                    <small class="text-muted"><strong>Custom Fields:</strong></small>
                    <ul class="list-unstyled ms-3 mt-2">
            `;
            contact.custom_fields.forEach(cf => {
                card += `<li><small><strong>${escapeHtml(cf.field_label)}:</strong> ${escapeHtml(cf.value || 'N/A')}</small></li>`;
            });
            card += `</ul></div>`;
        }

        card += `
                    <div class="text-center mt-3">
                        <button class="btn btn-success select-master-btn" data-contact-id="${contact.id}">
                            <i class="fas fa-check"></i> Select as Master
                        </button>
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    // Select master contact
    $(document).on('click', '.select-master-btn', function() {
        selectedMasterContactId = $(this).data('contact-id');
        
        // Update UI
        $('.merge-preview-card').removeClass('selected');
        $(this).closest('.merge-preview-card').addClass('selected');
        
        // Update all buttons
        $('.select-master-btn').removeClass('btn-success').addClass('btn-outline-success');
        $('.select-master-btn').html('<i class="fas fa-check"></i> Select as Master');
        
        // Update selected button
        $(this).removeClass('btn-outline-success').addClass('btn-success');
        $(this).html('<i class="fas fa-check-circle"></i> Master Contact');
        
        // Enable confirm button
        $('#confirmMergeBtn').prop('disabled', false);
    });

    // Confirm merge
    $('#confirmMergeBtn').on('click', function() {
        if (!selectedMasterContactId) {
            showWarningAlert('Please select a master contact');
            return;
        }

        // Get the other contact ID
        const allContactIds = [mergeData.contact1.id, mergeData.contact2.id];
        const mergeContactId = allContactIds.find(id => id !== selectedMasterContactId);

        // Show final confirmation
        Swal.fire({
            title: 'Confirm Merge',
            html: `
                <div class="text-start">
                    <p><strong>Master Contact:</strong> ${getMasterContactName()}</p>
                    <p><strong>Contact to Merge:</strong> ${getMergeContactName()}</p>
                    <hr>
                    <p class="text-muted">This action will:</p>
                    <ul class="text-start">
                        <li>Keep all data from the master contact</li>
                        <li>Add additional emails and phones from the other contact</li>
                        <li>Merge custom field values</li>
                        <li>Mark the merged contact as inactive</li>
                        <li><strong>No data will be lost!</strong></li>
                    </ul>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-check"></i> Yes, Merge Now!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                executeMerge(selectedMasterContactId, mergeContactId);
            }
        });
    });

    // Get master contact name
    function getMasterContactName() {
        if (!mergeData) return 'Unknown';
        return mergeData.contact1.id === selectedMasterContactId 
            ? mergeData.contact1.name 
            : mergeData.contact2.name;
    }

    // Get merge contact name
    function getMergeContactName() {
        if (!mergeData) return 'Unknown';
        return mergeData.contact1.id !== selectedMasterContactId 
            ? mergeData.contact1.name 
            : mergeData.contact2.name;
    }

    // Execute merge
    function executeMerge(masterContactId, mergeContactId) {
        showLoading();

        $.ajax({
            url: '/admin/contacts/merge/confirm',
            type: 'POST',
            data: {
                master_contact_id: masterContactId,
                merge_contact_id: mergeContactId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#mergePreviewModal').modal('hide');
                    
                    // Show detailed success message
                    showMergeSuccessMessage(response.data);
                    
                    // Reset selection
                    $('.contact-checkbox').prop('checked', false);
                    $('#selectAll').prop('checked', false);
                    $('#mergeButtonContainer').hide();
                    
                    // Reload contacts
                    if (typeof loadContacts === 'function') {
                        loadContacts(1);
                    } else {
                        location.reload();
                    }
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, 'merge contacts');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    // Show merge success message with details
    function showMergeSuccessMessage(data) {
        const mergeInfo = data.merge_data;
        
        let detailsHtml = '<div class="text-start"><p><strong>Merge completed successfully!</strong></p>';
        
        // Emails merged
        if (mergeInfo.emails_merged && mergeInfo.emails_merged.length > 0) {
            detailsHtml += `<p class="mb-2"><i class="fas fa-envelope text-info"></i> <strong>Emails added:</strong> ${mergeInfo.emails_merged.length}</p>`;
        }
        
        // Phones merged
        if (mergeInfo.phones_merged && mergeInfo.phones_merged.length > 0) {
            detailsHtml += `<p class="mb-2"><i class="fas fa-phone text-success"></i> <strong>Phones added:</strong> ${mergeInfo.phones_merged.length}</p>`;
        }
        
        // Custom fields merged
        if (mergeInfo.custom_fields_merged && mergeInfo.custom_fields_merged.length > 0) {
            const addedFields = mergeInfo.custom_fields_merged.filter(cf => cf.action === 'added').length;
            if (addedFields > 0) {
                detailsHtml += `<p class="mb-2"><i class="fas fa-cog text-warning"></i> <strong>Custom fields added:</strong> ${addedFields}</p>`;
            }
        }
        
        detailsHtml += '<p class="text-muted mt-3"><small>All data has been preserved. The merged contact has been marked as inactive.</small></p></div>';
        
        Swal.fire({
            icon: 'success',
            title: 'Contacts Merged!',
            html: detailsHtml,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Great!',
            timer: 5000,
            timerProgressBar: true
        });
    }

    // Reset merge modal when closed
    $('#mergePreviewModal').on('hidden.bs.modal', function() {
        selectedMasterContactId = null;
        mergeData = null;
        $('#confirmMergeBtn').prop('disabled', true);
        $('#mergePreviewContainer').empty();
    });

    // Show merge info tooltip
    $(document).on('mouseenter', '.merge-preview-card', function() {
        $(this).css('cursor', 'pointer');
    });

    // Click anywhere on card to select
    $(document).on('click', '.merge-preview-card', function(e) {
        if (!$(e.target).hasClass('select-master-btn')) {
            $(this).find('.select-master-btn').click();
        }
    });

    // Keyboard navigation
    $(document).on('keydown', function(e) {
        if ($('#mergePreviewModal').hasClass('show')) {
            if (e.key === '1') {
                $('.merge-preview-card').first().find('.select-master-btn').click();
            } else if (e.key === '2') {
                $('.merge-preview-card').last().find('.select-master-btn').click();
            } else if (e.key === 'Enter' && !$('#confirmMergeBtn').prop('disabled')) {
                $('#confirmMergeBtn').click();
            }
        }
    });

    

    console.log('Merge.js loaded successfully');
});