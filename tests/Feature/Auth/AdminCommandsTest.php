<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('creates an administrator from a one-time password file and removes that file', function () {
    $passwordFile = storage_path('framework/testing/admin-password.txt');
    File::ensureDirectoryExists(dirname($passwordFile));
    File::put($passwordFile, 'very-secret-password');

    $this->artisan('admin:create', [
        '--email' => 'owner@example.com',
        '--password-file' => $passwordFile,
    ])
        ->expectsOutput('Ο διαχειριστής owner@example.com είναι έτοιμος.')
        ->assertSuccessful();

    $user = User::query()->where('email', 'owner@example.com')->first();

    expect($user)->not->toBeNull()
        ->and(Hash::check('very-secret-password', $user->password))->toBeTrue()
        ->and(File::exists($passwordFile))->toBeFalse();
});

it('refuses non-interactive administrator creation without a password file', function () {
    $this->artisan('admin:create', [
        '--email' => 'owner@example.com',
    ])
        ->expectsOutput('Δώστε αρχείο κωδικού με --password-file=... Ο κωδικός δεν πρέπει να μπει στη γραμμή της εντολής.')
        ->assertFailed();

    $this->assertDatabaseMissing('users', [
        'email' => 'owner@example.com',
    ]);
});

it('lists and deletes administrators with Greek command output while preserving the last account', function () {
    $first = User::factory()->create(['email' => 'first@example.com']);
    $second = User::factory()->create(['email' => 'second@example.com']);

    $this->artisan('admin:list')
        ->expectsTable(['ID', 'Email', 'Δημιουργήθηκε'], [
            [$first->id, 'first@example.com', $first->created_at->toDateTimeString()],
            [$second->id, 'second@example.com', $second->created_at->toDateTimeString()],
        ])
        ->assertSuccessful();

    $this->artisan('admin:delete', ['--email' => 'second@example.com'])
        ->expectsOutput('Ο διαχειριστής second@example.com διαγράφηκε.')
        ->assertSuccessful();

    $this->artisan('admin:delete', ['--email' => 'first@example.com'])
        ->expectsOutput('Δεν μπορεί να διαγραφεί ο τελευταίος διαχειριστής.')
        ->assertFailed();
});
