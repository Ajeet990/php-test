@extends('layouts.admin')

@section('title', 'Manage Contacts')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-address-book"></i> Manage Contacts
        </h1>
        
    </div>

    <!-- Filters Section -->
    <div class="card content-card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filters
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="filterName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="filterName" name="name" placeholder="Search by name">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filterEmail" class="form-label">Email</label>
                        <input type="text" class="form-control" id="filterEmail" name="email" placeholder="Search by email">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="filterGender" class="form-label">Gender</label>
                        <select class="form-select" id="filterGender" name="gender">
                            <option value="">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button type="button" class="btn btn-secondary" id="resetFilters">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card content-card">
        <div class="card-header">
            <i class="fas fa-list"></i> Contacts List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="contactsTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Profile</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contactsTableBody">
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="mt-3"></div>
        </div>
    </div>

    <!-- Merge Button (Appears when 2 contacts selected) -->
    <div id="mergeButtonContainer" class="position-fixed bottom-0 end-0 p-4" style="display: none;">
        <button class="btn btn-warning btn-lg shadow" id="mergeContactsBtn">
            <i class="fas fa-compress-arrows-alt"></i> Merge Selected Contacts (<span id="selectedCount">0</span>)
        </button>
    </div>
</div>



<!-- Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContactModalLabel">
                    <i class="fas fa-user-edit"></i> Edit Contact
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editContactForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editContactId" name="contact_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editPhone" name="phone" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="editMale" value="male" required>
                                    <label class="form-check-label" for="editMale">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="editFemale" value="female">
                                    <label class="form-check-label" for="editFemale">Female</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="editOther" value="other">
                                    <label class="form-check-label" for="editOther">Other</label>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editProfileImage" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="editProfileImage" name="profile_image" accept="image/*">
                            <small class="text-muted">Max: 2MB (Leave empty to keep current)</small>
                            <div id="currentProfileImage" class="mt-2"></div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAdditionalFile" class="form-label">Additional Document</label>
                            <input type="file" class="form-control" id="editAdditionalFile" name="additional_file" accept=".pdf,.doc,.docx,.txt">
                            <small class="text-muted">Max: 5MB (Leave empty to keep current)</small>
                            <div id="currentAdditionalFile" class="mt-2"></div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Edit Custom Fields Container -->
                    <div id="editCustomFieldsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Contact Modal -->
<div class="modal fade" id="viewContactModal" tabindex="-1" aria-labelledby="viewContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContactModalLabel">
                    <i class="fas fa-user"></i> Contact Details1
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewContactBody">
                <!-- Contact details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Merge Preview Modal -->
<div class="modal fade" id="mergePreviewModal" tabindex="-1" aria-labelledby="mergePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="mergePreviewModalLabel">
                    <i class="fas fa-compress-arrows-alt"></i> Merge Contacts - Select Master Contact
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Select which contact should be the master.</strong> 
                    The master contact will keep all its data, and data from the other contact will be merged into it.
                </div>
                
                <div class="row" id="mergePreviewContainer">
                    <!-- Merge preview cards will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmMergeBtn" disabled>
                    <i class="fas fa-check"></i> Confirm Merge
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/contacts.js') }}"></script>
<script src="{{ asset('js/merge.js') }}"></script>
@endpush