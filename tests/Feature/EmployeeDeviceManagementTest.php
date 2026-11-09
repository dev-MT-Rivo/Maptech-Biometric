<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDevice;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDeviceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_filter_employee_device_mappings(): void
    {
        $role = Role::create([
            'name' => 'HR Admin',
            'permissions' => [],
        ]);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $employee = $this->createEmployee('EMP-100', 'Maya', 'Santos');
        $otherEmployee = $this->createEmployee('EMP-200', 'Noah', 'Reyes');

        EmployeeDevice::create([
            'employee_id' => $employee->id,
            'device_identifier' => 'BIO-100',
            'device_name' => 'Main entrance',
            'is_primary' => true,
        ]);
        EmployeeDevice::create([
            'employee_id' => $otherEmployee->id,
            'device_identifier' => 'BIO-200',
            'device_name' => 'Warehouse',
            'is_primary' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.employee-devices.index', ['search' => 'EMP-100', 'primary' => '1']))
            ->assertOk()
            ->assertSee('Maya Santos')
            ->assertSee('BIO-100')
            ->assertDontSee('Noah Reyes')
            ->assertDontSee('BIO-200')
            ->assertSee('Total mappings')
            ->assertSee('Employees assigned');

        $this->actingAs($admin)
            ->get(route('admin.employee-devices.index', ['primary' => '0']))
            ->assertOk()
            ->assertSee('Noah Reyes')
            ->assertSee('BIO-200')
            ->assertDontSee('BIO-100');
    }

    private function createEmployee(string $employeeId, string $firstName, string $lastName): Employee
    {
        $user = User::factory()->create();

        return Employee::create([
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($employeeId).'@example.test',
            'joining_date' => '2025-01-01',
        ]);
    }
}
