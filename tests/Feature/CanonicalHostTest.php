<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.url' => 'https://karanikolisteam.gr']);
});

function inProduction(): void
{
    app()->detectEnvironment(fn (): string => 'production');
}

it('moves a page read on the old address to the same page on the domain, permanently', function () {
    inProduction();

    $this->get('http://karanikolis.twomymenu.com/news?page=2')
        ->assertStatus(301)
        ->assertRedirect('https://karanikolisteam.gr/news?page=2');
});

it('moves the www form of the domain as well', function () {
    inProduction();

    $this->get('https://www.karanikolisteam.gr/schedule')
        ->assertStatus(301)
        ->assertRedirect('https://karanikolisteam.gr/schedule');
});

it('moves a page that does not exist, so the right host shows the Greek 404', function () {
    inProduction();

    $this->get('http://karanikolis.twomymenu.com/mia-anyparkti-selida')
        ->assertStatus(301)
        ->assertRedirect('https://karanikolisteam.gr/mia-anyparkti-selida');
});

it('moves the domain read over plain http to https', function () {
    inProduction();

    $this->get('http://karanikolisteam.gr/about')
        ->assertStatus(301)
        ->assertRedirect('https://karanikolisteam.gr/about');
});

it('does not ask for https when APP_URL itself is plain http', function () {
    inProduction();
    config(['app.url' => 'http://karanikolisteam.gr']);

    $this->get('http://karanikolisteam.gr/')->assertOk();
});

it('serves the domain itself without a redirect', function () {
    inProduction();

    $this->get('https://karanikolisteam.gr/')->assertOk();
});

it('leaves a submitted form alone, so a form open on the old address still arrives', function () {
    inProduction();

    $response = $this->post('http://karanikolis.twomymenu.com/admin/login', []);

    expect($response->getStatusCode())->not->toBe(301);
});

it('never redirects outside production, so the phone preview on the local network keeps working', function () {
    $this->get('http://192.168.1.46:8000/')->assertOk();
});
