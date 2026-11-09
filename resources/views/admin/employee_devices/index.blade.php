@extends('layouts.app')

@section('title', 'Employee Devices')

@section('content')
<div class="content-wrapper employee-devices-page">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-id-card"></i> Employee devices</h1>
            <p class="page-subtitle">Manage the biometric device IDs assigned to your employees.</p>
        </div>
        <a href="{{ route('admin.employee-devices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Map a device
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card employee-device-stat">
                <div>
                    <div class="stat-label">Total mappings</div>
                    <div class="stat-value">{{ number_format($mappingCount) }}</div>
                    <div class="employee-device-stat-note">Registered device IDs</div>
                </div>
                <div class="stat-icon employee-device-stat-icon devices"><i class="fas fa-fingerprint"></i></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card employee-device-stat">
                <div>
                    <div class="stat-label">Primary devices</div>
                    <div class="stat-value">{{ number_format($primaryCount) }}</div>
                    <div class="employee-device-stat-note">Preferred IDs for employees</div>
                </div>
                <div class="stat-icon employee-device-stat-icon primary"><i class="fas fa-star"></i></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card employee-device-stat">
                <div>
                    <div class="stat-label">Employees assigned</div>
                    <div class="stat-value">{{ number_format($assignedEmployeeCount) }}</div>
                    <div class="employee-device-stat-note">Employees with a device ID</div>
                </div>
                <div class="stat-icon employee-device-stat-icon employees"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    <div class="card employee-device-list-card">
        <div class="employee-device-list-heading">
            <div>
                <h2 class="h6 mb-1">Device assignments</h2>
                <p class="text-muted small mb-0">Search by employee or device identifier.</p>
            </div>
            <span class="employee-device-result-count">{{ number_format($devices->total()) }} results</span>
        </div>

        <form method="GET" action="{{ route('admin.employee-devices.index') }}" class="employee-device-filters">
            <div class="employee-device-search">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Search name, employee ID, or device ID"
                    aria-label="Search employee device mappings"
                >
            </div>
            <select name="primary" class="form-select employee-device-primary-filter" aria-label="Filter by primary status">
                <option value="">All assignments</option>
                <option value="1" @selected($primary === '1')>Primary only</option>
                <option value="0" @selected($primary === '0')>Non-primary only</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if($search !== '' || $primary !== '')
            <a href="{{ route('admin.employee-devices.index') }}" class="btn btn-light">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover employee-device-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Device identifier</th>
                        <th>Device name</th>
                        <th>Type</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr>
                        <td>
                            <div class="employee-device-person">
                                <span class="employee-device-person-icon"><i class="fas fa-user"></i></span>
                                <span>
                                    <strong>{{ $device->employee?->full_name ?? 'Unknown employee' }}</strong>
                                    @if($device->employee?->employee_id)
                                    <small>{{ $device->employee->employee_id }}</small>
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td><code class="employee-device-id">{{ $device->device_identifier }}</code></td>
                        <td>{{ $device->device_name ?: '—' }}</td>
                        <td>
                            @if($device->is_primary)
                            <span class="employee-device-badge is-primary"><i class="fas fa-star me-1"></i>Primary</span>
                            @else
                            <span class="employee-device-badge is-secondary">Additional</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.employee-devices.show', $device) }}" class="btn btn-sm btn-light" title="View mapping" aria-label="View mapping">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.employee-devices.edit', $device) }}" class="btn btn-sm btn-light" title="Edit mapping" aria-label="Edit mapping">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.employee-devices.destroy', $device) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this employee-device mapping?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light employee-device-delete" title="Remove mapping" aria-label="Remove mapping">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="employee-device-empty">
                                <span class="employee-device-empty-icon"><i class="fas fa-fingerprint"></i></span>
                                <h3>{{ $search !== '' || $primary !== '' ? 'No matching assignments' : 'No device assignments yet' }}</h3>
                                <p>{{ $search !== '' || $primary !== '' ? 'Try another search or clear the filters.' : 'Map a device identifier to an employee to get started.' }}</p>
                                @if($search !== '' || $primary !== '')
                                <a href="{{ route('admin.employee-devices.index') }}" class="btn btn-light btn-sm">Clear filters</a>
                                @else
                                <a href="{{ route('admin.employee-devices.create') }}" class="btn btn-primary btn-sm">Map a device</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
        <div class="employee-device-pagination">
            <span class="text-muted small">
                Showing {{ $devices->firstItem() }}–{{ $devices->lastItem() }} of {{ number_format($devices->total()) }}
            </span>
            {{ $devices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .employee-devices-page .page-header { margin-bottom: 22px; }
    .employee-devices-page .page-header > .btn { padding: 9px 14px; }
    .employee-device-stat { min-height: 126px; }
    .employee-device-stat-note { color: var(--text-tertiary); font-size: .78rem; margin-top: 5px; }
    .employee-device-stat-icon.devices { color: #2563eb; background: #dbeafe; }
    .employee-device-stat-icon.primary { color: #b45309; background: #fef3c7; }
    .employee-device-stat-icon.employees { color: #059669; background: #d1fae5; }
    body.dark-mode .employee-device-stat-icon.devices { color: #bfdbfe; background: #1e3a8a; }
    body.dark-mode .employee-device-stat-icon.primary { color: #fde68a; background: #78350f; }
    body.dark-mode .employee-device-stat-icon.employees { color: #a7f3d0; background: #064e3b; }
    .employee-device-list-card { overflow: hidden; }
    .employee-device-list-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 14px;
    }
    .employee-device-list-heading h2 { font-weight: 700; }
    .employee-device-result-count {
        color: var(--text-secondary);
        background: var(--bg-tertiary);
        border-radius: 20px;
        padding: 5px 10px;
        font-size: .75rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .employee-device-filters {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 20px 16px;
    }
    .employee-device-search { position: relative; flex: 1; min-width: 220px; }
    .employee-device-search > i {
        position: absolute;
        z-index: 1;
        top: 50%;
        left: 13px;
        color: var(--text-tertiary);
        transform: translateY(-50%);
    }
    .employee-device-search .form-control { padding-left: 37px; }
    .employee-device-primary-filter { width: auto; min-width: 160px; }
    .employee-device-table { min-width: 720px; }
    .employee-device-table thead th { white-space: nowrap; }
    .employee-device-person { display: flex; align-items: center; gap: 11px; }
    .employee-device-person > span:last-child { display: flex; flex-direction: column; gap: 2px; }
    .employee-device-person strong { font-weight: 600; }
    .employee-device-person small { color: var(--text-tertiary); font-size: .76rem; }
    .employee-device-person-icon, .employee-device-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        color: #2563eb;
        background: #eff6ff;
    }
    body.dark-mode .employee-device-person-icon,
    body.dark-mode .employee-device-empty-icon { color: #bfdbfe; background: #1e3a8a; }
    .employee-device-id {
        display: inline-block;
        padding: 4px 7px;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border-radius: 5px;
        font-size: .82rem;
    }
    .employee-device-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .employee-device-badge.is-primary { color: #92400e; background: #fef3c7; }
    .employee-device-badge.is-secondary { color: var(--text-secondary); background: var(--bg-tertiary); }
    body.dark-mode .employee-device-badge.is-primary { color: #fde68a; background: #78350f; }
    .employee-device-table .btn-sm { margin-left: 4px; }
    .employee-device-delete { color: #dc2626; }
    .employee-device-delete:hover { color: #fff; background: #dc2626; border-color: #dc2626; }
    .employee-device-empty { padding: 42px 20px; text-align: center; }
    .employee-device-empty-icon { width: 52px; height: 52px; margin-bottom: 14px; font-size: 1.2rem; }
    .employee-device-empty h3 { font-size: 1rem; font-weight: 700; }
    .employee-device-empty p { color: var(--text-secondary); margin-bottom: 16px; }
    .employee-device-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 20px;
        border-top: 1px solid var(--border-color);
    }
    @media (max-width: 767.98px) {
        .employee-device-filters { align-items: stretch; flex-wrap: wrap; }
        .employee-device-search { flex-basis: 100%; }
        .employee-device-primary-filter { flex: 1; }
        .employee-device-pagination { align-items: flex-start; flex-direction: column; }
    }
</style>
@endsection
