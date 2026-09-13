<?php

use App\Models\Article;
use App\Models\User;
use App\Support\UploadLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/**
 * An English framework page reaching the owner or a visitor is the bug this
 * file exists to catch, so assert on words Laravel ships rather than only on
 * the Greek we wrote — a missing view falls back to those silently.
 */
function assertNoEnglishFrameworkPage(TestResponse $response): void
{
    foreach (['Not Found', 'Server Error', 'Page Expired', 'Payload Too Large', 'Whoops'] as $english) {
        expect($response->getContent())->not->toContain($english);
    }
}

it('answers an unknown article id in the admin in Greek', function () {
    $response = test()
        ->actingAs(User::factory()->create())
        ->get('/admin/articles/4040/edit');

    $response->assertStatus(404)->assertSee('Η σελίδα δεν βρέθηκε', false);
    assertNoEnglishFrameworkPage($response);
});

it('answers an unknown admin address in Greek', function () {
    $response = test()
        ->actingAs(User::factory()->create())
        ->get('/admin/kati-pou-den-yparxei');

    $response->assertStatus(404)->assertSee('Η σελίδα δεν βρέθηκε', false);
    assertNoEnglishFrameworkPage($response);
});

it('keeps the public 404 on the site rather than the plain error page', function () {
    // A visitor who mistypes a news address should land on a page of the site,
    // not on the bare page the admin gets.
    test()->get('/news/den-yparxei-ayto')
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page->component('NotFound'));
});

it('says in Greek that something broke, without showing what', function () {
    Config::set('app.debug', false);
    Route::get('/__test_boom', fn () => throw new RuntimeException('leaked internals'))->middleware('web');

    $response = test()->get('/__test_boom');

    $response->assertStatus(500)->assertSee('Κάτι πήγε στραβά', false);
    expect($response->getContent())->not->toContain('leaked internals');
    assertNoEnglishFrameworkPage($response);
});

it('explains an expired form in Greek instead of showing Page Expired', function () {
    Config::set('app.debug', false);
    Route::post('/__test_stale', fn () => throw new TokenMismatchException)->middleware('web');

    $response = test()->post('/__test_stale');

    $response->assertStatus(419)->assertSee('Η σελίδα έληξε', false);
    assertNoEnglishFrameworkPage($response);
});

it('names the real upload limit when PHP refuses the request before parsing files', function () {
    $article = Article::factory()->create();

    $response = test()
        ->actingAs(User::factory()->create())
        ->call('POST', "/admin/articles/{$article->id}/cover", [], [], [], [
            'CONTENT_LENGTH' => (string) (13 * 1024 * 1024),
            'CONTENT_TYPE' => 'multipart/form-data',
        ], str_repeat('x', 13 * 1024 * 1024));

    $response->assertStatus(413)
        ->assertSee('Η φωτογραφία είναι μεγαλύτερη από όσο δέχεται ο server. Ανεβάστε μικρότερη φωτογραφία.', false)
        ->assertSee('Το όριο είναι '.UploadLimits::articleImageMaxLabel().'.', false);
    assertNoEnglishFrameworkPage($response);
});

it('styles the error pages from a file that does not need a build', function () {
    Config::set('app.debug', false);
    Route::get('/__test_boom_css', fn () => abort(500))->middleware('web');

    // The Vite manifest is one of the things that can be missing when a visitor
    // sees a 500, so the page that reports it must not read the manifest.
    $response = test()->get('/__test_boom_css');

    expect($response->getContent())
        ->toContain('/css/error.css')
        ->not->toContain('/build/');
    expect(file_exists(public_path('css/error.css')))->toBeTrue();
});
