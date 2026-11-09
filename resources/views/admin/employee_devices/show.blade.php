@extends('layouts.app')

@section('title', 'Employee Device Mapping')

@section('content')
<div class="content-wrapper employee-device-show-page">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-id-card"></i> Device mapping</h1>
            <p class="page-subtitle">View the employee and biometric ID linked by this assignment.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.employee-devices.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <a href="{{ route('admin.employee-devices.edit', $device) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Edit mapping</a>
        </div>
    </div>

    <div class="card employee-device-details-card">
        <div class="employee-device-details-heading">
            <span class="employee-device-details-icon"><i class="fas fa-fingerprint"></i></span>
            <div>
                <h2 class="h6 mb-1">Assignment details</h2>
                <p class="text-muted small mb-0">Mapping #{{ $device->id }}</p>
            </div>
            @if($device->is_primary)
            <span class="employee-device-badge is-primary ms-auto"><i class="fas fa-star me-1"></i>Primary device</span>
            @else
            <span class="employee-device-badge is-secondary ms-auto">Additional device</span>
            @endif
        </div>
        <div class="employee-device-details-grid">
            <div class="employee-device-detail">
                <span>Employee</span>
                <strong>{{ $device->employee?->full_name ?? 'Unknown employee' }}</strong>
                @if($device->employee?->employee_id)
                <small>Employee ID: {{ $device->employee->employee_id }}</small>
                @endif
            </div>
            <div class="employee-device-detail">
                <span>Device identifier</span>
                <strong><code class="employee-device-id">{{ $device->device_identifier }}</code></strong>
            </div>
            <div class="employee-device-detail">
                <span>Device name</span>
                <strong>{{ $device->device_name ?: 'Not specified' }}</strong>
            </div>
            <div class="employee-device-detail">
                <span>Created</span>
                <strong>{{ $device->created_at?->format('M j, Y') ?? '—' }}</strong>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .employee-device-show-page .page-header { margin-bottom: 22px; }
    .employee-device-details-card { overflow: hidden; }
    .employee-device-details-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border-color);
    }
    .employee-device-details-heading h2 { font-weight: 700; }
    .employee-device-details-icon {
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
    body.dark-mode .employee-device-details-icon { color: #bfdbfe; background: #1e3a8a; }
    .employee-device-details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .employee-device-detail { display: flex; flex-direction: column; gap: 7px; padding: 20px; border-bottom: 1px solid var(--border-color); }
    .employee-device-detail:nth-child(odd) { border-right: 1px solid var(--border-color); }
    .employee-device-detail > span { color: var(--text-tertiary); font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .employee-device-detail > strong { font-weight: 600; }
    .employee-device-detail > small { color: var(--text-secondary); }
    .employee-device-id {
        display: inline-block;
        padding: 4px 7px;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border-radius: 5px;
        font-size: .85rem;
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
    @media (max-width: 575.98px) {
        .employee-device-details-heading { flex-wrap: wrap; }
        .employee-device-details-grid { grid-template-columns: 1fr; }
        .employee-device-detail:nth-child(odd) { border-right: 0; }
        .employee-device-show-page .page-header { align-items: flex-start; }
    }
</style>
@endsection
