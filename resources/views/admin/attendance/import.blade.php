@extends('layouts.app')

@section('title', 'Import Attendance')

@section('content')
<div class="content-wrapper attendance-import-page">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-file-import"></i> Import attendance</h1>
            <p class="page-subtitle">Upload a ZKTeco attendance .dat file to import historical punches.</p>
        </div>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-1"></i> Back to attendance
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" role="alert">
        <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('import_result'))
    <div class="card attendance-import-result mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="h6 mb-1">Import complete</h2>
                <p class="text-muted small mb-0">
                    {{ number_format(session('import_result.processed')) }} punches imported
                    @if(session('import_result.skipped'))
                        · {{ number_format(session('import_result.skipped')) }} rows skipped (unmatched or invalid)
                    @endif
                    <br>
                    Attendance dates: {{ \Illuminate\Support\Carbon::parse(session('import_result.date_from'))->format('M d, Y') }}
                    @if(session('import_result.date_from') !== session('import_result.date_to'))
                        – {{ \Illuminate\Support\Carbon::parse(session('import_result.date_to'))->format('M d, Y') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.attendance.hr-admin', ['date_from' => session('import_result.date_from'), 'date_to' => session('import_result.date_to')]) }}" class="btn btn-primary">
                <i class="fas fa-calendar-check me-1"></i> View imported attendance
            </a>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>The file could not be imported.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card attendance-import-card">
                <div class="attendance-import-card-heading">
                    <span class="attendance-import-icon"><i class="fas fa-cloud-arrow-up"></i></span>
                    <div>
                        <h2 class="h6 mb-1">Upload attendance file</h2>
                        <p class="text-muted small mb-0">Accepted format: tab-separated ZKTeco ATTLOG .dat, up to 10 MB.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.attendance.import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="attendance_file" class="form-label">ATTLOG .dat file <span class="text-danger">*</span></label>
                            <input
                                type="file"
                                id="attendance_file"
                                name="file"
                                class="form-control @error('file') is-invalid @enderror"
                                accept=".dat"
                                required
                            >
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="device_id" class="form-label">Attendance source</label>
                            <select id="device_id" name="device_id" class="form-select @error('device_id') is-invalid @enderror">
                                <option value="">DAT Import</option>
                                @foreach($devices as $device)
                                <option value="{{ $device->id }}" @selected(old('device_id') == $device->id)>{{ $device->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose the registered device to merge with its attendance history, or leave as DAT Import.</div>
                            @error('device_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="attendance-import-format">
                            <div class="small fw-semibold mb-2">Expected row format</div>
                            <code>PIN&nbsp;&nbsp;&nbsp;&nbsp;DateTime&nbsp;&nbsp;&nbsp;&nbsp;Verify&nbsp;&nbsp;&nbsp;&nbsp;Status&nbsp;&nbsp;&nbsp;&nbsp;WorkCode</code>
                            <div class="text-muted small mt-2">For full ZKTeco rows, status is the fourth column: 0 = check-in and 1 = check-out. Short rows may omit the verify column.</div>
                        </div>
                    </div>
                    <div class="attendance-import-footer">
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-file-import me-1"></i> Import attendance</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card attendance-import-help">
                <span class="attendance-import-icon help"><i class="fas fa-circle-info"></i></span>
                <h2 class="h6">Before you import</h2>
                <ul class="text-muted small mb-0">
                    <li>Employee PINs must match an employee ID, biometric ID, or employee-device mapping already in the system.</li>
                    <li>Check-in and check-out punches are combined into daily attendance records.</li>
                    <li>Re-importing the same file and source updates the existing records instead of creating duplicates.</li>
                    <li>Unknown employee PINs and malformed rows are skipped and included in the import summary.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .attendance-import-page .page-header { margin-bottom: 22px; }
    .attendance-import-page .page-header > .btn { white-space: nowrap; }
    .attendance-import-card { overflow: hidden; }
    .attendance-import-card-heading { display: flex; align-items: center; gap: 12px; padding: 18px 20px; border-bottom: 1px solid var(--border-color); }
    .attendance-import-card-heading h2 { font-weight: 700; }
    .attendance-import-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; width: 40px; height: 40px; border-radius: 11px; color: #2563eb; background: #eff6ff; }
    .attendance-import-icon.help { margin-bottom: 14px; color: #7c3aed; background: #f3e8ff; }
    body.dark-mode .attendance-import-icon { color: #bfdbfe; background: #1e3a8a; }
    body.dark-mode .attendance-import-icon.help { color: #ddd6fe; background: #4c1d95; }
    .attendance-import-format { padding: 14px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-tertiary); }
    .attendance-import-format code { color: var(--text-primary); font-size: .78rem; white-space: normal; }
    .attendance-import-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 1px solid var(--border-color); background: var(--bg-tertiary); }
    .attendance-import-help { padding: 20px; }
    .attendance-import-help h2 { font-weight: 700; }
    .attendance-import-help ul { padding-left: 18px; }
    .attendance-import-help li + li { margin-top: 10px; }
    @media (max-width: 575.98px) { .attendance-import-page .page-header { align-items: flex-start; flex-direction: column; } }
</style>
@endsection
