@extends('layouts.admin')

@section('title', 'Manage Custom Fields')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cog"></i> Manage Custom Fields
        </h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomFieldModal">
            <i class="fas fa-plus"></i> Add Custom Field
        </button>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i> 
        <strong>Custom Fields</strong> allow you to add additional information to your contacts dynamically. 
        Create fields like "Birthday", "Company Name", "Address", etc.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Custom Fields List -->
    <div class="card content-card">
        <div class="card-header">
            <i class="fas fa-list"></i> Custom Fields List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="customFieldsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Field Label</th>
                            <th>Field Name</th>
                            <th>Field Type</th>
                            <th>Options</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customFieldsTableBody">
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Custom Field Modal -->
<div class="modal fade" id="addCustomFieldModal" tabindex="-1" aria-labelledby="addCustomFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomFieldModalLabel">
                    <i class="fas fa-plus-circle"></i> Add Custom Field
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCustomFieldForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addFieldLabel" class="form-label">Field Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addFieldLabel" name="field_label" required placeholder="e.g., Birthday, Company Name">
                        <small class="text-muted">This is what users will see</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="addFieldType" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="addFieldType" name="field_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="text">Text (Single Line)</option>
                            <option value="textarea">Textarea (Multiple Lines)</option>
                            <option value="date">Date</option>
                            <option value="number">Number</option>
                            <option value="select">Dropdown (Select)</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3" id="addOptionsContainer" style="display: none;">
                        <label for="addFieldOptions" class="form-label">Field Options <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="addFieldOptions" name="field_options" rows="3" placeholder="Enter options separated by comma (e.g., Option 1, Option 2, Option 3)"></textarea>
                        <small class="text-muted">Separate options with commas</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="addIsActive" name="is_active" checked>
                            <label class="form-check-label" for="addIsActive">
                                Active (Show in forms)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Custom Field
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Custom Field Modal -->
<div class="modal fade" id="editCustomFieldModal" tabindex="-1" aria-labelledby="editCustomFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomFieldModalLabel">
                    <i class="fas fa-edit"></i> Edit Custom Field
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCustomFieldForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editCustomFieldId" name="custom_field_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editFieldLabel" class="form-label">Field Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editFieldLabel" name="field_label" required>
                        <small class="text-muted">This is what users will see</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editFieldType" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="editFieldType" name="field_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="text">Text (Single Line)</option>
                            <option value="textarea">Textarea (Multiple Lines)</option>
                            <option value="date">Date</option>
                            <option value="number">Number</option>
                            <option value="select">Dropdown (Select)</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3" id="editOptionsContainer" style="display: none;">
                        <label for="editFieldOptions" class="form-label">Field Options <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editFieldOptions" name="field_options" rows="3" placeholder="Enter options separated by comma"></textarea>
                        <small class="text-muted">Separate options with commas</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active">
                            <label class="form-check-label" for="editIsActive">
                                Active (Show in forms)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Custom Field
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/custom-fields.js') }}"></script>
@endpush