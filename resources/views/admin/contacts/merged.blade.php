@extends('layouts.admin')

@section('title', 'Merged Contacts')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-compress-arrows-alt"></i> Merged Contacts
        </h1>
        <a href="{{ route('admin.contacts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Contacts
        </a>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        These contacts have been merged into other contacts. No data was lost during the merge.
    </div>

    <!-- Merged Contacts Table -->
    <div class="card content-card">
        <div class="card-header">
            <i class="fas fa-list"></i> Merged Contacts ({{ $mergedContacts->count() }})
        </div>
        <div class="card-body">
            @if($mergedContacts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Merged Into</th>
                            <th>Merged Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mergedContacts as $contact)
                        <tr>
                            <td>{{ $contact->id }}</td>
                            <td>{{ $contact->name }}</td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ $contact->phone }}</td>
                            <td>
                                @if($contact->mergedIntoContact)
                                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-user"></i> {{ $contact->mergedIntoContact->name }}
                                    </a>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $contact->updated_at->format('M d, Y H:i') }}</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-merge-details" 
                                        data-id="{{ $contact->id }}">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h4>No Merged Contacts</h4>
                <p>No contacts have been merged yet</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Merge Details Modal -->
<div class="modal fade" id="mergeDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Merge Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="mergeDetailsBody">
                <!-- Details loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // View merge details
    $('.view-merge-details').on('click', function() {
        const contactId = $(this).data('id');
        
        $.ajax({
            url: `/admin/contacts/${contactId}`,
            type: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    displayMergeDetails(response.data);
                    $('#mergeDetailsModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Details',
                        text: 'No merge details found'
                    });
                }
            }
        });
    });
    
    function displayMergeDetails(contact) {
        const history = contact.merged_history;
        let html = `
            <h6>Contact Information</h6>
            <table class="table table-sm">
                <tr><td><strong>Name:</strong></td><td>${contact.name}</td></tr>
                <tr><td><strong>Email:</strong></td><td>${contact.email}</td></tr>
                <tr><td><strong>Phone:</strong></td><td>${contact.phone}</td></tr>
            </table>
            <hr>
            <h6>Merge Information</h6>
        `;
        
        if (history && history.merge_data) {
            const data = history.merge_data;
            
            if (data.emails_merged && data.emails_merged.length > 0) {
                html += `<p><strong>Emails Merged:</strong> ${data.emails_merged.join(', ')}</p>`;
            }
            
            if (data.phones_merged && data.phones_merged.length > 0) {
                html += `<p><strong>Phones Merged:</strong> ${data.phones_merged.join(', ')}</p>`;
            }
            
            if (data.custom_fields_merged && data.custom_fields_merged.length > 0) {
                html += `<p><strong>Custom Fields:</strong> ${data.custom_fields_merged.length} fields merged</p>`;
            }
        }
        
        $('#mergeDetailsBody').html(html);
    }
});
</script>
@endpush