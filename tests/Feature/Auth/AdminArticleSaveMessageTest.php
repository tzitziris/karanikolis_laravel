<?php

use App\Models\Article;
use App\Models\User;
use App\Services\AdminArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function saveMessageFor(Article $article, ?string $publishedAt): string
{
    $response = test()
        ->actingAs(User::factory()->create())
        ->put("/admin/articles/{$article->id}", [
            'body' => ['type' => 'doc', 'content' => [[
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'Κείμενο άρθρου.']],
            ]]],
            'excerpt' => 'Σύνοψη.',
            'published_at' => $publishedAt,
            'title' => $article->title,
        ]);

    $response->assertRedirect();

    return (string) $response->baseResponse->getSession()->get('success');
}

it('says the article is on the site when saving leaves it there', function () {
    $article = Article::factory()->published()->create(['published_at' => now()->subDay()]);

    expect(saveMessageFor($article, now()->subDay()->format('Y-m-d\TH:i')))
        ->toContain('Εμφανίζεται στη δημόσια σελίδα ειδήσεων.');
});

it('says the article has left the site when a future date takes it off', function () {
    $article = Article::factory()->published()->create(['published_at' => now()->subDays(10)]);

    // The owner typed the date, but nothing told them the article just disappeared.
    expect(saveMessageFor($article, now()->addYear()->format('Y-m-d\TH:i')))
        ->toContain('Θα εμφανιστεί όταν φτάσει η ημερομηνία δημοσίευσης.');
});

it('says the date field was ignored rather than reporting a plain success', function () {
    $article = Article::factory()->published()->create(['published_at' => now()->subDays(10)]);

    expect(saveMessageFor($article, ''))
        ->toContain('Το πεδίο της ημερομηνίας ήταν άδειο, οπότε κρατήθηκε η ημερομηνία που είχε ήδη.');
});

it('does not mention a kept date when there was none to keep', function () {
    $article = Article::factory()->create(['is_visible' => false, 'published_at' => null]);

    expect(saveMessageFor($article, ''))
        ->not->toContain('κρατήθηκε η ημερομηνία')
        ->toContain('Δεν εμφανίζεται δημόσια και δεν έχει ημερομηνία δημοσίευσης.');
});

it('says a hidden article is hidden', function () {
    $article = Article::factory()->create(['is_visible' => false, 'published_at' => now()->subDay()]);

    expect(saveMessageFor($article, now()->subDay()->format('Y-m-d\TH:i')))
        ->toContain('Δεν εμφανίζεται δημόσια, αλλά κρατά την ημερομηνία του.');
});

it('describes what the owner saved with the one definition the dashboard uses', function () {
    // Two places must not be able to disagree about what state an article is in.
    $article = Article::factory()->visible()->create(['published_at' => null]);
    $detail = app(AdminArticleService::class)->stateFor($article)['detail'];

    expect(saveMessageFor($article, ''))->toContain($detail);
});
