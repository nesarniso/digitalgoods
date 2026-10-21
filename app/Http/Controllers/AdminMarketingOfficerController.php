<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Entrepreneurs\Models\EntrepreneurProfile;
use App\Domain\Users\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminMarketingOfficerController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('employee.view'), 403);

        $officers = User::query()
            ->role('marketing_officer')
            ->with('employeeProfile')
            ->select('users.*')
            ->selectSub(
                EntrepreneurProfile::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('entrepreneur_profiles.referred_by_user_id', 'users.id'),
                'referred_entrepreneurs_count'
            )
            ->when($request->filled('q'), fn ($query) => $query->where(function ($user) use ($request) {
                $term = '%'.$request->string('q').'%';
                $user->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term);
            }))
            ->latest('users.id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.marketing-officers', [
            'officers' => $officers,
            'totalCount' => User::role('marketing_officer')->count(),
            'activeCount' => User::role('marketing_officer')->whereHas('employeeProfile', fn ($query) => $query->where('is_active', true))->count(),
            'entrepreneurCount' => EntrepreneurProfile::query()->whereIn('referred_by_user_id', User::role('marketing_officer')->select('users.id'))->count(),
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
            $user->assignRole('marketing_officer');

            $profile = EmployeeProfile::create([
                'user_id' => $user->id,
                'employee_id' => 'MO-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'department' => 'Marketing',
                'designation' => 'Marketing Officer',
                'area' => $data['area'] ?? null,
            ]);

            return [$user, $profile];
        });

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'created',
            module: 'Marketing Officers',
            entityType: 'EmployeeProfile',
            entityId: (string) $profile->id,
            newValue: ['user_id' => $user->id, 'area' => $profile->area],
        );

        return redirect()->route('admin.marketing-officers')->with('success', 'Marketing officer created.');
    }

    public function update(Request $request, EmployeeProfile $officer)
    {
        abort_unless($request->user()->can('employee.manage'), 403);
        abort_unless($officer->user?->hasRole('marketing_officer'), 404);

        $data = $request->validate([
            'area' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
        $old = $officer->only(['area', 'is_active']);
        $officer->update($data);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'updated',
            module: 'Marketing Officers',
            entityType: 'EmployeeProfile',
            entityId: (string) $officer->id,
            oldValue: $old,
            newValue: $officer->only(['area', 'is_active']),
        );

        return back()->with('success', 'Marketing officer updated.');
    }
}
