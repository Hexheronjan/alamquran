<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::query();

        // Search nama, username, email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter status aktif/nonaktif
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $admins = $query->latest()->paginate(15)->withQueryString();

        // Statistik ringkas
        $totalAdmins   = Admin::count();
        $activeAdmins  = Admin::where('is_active', true)->count();
        $inactiveAdmins = Admin::where('is_active', false)->count();

        $roles = Admin::getRoleLabels();

        return view('admin.admins.index', compact(
            'admins',
            'totalAdmins',
            'activeAdmins',
            'inactiveAdmins',
            'roles'
        ));
    }

    public function create()
    {
        $roles       = Admin::getRoleLabels();
        $permissions = Admin::getAvailablePermissions();

        return view('admin.admins.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'username'           => ['required', 'string', 'max:50', 'alpha_dash', 'unique:admins,username'],
            'email'              => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'password'           => ['required', 'string', 'min:6', 'confirmed'],
            'role'               => ['required', Rule::in(array_keys(Admin::getRoleLabels()))],
            'assigned_toko'      => ['nullable', Rule::in(['kantin_1', 'kantin_2', 'aqumart'])],
            'is_active'          => ['nullable', 'boolean'],
            'custom_permissions' => ['nullable', 'array'],
        ], [
            'username.unique'     => 'Username sudah digunakan oleh admin lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-) dan garis bawah (_).',
            'email.unique'        => 'Email sudah terdaftar.',
            'password.confirmed'  => 'Konfirmasi password tidak cocok.',
            'password.min'        => 'Password minimal 6 karakter.',
        ]);

        $permissions = [];
        $availableKeys = array_keys(Admin::getAvailablePermissions());
        foreach ($availableKeys as $permKey) {
            $permissions[$permKey] = !empty($request->input("custom_permissions.{$permKey}"));
        }

        // assigned_toko hanya relevan untuk role kasir
        $assignedToko = ($request->role === Admin::ROLE_KASIR)
            ? $request->assigned_toko
            : null;

        $admin = Admin::create([
            'name'          => $request->name,
            'username'      => strtolower($request->username),
            'email'         => strtolower($request->email),
            'password'      => Hash::make($request->password),
            'role'          => $request->role,
            'assigned_toko' => $assignedToko,
            'is_active'     => $request->boolean('is_active', true),
            'permissions'   => $permissions,
        ]);

        AdminActivityLogger::log(
            'create_admin',
            "Menambahkan akun admin baru: {$admin->name} ({$admin->role_label})"
                . ($assignedToko ? " — Kantin: {$admin->assigned_toko_label}" : ''),
            'Admin',
            $admin->id
        );

        return redirect()->route('admin.admins.index')
            ->with('success', "Akun admin '{$admin->name}' berhasil dibuat.");
    }

    public function edit($id)
    {
        $admin       = Admin::findOrFail($id);
        $roles       = Admin::getRoleLabels();
        $permissions = Admin::getAvailablePermissions();

        return view('admin.admins.edit', compact('admin', 'roles', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'username'           => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('admins', 'username')->ignore($admin->id)],
            'email'              => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'password'           => ['nullable', 'string', 'min:6', 'confirmed'],
            'role'               => ['required', Rule::in(array_keys(Admin::getRoleLabels()))],
            'assigned_toko'      => ['nullable', Rule::in(['kantin_1', 'kantin_2', 'aqumart'])],
            'is_active'          => ['nullable', 'boolean'],
            'custom_permissions' => ['nullable', 'array'],
        ], [
            'username.unique'    => 'Username sudah digunakan oleh admin lain.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        // Proteksi Super Admin Utama (id: 1)
        if ($admin->id === 1) {
            $role     = Admin::ROLE_SUPER_ADMIN;
            $isActive = true;
        } else {
            $role     = $request->role;
            $isActive = ($admin->id === auth('admin')->id()) ? true : $request->boolean('is_active');
        }

        $permissions = [];
        $availableKeys = array_keys(Admin::getAvailablePermissions());
        foreach ($availableKeys as $permKey) {
            $permissions[$permKey] = !empty($request->input("custom_permissions.{$permKey}"));
        }

        // assigned_toko hanya relevan untuk role kasir
        $assignedToko = ($role === Admin::ROLE_KASIR)
            ? $request->assigned_toko
            : null;

        $updateData = [
            'name'          => $request->name,
            'username'      => strtolower($request->username),
            'email'         => strtolower($request->email),
            'role'          => $role,
            'assigned_toko' => $assignedToko,
            'is_active'     => $isActive,
            'permissions'   => $permissions,
        ];

        if (!empty($request->password)) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        AdminActivityLogger::log(
            'update_admin',
            "Memperbarui data akun admin: {$admin->name} ({$admin->role_label})"
                . ($assignedToko ? " — Kantin: {$admin->assigned_toko_label}" : ''),
            'Admin',
            $admin->id
        );

        return redirect()->route('admin.admins.index')
            ->with('success', "Data admin '{$admin->name}' berhasil diperbarui.");
    }

    public function toggleStatus($id)
    {
        $admin = Admin::findOrFail($id);

        // Proteksi: Super admin utama atau akun yang sedang login tidak dapat dinonaktifkan
        if ($admin->id === 1 || $admin->id === auth('admin')->id()) {
            return back()->with('error', 'Akun Admin Utama atau akun yang sedang Anda gunakan tidak dapat dinonaktifkan.');
        }

        $newStatus = !$admin->is_active;
        $admin->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        AdminActivityLogger::log(
            'toggle_status_admin',
            "Mengubah status admin {$admin->name} menjadi: {$statusText}",
            'Admin',
            $admin->id
        );

        return back()->with('success', "Akun admin '{$admin->name}' berhasil {$statusText}.");
    }

    public function resetPassword(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        AdminActivityLogger::log(
            'reset_password_admin',
            "Mereset password untuk akun admin: {$admin->name}",
            'Admin',
            $admin->id
        );

        return back()->with('success', "Password untuk admin '{$admin->name}' berhasil direset.");
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);

        // Proteksi: Super admin atau akun sendiri tidak dapat dihapus
        if ($admin->id === 1 || $admin->role === Admin::ROLE_SUPER_ADMIN || $admin->id === auth('admin')->id()) {
            return back()->with('error', 'Akun Super Admin atau akun yang sedang Anda gunakan tidak dapat dihapus.');
        }

        $adminName = $admin->name;
        $admin->delete();

        AdminActivityLogger::log(
            'delete_admin',
            "Menghapus akun admin: {$adminName}",
            'Admin',
            $id
        );

        return back()->with('success', "Akun admin '{$adminName}' berhasil dihapus.");
    }

    public function activityLogs(Request $request)
    {
        $query = AdminActivityLog::with('admin')->latest();

        // Filter admin
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter jenis aksi
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Search deskripsi / IP / nama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('admin_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs   = $query->paginate(20)->withQueryString();
        $admins = Admin::select('id', 'name', 'role')->get();

        $actionTypes = [
            'login'                => 'Login Berhasil',
            'login_failed'         => 'Login Gagal',
            'logout'               => 'Logout',
            'create_admin'         => 'Tambah Admin',
            'update_admin'         => 'Edit Admin',
            'toggle_status_admin'  => 'Ubah Status Admin',
            'reset_password_admin' => 'Reset Password Admin',
            'delete_admin'         => 'Hapus Admin',
            'update_santri_status' => 'Ubah Status Santri',
            'update_santri_data'   => 'Update Data Santri',
            'delete_santri'        => 'Hapus Data Santri',
            'approve_deposit'      => 'Setujui Deposit Transfer',
            'reject_deposit'       => 'Tolak Deposit Transfer',
            'delete_deposit'       => 'Hapus Deposit Transfer',
        ];

        return view('admin.admins.activity_logs', compact('logs', 'admins', 'actionTypes'));
    }
}