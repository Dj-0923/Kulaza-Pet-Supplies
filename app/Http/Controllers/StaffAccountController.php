<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffAccountController extends Controller
{
    public function index()
    {
        $staffAccounts = User::where('role', 'staff')->orderBy('name')->get();

        return view('staff-accounts', compact('staffAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['full_name'],
            'username' => $validated['username'],
            'email' => Str::lower($validated['username']).'@kulaza.local',
            'password' => $validated['password'],
            'role' => 'staff',
        ]);

        return redirect()->route('staff')->with('status', 'Staff account created.');
    }

    public function updateStatus(Request $request, User $user)
    {
        if ($user->role !== 'staff') {
            abort(404);
        }

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update(['is_active' => $validated['is_active']]);

        return redirect()->route('staff')->with('status', 'Staff work status updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->role !== 'staff') {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $validated['password']]);

        return redirect()->route('staff')->with('status', 'Staff password reset successfully.');
    }
}
