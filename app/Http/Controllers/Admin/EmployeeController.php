<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\EmploymentType;
use App\Models\EmployeeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with(['user', 'department', 'position'])
            ->latest()
            ->paginate(10);

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $types = EmploymentType::orderBy('name')->get();
        $statuses = EmployeeStatus::orderBy('name')->get();

        return view('admin.employees.create', compact('departments', 'positions', 'types', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:255|unique:employees,employee_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'address' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => [
                'nullable',
                Rule::exists('positions', 'id')->where(
                    fn ($query) => $query->where('department_id', $request->input('department_id'))
                ),
            ],
            'joining_date' => 'required|date',
            'employment_type_id' => 'nullable|exists:employment_types,id',
            'employee_status_id' => 'nullable|exists:employee_statuses,id',
            'device_identifier' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('employees', 'biometric_id'),
                Rule::unique('employee_devices', 'device_identifier'),
            ],
            'device_name' => 'nullable|string|max:255',
            'monthly_salary' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $employee = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => Role::where('name', 'Employee')->firstOrFail()->id,
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'position_id' => $validated['position_id'] ?? null,
                'joining_date' => $validated['joining_date'],
                'employment_type_id' => $validated['employment_type_id'] ?? null,
                'employee_status_id' => $validated['employee_status_id'] ?? null,
                'biometric_id' => $validated['device_identifier'] ?? null,
                'monthly_salary' => $validated['monthly_salary'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            if (! empty($validated['device_identifier'])) {
                $employee->devices()->create([
                    'device_identifier' => $validated['device_identifier'],
                    'device_name' => $validated['device_name'] ?? null,
                    'is_primary' => true,
                ]);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'create_employee',
                'model' => 'Employee',
                'model_id' => $employee->id,
                'description' => "Created employee and login account: {$employee->full_name}",
                'new_values' => $employee->toArray(),
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'create_user',
                'model' => 'User',
                'model_id' => $user->id,
                'description' => "Created employee login account: {$user->email}",
                'new_values' => $user->toArray(),
            ]);

            return $employee;
        });

        $message = 'Employee and login account created successfully.';
        if (! empty($validated['device_identifier'])) {
            $message .= ' Biometric device ID linked.';
        }

        return redirect()->route('admin.employees.index')->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'position', 'status', 'employmentType', 'devices']);

        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $users = User::where('id', $employee->user_id)
            ->orWhereDoesntHave('employee')
            ->orderBy('name')
            ->get();
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $types = EmploymentType::orderBy('name')->get();
        $statuses = EmployeeStatus::orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'users', 'departments', 'positions', 'types', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:employees,user_id,' . $employee->id,
            'employee_id' => 'required|string|max:255|unique:employees,employee_id,' . $employee->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'address' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => [
                'nullable',
                Rule::exists('positions', 'id')->where(
                    fn ($query) => $query->where('department_id', $request->input('department_id'))
                ),
            ],
            'joining_date' => 'required|date',
            'employment_type_id' => 'nullable|exists:employment_types,id',
            'employee_status_id' => 'nullable|exists:employee_statuses,id',
            'biometric_id' => 'nullable|string|max:255|unique:employees,biometric_id,' . $employee->id,
            'monthly_salary' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $oldValues = $employee->toArray();

        $employee->update([
            'user_id' => $request->user_id,
            'employee_id' => $request->employee_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address' => $request->address,
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
            'joining_date' => $request->joining_date,
            'employment_type_id' => $request->employment_type_id,
            'employee_status_id' => $request->employee_status_id,
            'biometric_id' => $request->biometric_id,
            'monthly_salary' => $request->monthly_salary,
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_employee',
            'model' => 'Employee',
            'model_id' => $employee->id,
            'description' => "Updated employee: {$employee->full_name}",
            'old_values' => $oldValues,
            'new_values' => $employee->toArray(),
        ]);

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete_employee',
            'model' => 'Employee',
            'model_id' => $employee->id,
            'description' => "Deleted employee: {$employee->full_name}",
            'old_values' => $employee->toArray(),
        ]);

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
