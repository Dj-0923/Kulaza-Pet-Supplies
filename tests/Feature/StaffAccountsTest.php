<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('staff accounts are empty until created and persist in the database', function () {
    $this->get(route('staff'))
        ->assertOk()
        ->assertSee('No staff accounts yet. Create an account using the form above.');

    $this->post(route('staff.store'), [
        'full_name' => 'Alex Staff',
        'username' => 'alex_staff',
        'password' => 'secure-pass-123',
        'password_confirmation' => 'secure-pass-123',
    ])->assertRedirect(route('staff'))
        ->assertSessionHas('status', 'Staff account created.');

    $staffAccount = User::where('username', 'alex_staff')->firstOrFail();

    expect($staffAccount->name)->toBe('Alex Staff')
        ->and($staffAccount->role)->toBe('staff')
        ->and($staffAccount->is_active)->toBeTrue()
        ->and(Hash::check('secure-pass-123', $staffAccount->password))->toBeTrue();

    $this->get(route('staff'))
        ->assertOk()
        ->assertSee('Alex Staff')
        ->assertSee('alex_staff')
        ->assertSee('Active')
        ->assertDontSee('No staff accounts yet.');
});

test('duplicate staff usernames are rejected', function () {
    User::create([
        'name' => 'Existing Staff',
        'email' => 'existing@kulaza.local',
        'username' => 'existing',
        'role' => 'staff',
        'password' => 'secure-pass-123',
    ]);

    $this->from(route('staff'))->post(route('staff.store'), [
        'full_name' => 'Another Staff',
        'username' => 'existing',
        'password' => 'secure-pass-123',
        'password_confirmation' => 'secure-pass-123',
    ])->assertRedirect(route('staff'))
        ->assertSessionHasErrors('username');

    expect(User::where('role', 'staff')->count())->toBe(1);

    $this->get(route('staff'))
        ->assertOk()
        ->assertSee('The username has already been taken.');
});

test('admin can mark staff inactive and active again', function () {
    $staffAccount = User::create([
        'name' => 'Alex Staff',
        'email' => 'alex_staff@kulaza.local',
        'username' => 'alex_staff',
        'role' => 'staff',
        'password' => 'secure-pass-123',
    ]);

    $this->patch(route('staff.status', $staffAccount), ['is_active' => '0'])
        ->assertRedirect(route('staff'));

    expect($staffAccount->fresh()->is_active)->toBeFalse();

    $this->get(route('staff'))
        ->assertOk()
        ->assertSee('Inactive')
        ->assertSee('Mark Active');

    $this->patch(route('staff.status', $staffAccount), ['is_active' => '1'])
        ->assertRedirect(route('staff'));

    expect($staffAccount->fresh()->is_active)->toBeTrue();
});

test('staff work status cannot be changed for a non-staff user', function () {
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@kulaza.local',
        'username' => 'admin',
        'role' => 'admin',
        'password' => 'secure-pass-123',
    ]);

    $this->patch(route('staff.status', $user), ['is_active' => '0'])
        ->assertNotFound();

    expect($user->fresh()->is_active)->toBeTrue();
});

test('admin can reset a staff password', function () {
    $staffAccount = User::create([
        'name' => 'Alex Staff',
        'email' => 'alex_staff@kulaza.local',
        'username' => 'alex_staff',
        'role' => 'staff',
        'password' => 'old-password-123',
    ]);

    $this->get(route('staff'))
        ->assertOk()
        ->assertSee('Reset Password');

    $this->patch(route('staff.password', $staffAccount), [
        'password' => 'new-password-456',
        'password_confirmation' => 'new-password-456',
    ])->assertRedirect(route('staff'))
        ->assertSessionHas('status', 'Staff password reset successfully.');

    expect(Hash::check('new-password-456', $staffAccount->fresh()->password))->toBeTrue();
});

test('staff password reset rejects invalid password and non-staff account', function () {
    $staffAccount = User::create([
        'name' => 'Alex Staff',
        'email' => 'alex_staff@kulaza.local',
        'username' => 'alex_staff',
        'role' => 'staff',
        'password' => 'old-password-123',
    ]);

    $this->from(route('staff'))->patch(route('staff.password', $staffAccount), [
        'password' => 'short',
        'password_confirmation' => 'different',
    ])->assertRedirect(route('staff'))
        ->assertSessionHasErrors(['password']);

    expect(Hash::check('old-password-123', $staffAccount->fresh()->password))->toBeTrue();

    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@kulaza.local',
        'username' => 'admin',
        'role' => 'admin',
        'password' => 'admin-password-123',
    ]);

    $this->patch(route('staff.password', $admin), [
        'password' => 'new-password-456',
        'password_confirmation' => 'new-password-456',
    ])->assertNotFound();

    expect(Hash::check('admin-password-123', $admin->fresh()->password))->toBeTrue();
});
