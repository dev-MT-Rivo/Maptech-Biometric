@extends('layouts.app')

@section('title', "Create Employee - Maptech's Employee System")

@section('content')
<div class="content-wrapper employee-create-page">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-user-plus"></i> Add employee</h1>
            <p class="page-subtitle">Create the employee profile, login account, and biometric mapping in one step.</p>
        </div>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back to employees</a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Please review the following fields.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.employees.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card employee-create-card mb-4">
                    <div class="employee-create-card-heading">
                        <span class="employee-create-icon"><i class="fas fa-address-card"></i></span>
                        <div>
                            <h2 class="h6 mb-1">Employee information</h2>
                            <p class="text-muted small mb-0">Basic details and employment information.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                                <input id="first_name" type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                                <input id="last_name" type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="employee_id" class="form-label">Employee ID <span class="text-danger">*</span></label>
                                <input id="employee_id" type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id') }}" required>
                                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email / login username <span class="text-danger">*</span></label>
                                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" required>
                                <div class="form-text">This email is used for the employee profile and their login account.</div>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input id="phone" type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of birth</label>
                                <input id="date_of_birth" type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}">
                                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="department_id" class="form-label">Department</label>
                                <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                    <option value="">Select department</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="position_id" class="form-label">Position</label>
                                <select id="position_id" name="position_id" class="form-select @error('position_id') is-invalid @enderror">
                                    <option value="">Select position</option>
                                    @foreach($positions as $position)
                                    <option value="{{ $position->id }}" data-department-id="{{ $position->department_id }}" @selected(old('position_id') == $position->id)>{{ $position->name }}</option>
                                    @endforeach
                                </select>
                                @error('position_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="joining_date" class="form-label">Joining date <span class="text-danger">*</span></label>
                                <input id="joining_date" type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date') }}" required>
                                @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Select gender</option>
                                    <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                                    <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                                    <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="employment_type_id" class="form-label">Employment type</label>
                                <select id="employment_type_id" name="employment_type_id" class="form-select @error('employment_type_id') is-invalid @enderror">
                                    <option value="">Select type</option>
                                    @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('employment_type_id') == $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('employment_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="employee_status_id" class="form-label">Employee status</label>
                                <select id="employee_status_id" name="employee_status_id" class="form-select @error('employee_status_id') is-invalid @enderror">
                                    <option value="">Select status</option>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" @selected(old('employee_status_id') == $status->id)>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="monthly_salary" class="form-label">Monthly salary</label>
                                <input id="monthly_salary" type="number" step="0.01" min="0" name="monthly_salary" class="form-control @error('monthly_salary') is-invalid @enderror" value="{{ old('monthly_salary') }}">
                                @error('monthly_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', '1'))>
                                    <label for="is_active" class="form-check-label">Employee is active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card employee-create-card mb-4">
                    <div class="employee-create-card-heading">
                        <span class="employee-create-icon account"><i class="fas fa-lock"></i></span>
                        <div>
                            <h2 class="h6 mb-1">Login account</h2>
                            <p class="text-muted small mb-0">An Employee-role account is created automatically with this profile.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Initial password <span class="text-danger">*</span></label>
                                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                                <div class="form-text">Use at least 8 characters.</div>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm password <span class="text-danger">*</span></label>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card employee-create-card">
                    <div class="employee-create-card-heading">
                        <span class="employee-create-icon device"><i class="fas fa-fingerprint"></i></span>
                        <div>
                            <h2 class="h6 mb-1">Biometric device</h2>
                            <p class="text-muted small mb-0">Optional. Add the user ID from the terminal to link attendance automatically.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="device_identifier" class="form-label">Biometric device user ID</label>
                                <input id="device_identifier" type="text" name="device_identifier" class="form-control @error('device_identifier') is-invalid @enderror" value="{{ old('device_identifier') }}" placeholder="e.g. 10025">
                                <div class="form-text">Saved as the employee's biometric ID and primary device mapping.</div>
                                @error('device_identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="device_name" class="form-label">Device name <span class="text-muted">(optional)</span></label>
                                <input id="device_name" type="text" name="device_name" class="form-control @error('device_name') is-invalid @enderror" value="{{ old('device_name') }}" placeholder="e.g. Main office terminal">
                                @error('device_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="employee-create-actions">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-light">Cancel</a>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus me-1"></i> Create employee</button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card employee-create-note">
                    <span class="employee-create-icon account"><i class="fas fa-circle-info"></i></span>
                    <h2 class="h6">One-step setup</h2>
                    <p class="text-muted small mb-0">Saving this form creates the employee and their login account together. If you enter a biometric device user ID, it is also linked as the employee's primary device mapping.</p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('styles')
<style>
    .employee-create-page .page-header { margin-bottom: 22px; }
    .employee-create-page .page-header > .btn { white-space: nowrap; }
    .employee-create-card { overflow: hidden; }
    .employee-create-card-heading { display: flex; align-items: center; gap: 12px; padding: 18px 20px; border-bottom: 1px solid var(--border-color); }
    .employee-create-card-heading h2 { font-weight: 700; }
    .employee-create-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; width: 40px; height: 40px; border-radius: 11px; color: #2563eb; background: #eff6ff; }
    .employee-create-icon.account { color: #7c3aed; background: #f3e8ff; }
    .employee-create-icon.device { color: #059669; background: #d1fae5; }
    body.dark-mode .employee-create-icon { color: #bfdbfe; background: #1e3a8a; }
    body.dark-mode .employee-create-icon.account { color: #ddd6fe; background: #4c1d95; }
    body.dark-mode .employee-create-icon.device { color: #a7f3d0; background: #064e3b; }
    .employee-create-note { padding: 20px; }
    .employee-create-note .employee-create-icon { margin-bottom: 14px; }
    .employee-create-note h2 { font-weight: 700; }
    .employee-create-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    @media (max-width: 575.98px) {
        .employee-create-page .page-header { align-items: flex-start; flex-direction: column; }
    }
</style>
@endsection

@section('scripts')
    @include('admin.employees.partials.department-position-filter')
@endsection
