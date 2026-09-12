<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('serves the administrator sign-in page without the public site shell', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Login'));

    $login = file_get_contents(resource_path('js/Pages/Admin/Login.jsx'));

    expect($login)->toContain('Σύνδεση διαχείρισης')
        ->and($login)->toContain('Συνδεθείτε για να διαχειριστείτε τις ειδήσεις της σχολής.');
});

it('signs an administrator in with a regenerated session and generic Greek failures', function () {
    User::factory()->create([
        'email' => 'owner@example.com',
        'password' => Hash::make('correct-password'),
    ]);

    $this->from('/admin/login')
        ->post('/admin/login', [
            'email' => 'owner@example.com',
            'password' => 'wrong-password',
        ])
        ->assertSessionHasErrors([
            'email' => 'Τα στοιχεία σύνδεσης δεν είναι σωστά.',
        ]);

    $this->assertGuest();

    $this->withSession(['before_login' => 'kept'])
        ->post('/admin/login', [
            'email' => 'owner@example.com',
            'password' => 'correct-password',
        ])
        ->assertRedirect('/admin');

    $this->assertAuthenticated();
});

it('locks sign-in on the eleventh wrong password without confirming whether the email exists', function () {
    RateLimiter::clear('unknown@example.com|127.0.0.1');

    for ($attempt = 1; $attempt <= 10; $attempt++) {
        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'unknown@example.com',
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors([
                'email' => 'Τα στοιχεία σύνδεσης δεν είναι σωστά.',
            ]);
    }

    $this->from('/admin/login')
        ->post('/admin/login', [
            'email' => 'unknown@example.com',
            'password' => 'wrong-password',
        ])
        ->assertSessionHasErrors([
            'email' => 'Έγιναν πολλές αποτυχημένες προσπάθειες. Δοκιμάστε ξανά σε 15 λεπτά.',
        ]);
});

it('discards the session when an administrator signs out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/logout')
        ->assertRedirect('/admin/login');

    $this->assertGuest();
});

it('redirects direct unauthenticated management requests to the sign-in page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/admin/login');
})->with([
    ['get', '/admin'],
    ['post', '/admin/logout'],
    ['patch', '/admin/articles/1/publish'],
    ['patch', '/admin/articles/1/unpublish'],
    ['delete', '/admin/articles/1'],
    ['get', '/admin/news'],
    ['get', '/admin/news/create'],
    ['get', '/admin/news/1/edit'],
]);

it('keeps not-yet-built management screens unavailable after sign-in', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/news')
        ->assertNotFound();
});
