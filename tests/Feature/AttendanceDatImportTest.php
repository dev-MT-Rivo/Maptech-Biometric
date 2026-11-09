<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AttendanceDatImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_import_historical_zkteco_dat_attendance(): void
    {
        $admin = $this->createSuperAdmin();
        $employee = $this->createEmployee();
        $device = BiometricDevice::create([
            'name' => 'Main Office Terminal',
            'serial_number' => 'DAT-IMPORT-DEVICE',
        ]);
        $file = UploadedFile::fake()->createWithContent('attendance.dat', implode("\r\n", [
            "1001\t2026-10-12 08:00:00\t24\t0\t1\t0",
            "1001\t2026-10-12 15:30:00\t24\t1\t1\t0",
            "UNKNOWN\t2026-10-12 09:00:00\t24\t0\t1\t0",
            'invalid row',
        ]));

        $this->actingAs($admin)
            ->get(route('admin.attendance.import'))
            ->assertOk()
            ->assertSee('Upload attendance file')
            ->assertSee('Main Office Terminal');

        $this->actingAs($admin)
            ->post(route('admin.attendance.import.store'), [
                'file' => $file,
                'device_id' => $device->id,
            ])
            ->assertRedirect(route('admin.attendance.import'))
            ->assertSessionHas('import_result', [
                'processed' => 2,
                'skipped' => 2,
                'date_from' => '2026-10-12',
                'date_to' => '2026-10-12',
            ]);

        $attendance = Attendance::where('employee_id', $employee->id)->firstOrFail();
        $this->assertSame('Main Office Terminal', $attendance->source);
        $this->assertSame('08:00:00', $attendance->check_in_time);
        $this->assertSame('15:30:00', $attendance->check_out_time);
        $this->assertSame('Undertime', $attendance->status);

        $this->actingAs($admin)
            ->post(route('admin.attendance.import.store'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'attendance.dat',
                    "1001\t2026-10-12 07:55:00\t24\t0\t1\t0"
                ),
                'device_id' => $device->id,
            ])
            ->assertRedirect(route('admin.attendance.import'));

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'source' => 'Main Office Terminal',
            'check_in_time' => '07:55:00',
            'check_out_time' => '15:30:00',
        ]);
    }

    public function test_only_super_admin_can_open_attendance_import(): void
    {
        $hrRole = Role::create(['name' => 'HR Admin', 'permissions' => []]);
        $hrAdmin = User::factory()->create(['role_id' => $hrRole->id]);

        $this->actingAs($hrAdmin)
            ->get(route('admin.attendance.import'))
            ->assertForbidden();

        $this->actingAs($hrAdmin)
            ->post(route('admin.attendance.import.store'))
            ->assertForbidden();
    }

    public function test_import_keeps_next_day_punches_on_their_calendar_day_and_detaches_a_misdated_checkout(): void
    {
        $admin = $this->createSuperAdmin();
        $employee = $this->createEmployee();
        $device = BiometricDevice::create([
            'name' => 'Night Shift Terminal',
            'serial_number' => 'NIGHT-SHIFT-DEVICE',
        ]);
        Attendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-27',
            'check_in_time' => '08:00:00',
            'check_out_date' => '2026-10-28',
            'check_out_time' => '00:14:14',
            'status' => 'Present',
            'source' => $device->name,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.attendance.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('night-shift.dat', implode("\r\n", [
                    "1001\t2026-10-27 08:00:00\t24\t0\t1\t0",
                    "1001\t2026-10-28 00:14:14\t24\t0\t1\t0",
                    "1001\t2026-10-28 00:14:21\t24\t1\t1\t0",
                ])),
                'device_id' => $device->id,
            ])
            ->assertRedirect(route('admin.attendance.import'));

        $this->assertDatabaseCount('attendances', 2);
        $october27 = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', '2026-10-27')
            ->firstOrFail();
        $october28 = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', '2026-10-28')
            ->firstOrFail();
        $this->assertSame('08:00:00', $october27->check_in_time);
        $this->assertNull($october27->check_out_time);
        $this->assertNull($october27->check_out_date);
        $this->assertSame('00:14:14', $october28->check_in_time);
        $this->assertSame('00:14:21', $october28->check_out_time);

        $this->actingAs($admin)
            ->get(route('admin.attendance.hr-admin', [
                'date_from' => '2026-10-27',
                'date_to' => '2026-10-27',
            ]))
            ->assertOk()
            ->assertDontSee('Oct 28, 2026')
            ->assertDontSee('00:14:14 AM');
    }

    public function test_import_requires_a_dat_file_and_known_employee_pins(): void
    {
        $admin = $this->createSuperAdmin();
        $employee = $this->createEmployee();

        $this->actingAs($admin)
            ->from(route('admin.attendance.import'))
            ->post(route('admin.attendance.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('attendance.txt', '1001'),
            ])
            ->assertSessionHasErrors('file');

        $this->actingAs($admin)
            ->from(route('admin.attendance.import'))
            ->post(route('admin.attendance.import.store'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'unknown.dat',
                    "UNKNOWN\t2026-10-12 09:00:00\t0"
                ),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('attendances', ['employee_id' => $employee->id]);
    }

    private function createSuperAdmin(): User
    {
        $role = Role::create(['name' => 'Super Admin', 'permissions' => []]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createEmployee(): Employee
    {
        $user = User::factory()->create();

        return Employee::create([
            'user_id' => $user->id,
            'employee_id' => 'EMP-DAT-1001',
            'first_name' => 'Dana',
            'last_name' => 'Reyes',
            'email' => 'dana@example.test',
            'joining_date' => '2025-01-01',
            'biometric_id' => '1001',
        ]);
    }
}
