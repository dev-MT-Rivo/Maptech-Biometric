@extends('layouts.app')

@section('title', 'Edit Employee Device')

@section('content')
<div class="content-wrapper employee-device-form-page">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-pen-to-square"></i> Edit device mapping</h1>
            <p class="page-subtitle">Update the employee or identifier associated with this device.</p>
        </div>
        <a href="{{ route('admin.employee-devices.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-1"></i> Back to employee devices
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Please review the highlighted fields.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card employee-device-form-card">
                <div class="employee-device-form-card-header">
                    <span class="employee-device-form-icon"><i class="fas fa-fingerprint"></i></span>
                    <div>
                        <h2 class="h6 mb-1">Device assignment details</h2>
                        <p class="text-muted small mb-0">Fields marked with <span class="text-danger">*</span> are required.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.employee-devices.update', $device) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">Choose an employee</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected((string) old('employee_id', $device->employee_id) === (string) $employee->id)>
                                    {{ $employee->full_name }}{{ $employee->employee_id ? ' · ' . $employee->employee_id : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="device_identifier" class="form-label">Device identifier <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="device_identifier"
                                    id="device_identifier"
                                    class="form-control @error('device_identifier') is-invalid @enderror"
                                    value="{{ old('device_identifier', $device->device_identifier) }}"
                                    placeholder="e.g. 10025"
                                    required
                                >
                                <div class="form-text">Enter the user ID registered on the biometric device.</div>
                                @error('device_identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="device_name" class="form-label">Device name <span class="text-muted">(optional)</span></label>
                                <input
                                    type="text"
                                    name="device_name"
                                    id="device_name"
                                    class="form-control @error('device_name') is-invalid @enderror"
                                    value="{{ old('device_name', $device->device_name) }}"
                                    placeholder="e.g. Main office fingerprint reader"
                                >
                                @error('device_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="employee-device-primary-option mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_primary" id="is_primary" value="1" @checked(old('is_primary', $device->is_primary))>
                                <label class="form-check-label" for="is_primary">
                                    <strong>Set as primary device</strong>
                                    <span>Use this as the employee’s preferred biometric ID.</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="employee-device-form-footer">
                        <a href="{{ route('admin.employee-devices.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Save changes</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card employee-device-help-card">
                <div class="employee-device-help-icon"><i class="fas fa-circle-info"></i></div>
                <h2 class="h6">About device mappings</h2>
                <p class="text-muted small mb-0">An employee can have multiple device IDs, but only one can be marked as primary. Setting this mapping as primary will replace the previous primary mapping for the selected employee.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .employee-device-form-page .page-header { margin-bottom: 22px; }
    .employee-device-form-page .page-header > .btn { white-space: nowrap; }
    .employee-device-form-card { overflow: hidden; }
    .employee-device-form-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border-color);
    }
    .employee-device-form-card-header h2 { font-weight: 700; }
    .employee-device-form-icon, .employee-device-help-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 11px;
        color: #2563eb;
        background: #eff6ff;
    }
    body.dark-mode .employee-device-form-icon,
    body.dark-mode .employee-device-help-icon { color: #bfdbfe; background: #1e3a8a; }
    .employee-device-primary-option {
        padding: 14px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        background: var(--bg-tertiary);
    }
    .employee-device-primary-option .form-check { display: flex; align-items: flex-start; gap: 9px; }
    .employee-device-primary-option .form-check-input { margin-top: 4px; }
    .employee-device-primary-option .form-check-label { display: flex; flex-direction: column; gap: 3px; }
    .employee-device-primary-option .form-check-label span { color: var(--text-secondary); font-size: .8rem; }
    .employee-device-form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 20px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-tertiary);
    }
    .employee-device-help-card { padding: 20px; }
    .employee-device-help-icon { margin-bottom: 14px; }
    .employee-device-help-card h2 { font-weight: 700; }
    @media (max-width: 575.98px) {
        .employee-device-form-page .page-header { align-items: flex-start; flex-direction: column; }
    }
</style>
@endsection
