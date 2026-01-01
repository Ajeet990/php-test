@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Dashboard</h1>

    <div class="row">

        {{-- Total Contacts --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Contacts</h5>
                    <h2 class="fw-bold">{{ $totalContacts }}</h2>
                    <p class="text-muted mb-0">Active (Not merged)</p>
                </div>
            </div>
        </div>

        {{-- Merged Contacts --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title text-muted">Merged Contacts</h5>
                    <h2 class="fw-bold">{{ $mergedContacts }}</h2>
                    <p class="text-muted mb-0">Merged records</p>
                </div>
            </div>
        </div>

        {{-- Custom Fields --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title text-muted">Custom Fields</h5>
                    <h2 class="fw-bold">{{ $totalCustomFields }}</h2>
                    <p class="text-muted mb-0">Defined fields</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Quick Links --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>

                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-primary me-2">
                        View Contacts
                    </a>

                    <a href="{{ route('admin.custom-fields.index') }}" class="btn btn-secondary">
                        Manage Custom Fields
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




