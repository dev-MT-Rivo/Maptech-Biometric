<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\Employee;
use App\Models\EmployeeDevice;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ZktecoAttendanceService
{
    public function processLogs(BiometricDevice $device, array $logs): int
    {
        $processed = $this->processRecords($device->name, $logs);
        $device->forceFill(['last_synced_at' => now()])->save();

        return $processed;
    }

    public function processImportedLogs(string $source, array $logs): int
    {
        return $this->processRecords($source, $logs);
    }

    public function detachMisdatedCheckout(Employee $employee, string $source, Carbon $checkInTimestamp): void
    {
        $checkInDate = $checkInTimestamp->toDateString();
        $previousShift = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $checkInTimestamp->copy()->subDay()->toDateString())
            ->whereDate('check_out_date', $checkInDate)
            ->where('check_out_time', $checkInTimestamp->format('H:i:s'))
            ->where('source', $source)
            ->whereNotNull('check_in_time')
            ->first();

        if (! $previousShift) {
            return;
        }

        $previousShift->check_out_time = null;
        $previousShift->check_out_date = null;
        $previousShift->status = Attendance::statusForTimes(
            $previousShift->check_in_time,
            null,
            $previousShift->attendance_date->toDateString()
        );
        $previousShift->save();
    }

    private function processRecords(string $source, array $logs): int
    {
        $records = $this->normalizeLogs($logs);
        $processed = 0;

        foreach ($records as $log) {
            $employee = $this->resolveEmployee($log);
            if (! $employee) {
                continue;
            }

            $timestamp = $this->resolveTimestamp($log);
            if (! $timestamp) {
                continue;
            }

            $date = $timestamp->toDateString();
            $time = $timestamp->format('H:i:s');
            $status = $this->resolveStatus($log, $time);
            $isCheckout = $this->isCheckoutLog($log);

            if (! $isCheckout) {
                $this->detachMisdatedCheckout($employee, $source, $timestamp);
            }

            $attendance = Attendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $date)
                ->where('source', $source)
                ->first();

            if (! $attendance) {
                $attendance = new Attendance([
                    'employee_id' => $employee->id,
                    'attendance_date' => $date,
                ]);
            }

            if (! $attendance->exists) {
                $attendance->check_in_time = $isCheckout ? null : $time;
                $attendance->check_out_time = $isCheckout ? $time : null;
                $attendance->status = $isCheckout ? 'Present' : $status;
            } else {
                if (! $isCheckout && (empty($attendance->check_in_time) || $time < $attendance->check_in_time)) {
                    $attendance->check_in_time = $time;
                }

                if ($isCheckout && (empty($attendance->check_out_time) || $time > $attendance->check_out_time)) {
                    $attendance->check_out_time = $time;
                }

                if ($attendance->status === 'Absent' && $attendance->check_in_time) {
                    $attendance->status = $status;
                } elseif (! in_array($attendance->status, ['Leave', 'Overtime'], true) && $attendance->check_in_time) {
                    $attendance->status = Attendance::statusForTimes(
                        $attendance->check_in_time,
                        $attendance->check_out_time
                    );
                }
            }

            $attendance->source = $source;
            if ($isCheckout) {
                $attendance->check_out_date = $date;
            }
            if (
                ! in_array($attendance->status, ['Absent', 'Leave', 'Overtime'], true)
                && ($attendance->check_in_time || $attendance->check_out_time)
            ) {
                $attendance->status = Attendance::statusForTimes(
                    $attendance->check_in_time,
                    $attendance->check_out_time,
                    $attendance->attendance_date->toDateString(),
                    $attendance->check_out_date?->toDateString()
                );
            }
            if (
                ! in_array($attendance->status, ['Absent', 'Leave'], true)
                && $attendance->overtime_approval_status !== 'Rejected'
                && Attendance::isOvertimeCheckout($attendance->check_out_time)
            ) {
                $attendance->status = 'Overtime';
                if (empty($attendance->overtime_approval_status)) {
                    $attendance->overtime_approval_status = 'Pending';
                }
                if (empty($attendance->overtime_reason)) {
                    $attendance->overtime_reason = 'Auto-detected from checkout time.';
                }
            }
            $attendance->save();
            $processed++;
        }

        return $processed;
    }

    protected function normalizeLogs(array $logs): array
    {
        if (Arr::isAssoc($logs)) {
            return [$logs];
        }

        return $logs;
    }

    protected function resolveEmployee(array $log): ?Employee
    {
        $identifier = $log['biometric_id']
            ?? $log['employee_id']
            ?? $log['enroll_id']
            ?? $log['pin']
            ?? $log['user_id']
            ?? null;

        if (! $identifier) {
            return null;
        }

        return Employee::query()->where('biometric_id', (string) $identifier)->first()
            ?? Employee::query()->where('employee_id', (string) $identifier)->first()
            ?? optional(EmployeeDevice::query()->where('device_identifier', (string) $identifier)->with('employee')->first())->employee;
    }

    protected function resolveTimestamp(array $log): ?Carbon
    {
        $value = $log['timestamp']
            ?? $log['time']
            ?? $log['datetime']
            ?? $log['punch_time']
            ?? null;

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolveStatus(array $log, string $time): string
    {
        $status = strtolower((string) ($log['status'] ?? $log['event'] ?? $log['type'] ?? ''));

        if (str_contains($status, 'leave')) {
            return 'Leave';
        }

        if (str_contains($status, 'absent')) {
            return 'Absent';
        }

        if (str_contains($status, 'half')) {
            return 'Half Day';
        }

        return Attendance::statusForCheckIn($time);
    }

    protected function isCheckoutLog(array $log): bool
    {
        $event = $log['status'] ?? $log['event'] ?? $log['type'] ?? null;

        if (is_numeric($event)) {
            return (int) $event === 1;
        }

        return str_contains(strtolower((string) $event), 'check-out')
            || str_contains(strtolower((string) $event), 'checkout');
    }
}
