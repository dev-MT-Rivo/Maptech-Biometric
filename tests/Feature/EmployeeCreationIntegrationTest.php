<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDevice;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeCreationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_creation_also_creates_employee_login_and_primary_device_mapping(): void
    {
        $admin = $this->createAdmin();
        $employeeRole = Role::create(['name' => 'Employee', 'permissions' => []]);

        $this->actingAs($admin)
            ->get(route('admin.employees.create'))
            ->assertOk()
            ->assertSee('Login account')
            ->assertSee('Biometric device user ID')
            ->assertDontSee('Select User');

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $this->employeeData([
                'device_identifier' => 'BIO-NEW-01',
                'device_name' => 'Front desk terminal',
            ]))
            ->assertRedirect(route('admin.employees.index'));

        $user = User::where('email', 'maya@example.test')->firstOrFail();
        $employee = Employee::where('employee_id', 'EMP-NEW-01')->firstOrFail();

        $this->assertSame('Maya Santos', $user->name);
        $this->assertSame($employeeRole->id, $user->role_id);
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));
        $this->assertSame($user->id, $employee->user_id);
        $this->assertSame('BIO-NEW-01', $employee->biometric_id);
        $this->assertDatabaseHas('employee_devices', [
            'employee_id' => $employee->id,
            'device_identifier' => 'BIO-NEW-01',
            'device_name' => 'Front desk terminal',
            'is_primary' => true,
        ]);
    }

    public function test_employee_can_be_created_with_a_login_without_a_device_mapping(): void
    {
        $admin = $this->createAdmin();
        Role::create(['name' => 'Employee', 'permissions' => []]);

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $this->employeeData())
            ->assertRedirect(route('admin.employees.index'));

        $employee = Employee::where('employee_id', 'EMP-NEW-01')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'email' => 'maya@example.test',
        ]);
        $this->assertNull($employee->biometric_id);
        $this->assertSame(0, EmployeeDevice::count());
    }

    public function test_employee_creation_validates_device_identifier_uniqueness(): void
    {
        $admin = $this->createAdmin();
        Role::create(['name' => 'Employee', 'permissions' => []]);
        $existingEmployee = $this->createExistingEmployee();
        EmployeeDevice::create([
            'employee_id' => $existingEmployee->id,
            'device_identifier' => 'BIO-EXISTING',
            'is_primary' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.employees.create'))
            ->post(route('admin.employees.store'), $this->employeeData([
                'device_identifier' => 'BIO-EXISTING',
            ]))
            ->assertSessionHasErrors('device_identifier');

        $this->assertDatabaseMissing('users', ['email' => 'maya@example.test']);
        $this->assertDatabaseMissing('employees', ['employee_id' => 'EMP-NEW-01']);
    }

    private function createAdmin(): User
    {
        $role = Role::create(['name' => 'HR Admin', 'permissions' => []]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createExistingEmployee(): Employee
    {
        $user = User::factory()->create();

        return Employee::create([
            'user_id' => $user->id,
            'employee_id' => 'EMP-EXISTING',
            'first_name' => 'Existing',
            'last_name' => 'Person',
            'email' => 'existing@example.test',
            'joining_date' => '2025-01-01',
        ]);
    }

    private function employeeData(array $overrides = []): array
    {
        return array_merge([
            'employee_id' => 'EMP-NEW-01',
            'first_name' => 'Maya',
            'last_name' => 'Santos',
            'email' => 'maya@example.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'joining_date' => '2026-11-09',
            'is_active' => '1',
        ], $overrides);
    }
}
