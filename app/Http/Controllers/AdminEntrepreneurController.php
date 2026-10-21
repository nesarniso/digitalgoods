<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Entrepreneurs\Models\EntrepreneurProfile;
use App\Domain\Entrepreneurs\Models\EntrepreneurProduct;
use App\Domain\Finance\Models\Withdrawal;
use App\Domain\Finance\Services\WalletService;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminEntrepreneurController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(Request $request)
    {
        $this->authorizeEntrepreneurPermission($request, 'entrepreneur.view');

        return view('admin.entrepreneurs', [
            'entrepreneurs' => EntrepreneurProfile::query()
                ->with('user')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), fn ($query) => $query->whereHas('user', fn ($user) => $user
                    ->where('name', 'like', '%'.$request->string('q').'%')
                    ->orWhere('email', 'like', '%'.$request->string('q').'%')
                    ->orWhere('phone', 'like', '%'.$request->string('q').'%')))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeEntrepreneurPermission($request, 'entrepreneur.register');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'address' => ['required', 'string', 'max:1000'],
        ]);

        [$user, $profile] = DB::transaction(function () use ($data) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('entrepreneur');

            $profile = EntrepreneurProfile::create([
                'user_id' => $user->id,
                'entrepreneur_code' => $this->generateCode(),
                'status' => 'active',
                'address' => $data['address'],
                'verified_at' => now(),
            ]);

            return [$user, $profile];
        });

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'created',
            module: 'Entrepreneurs',
            entityType: 'EntrepreneurProfile',
            entityId: (string) $profile->id,
            newValue: ['status' => 'active', 'user_id' => $user->id],
        );

        return redirect()->route('admin.entrepreneurs')->with('created_credentials', [
            'name' => $user->name,
            'login' => $user->email ?: $user->phone,
            'password' => $data['password'],
            'entrepreneur_code' => $profile->entrepreneur_code,
        ]);
    }

    public function updateStatus(Request $request, EntrepreneurProfile $entrepreneur)
    {
        $action = $request->validate(['status' => ['required', 'in:active,rejected,suspended']])['status'];
        $this->authorizeEntrepreneurPermission($request, $action === 'active' ? 'entrepreneur.verify' : 'entrepreneur.suspend');

        $oldStatus = $entrepreneur->status;
        $entrepreneur->update([
            'status' => $action,
            'verified_at' => $action === 'active' ? now() : $entrepreneur->verified_at,
        ]);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'status_changed',
            module: 'Entrepreneurs',
            entityType: 'EntrepreneurProfile',
            entityId: (string) $entrepreneur->id,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $action],
        );

        return back()->with('success', 'Entrepreneur status updated.');
    }

    private function authorizeEntrepreneurPermission(Request $request, string $permission): void
    {
        $user = $request->user();

        abort_unless(
            $user->can($permission) || $user->hasAnyRole(['super_admin', 'admin']),
            403
        );
    }

    public function withdrawals(Request $request)
    {
        abort_unless($request->user()->can('withdrawal.approve'), 403);

        return view('admin.withdrawals', [
            'withdrawals' => Withdrawal::query()->with('entrepreneur')->latest('id')->paginate(20),
        ]);
    }

    public function report(Request $request)
    {
        $this->authorizeEntrepreneurPermission($request, 'entrepreneur.view');

        $entrepreneurs = EntrepreneurProfile::query()
            ->with('user')
            ->latest('id')
            ->paginate(20);

        return view('admin.entrepreneur-report', [
            'totalCount' => EntrepreneurProfile::query()->count(),
            'activeCount' => EntrepreneurProfile::query()->where('status', 'active')->count(),
            'pendingCount' => EntrepreneurProfile::query()->where('status', 'pending')->count(),
            'salesCount' => Order::query()->whereNotNull('entrepreneur_id')->count(),
            'deliveredCount' => Order::query()->whereNotNull('entrepreneur_id')->where('status', 'delivered')->count(),
            'revenue' => Order::query()->whereNotNull('entrepreneur_id')->sum('grand_total'),
            'profit' => OrderItem::query()->whereHas('order', fn ($query) => $query->whereNotNull('entrepreneur_id'))->sum('profit'),
            'entrepreneurs' => $entrepreneurs,
        ]);
    }

    public function show(Request $request, EntrepreneurProfile $entrepreneur, WalletService $wallet)
    {
        $this->authorizeEntrepreneurPermission($request, 'entrepreneur.view');

        $entrepreneur->load('user');
        abort_unless($entrepreneur->user, 404, 'This entrepreneur account no longer exists.');
        $orders = Order::query()
            ->where('entrepreneur_id', $entrepreneur->user_id)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.entrepreneur-detail', [
            'entrepreneur' => $entrepreneur,
            'orders' => $orders,
            'salesCount' => Order::query()->where('entrepreneur_id', $entrepreneur->user_id)->count(),
            'deliveredCount' => Order::query()->where('entrepreneur_id', $entrepreneur->user_id)->where('status', 'delivered')->count(),
            'customersCount' => User::query()->whereHas('orders', fn ($query) => $query->where('entrepreneur_id', $entrepreneur->user_id))->count(),
            'referralsCount' => EntrepreneurProfile::query()->where('referred_by_user_id', $entrepreneur->user_id)->count(),
            'revenue' => Order::query()->where('entrepreneur_id', $entrepreneur->user_id)->sum('grand_total'),
            'balance' => $wallet->balance($entrepreneur->user),
        ]);
    }

    public function destroy(Request $request, EntrepreneurProfile $entrepreneur): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeEntrepreneurPermission($request, 'entrepreneur.register');
        $user = $entrepreneur->user;

        abort_unless($user && ! $user->hasAnyRole(['super_admin', 'admin']), 422, 'This account cannot be deleted.');

        DB::transaction(function () use ($entrepreneur, $user): void {
            $entrepreneur->delete();
            $user->delete();
        });

        return redirect()->route('admin.entrepreneurs')->with('success', 'Entrepreneur account deleted.');
    }

    public function products(Request $request)
    {
        $this->authorizeEntrepreneurPermission($request, 'entrepreneur.view');

        return view('admin.entrepreneur-products', [
            'assignments' => EntrepreneurProduct::query()
                ->with(['entrepreneur', 'product'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), fn ($query) => $query->whereHas('product', fn ($product) => $product->where('name', 'like', '%'.$request->string('q').'%')->orWhere('sku', 'like', '%'.$request->string('q').'%')))
                ->latest()
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function updateWithdrawal(Request $request, Withdrawal $withdrawal, WalletService $wallet)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        abort_unless($request->user()->can($data['status'] === 'approved' ? 'withdrawal.approve' : 'withdrawal.reject'), 403);

        $wallet->processWithdrawal($withdrawal, $data['status'], $data['notes'] ?? null);

        return back()->with('success', 'Withdrawal '.$data['status'].'.');
    }

    private function generateCode(): string
    {
        do {
            $code = 'ENT-'.strtoupper(Str::random(8));
        } while (EntrepreneurProfile::query()->where('entrepreneur_code', $code)->exists());

        return $code;
    }
}
