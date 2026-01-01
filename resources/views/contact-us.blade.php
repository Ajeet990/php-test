@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="contact-us-page">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-envelope"></i> Contact Us</h3>
                    </div>
                    <div class="card-body p-4">
                        <form id="contactForm" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Gender -->
                            <div class="mb-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="male" value="male" required>
                                        <label class="form-check-label" for="male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                                        <label class="form-check-label" for="female">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="other" value="other">
                                        <label class="form-check-label" for="other">Other</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Profile Image -->
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Profile Image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <small class="text-muted">Accepted formats: JPG, PNG, GIF (Max: 2MB)</small>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Additional File -->
                            <div class="mb-3">
                                <label for="additional_file" class="form-label">Additional Document</label>
                                <input type="file" class="form-control" id="additional_file" name="additional_file" accept=".pdf,.doc,.docx,.txt">
                                <small class="text-muted">Accepted formats: PDF, DOC, DOCX, TXT (Max: 5MB)</small>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Custom Fields -->
                            @if($customFields->count() > 0)
                            <hr>
                            <h5 class="mb-3">Additional Information</h5>
                            @foreach($customFields as $field)
                            <div class="mb-3">
                                <label for="custom_field_{{ $field->id }}" class="form-label">{{ $field->field_label }}</label>
                                
                                @if($field->field_type == 'text')
                                    <input type="text" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}">
                                
                                @elseif($field->field_type == 'textarea')
                                    <textarea class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" rows="3"></textarea>
                                
                                @elseif($field->field_type == 'date')
                                    <input type="date" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}">
                                
                                @elseif($field->field_type == 'number')
                                    <input type="number" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}">
                                
                                @elseif($field->field_type == 'select')
                                    <select class="form-select" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}">
                                        <option value="">-- Select --</option>
                                        @if(is_array($field->field_options))
                                            @foreach($field->field_options as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @endif
                            </div>
                            @endforeach
                            @endif

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/contact-form.js') }}"></script>
@endpush