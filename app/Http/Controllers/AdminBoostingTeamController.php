<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Users\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminBoostingTeamController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('employee.view'), 403);

        $members = User::query()
            ->role('boosting_team')
            ->select('users.*')
            ->with('employeeProfile')
            ->when($request->filled('q'), fn ($query) => $query->where(function ($user) use ($request) {
                $term = '%'.$request->string('q').'%';
                $user->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            }))
            ->latest('users.id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.boosting-team', [
            'members' => $members,
            'totalCount' => User::role('boosting_team')->count(),
            'activeCount' => User::role('boosting_team')
                ->whereHas('employeeProfile', fn ($query) => $query->where('is_active', true))
                ->count(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('employee.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'area' => ['nullable', 'string', 'max:255'],
        ]);

        [$user, $profile] = DB::transaction(function () use ($data) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('boosting_team');

            $profile = EmployeeProfile::create([
                'user_id' => $user->id,
                'employee_id' => 'BT-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'department' => 'Marketing',
                'designation' => 'Boosting Team Member',
                'area' => $data['area'] ?? null,
            ]);

            return [$user, $profile];
        });

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'created',
            module: 'Boosting Team',
            entityType: 'EmployeeProfile',
            entityId: (string) $profile->id,
            newValue: ['user_id' => $user->id, 'area' => $profile->area],
        );

        return redirect()->route('admin.boosting-team')->with('success', 'Boosting team member created.');
    }

    public function update(Request $request, EmployeeProfile $member)
    {
        abort_unless($request->user()->can('employee.manage'), 403);
        abort_unless($member->user?->hasRole('boosting_team'), 404);

        $data = $request->validate([
            'area' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
        $old = $member->only(['area', 'is_active']);
        $member->update($data);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'updated',
            module: 'Boosting Team',
            entityType: 'EmployeeProfile',
            entityId: (string) $member->id,
            oldValue: $old,
            newValue: $member->only(['area', 'is_active']),
        );

        return back()->with('success', 'Boosting team member updated.');
    }
}
