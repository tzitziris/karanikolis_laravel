<?php

use App\Models\Article;
use App\Models\User;
use App\Support\ArticleBodyContract;
use App\Support\ArticleSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function editorBody(string $text = 'Κείμενο άρθρου για αποθήκευση.'): array
{
    return [
        'content' => [
            [
                'content' => [
                    [
                        'marks' => [
                            ['type' => 'bold'],
                        ],
                        'text' => $text,
                        'type' => 'text',
                    ],
                ],
                'type' => 'paragraph',
            ],
        ],
        'type' => 'doc',
    ];
}

it('serves create and edit article word forms only to authenticated administrators', function () {
    $article = Article::factory()->create();

    $this->get('/admin/articles/create')->assertRedirect('/admin/login');
    $this->get("/admin/articles/{$article->id}/edit")->assertRedirect('/admin/login');

    $this->actingAs(User::factory()->create())
        ->get('/admin/articles/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ArticleForm')
            ->where('mode', 'create')
            ->where('article', null)
            ->where('bodyContract.nodes', ArticleBodyContract::editableNodes())
        );

    $this->actingAs(User::factory()->create())
        ->get("/admin/articles/{$article->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ArticleForm')
            ->where('mode', 'edit')
            ->where('article.id', $article->id)
            ->where('article.slug', $article->slug)
        );
});

it('creates a draft article from JSON body content and never browser HTML', function () {
    $body = editorBody('Το σώμα αποθηκεύεται ως JSON.');

    $this->actingAs(User::factory()->create())
        ->post('/admin/articles', [
            'body' => $body,
            'excerpt' => 'Σύντομη σύνοψη για το άρθρο.',
            'published_at' => '2026-09-12T18:30',
            'title' => 'Νέο άρθρο από τον ιδιοκτήτη',
            'unsafe_html' => '<p>Δεν αποθηκεύεται ποτέ.</p>',
        ])
        ->assertRedirect();

    $article = Article::query()->where('title', 'Νέο άρθρο από τον ιδιοκτήτη')->firstOrFail();

    expect($article->is_visible)->toBeFalse()
        ->and($article->slug)->toBe(ArticleSlug::fromTitle('Νέο άρθρο από τον ιδιοκτήτη'))
        ->and($article->body)->toBe($body)
        ->and(json_encode($article->body, JSON_THROW_ON_ERROR))->not->toContain('<p>Δεν αποθηκεύεται ποτέ.</p>')
        ->and($article->published_at?->format('Y-m-d H:i'))->toBe('2026-09-12 18:30');
});

it('keeps Greek validation errors beside the fields while rejecting unsupported body vocabulary', function () {
    $unsupportedBody = [
        'content' => [
            [
                'attrs' => ['level' => 1],
                'content' => [['text' => 'Λάθος επικεφαλίδα', 'type' => 'text']],
                'type' => 'heading',
            ],
            [
                'type' => 'codeBlock',
                'content' => [['text' => 'Δεν υποστηρίζεται', 'type' => 'text']],
            ],
        ],
        'type' => 'doc',
    ];

    $this->actingAs(User::factory()->create())
        ->from('/admin/articles/create')
        ->post('/admin/articles', [
            'body' => $unsupportedBody,
            'excerpt' => '',
            'published_at' => 'όχι ημερομηνία',
            'title' => '',
        ])
        ->assertRedirect('/admin/articles/create')
        ->assertSessionHasErrors([
            'body',
            'excerpt' => 'Γράψτε τη σύνοψη.',
            'published_at' => 'Η ημερομηνία δημοσίευσης δεν είναι έγκυρη.',
            'title' => 'Γράψτε τον τίτλο.',
        ]);
});

it('rewrites a published article title without changing its slug, publication date or visibility', function () {
    $publishedAt = now()->subDays(20)->seconds(0);
    $article = Article::factory()->published()->create([
        'published_at' => $publishedAt,
        'title' => 'Ο αρχικός τίτλος',
    ]);
    $originalSlug = $article->slug;
    $originalPublishedAt = $article->published_at?->copy();

    $this->actingAs(User::factory()->create())
        ->put("/admin/articles/{$article->id}", [
            'body' => editorBody('Το άρθρο ενημερώθηκε.'),
            'excerpt' => 'Νέα σύνοψη, ίδια διεύθυνση.',
            'published_at' => $article->published_at?->format('Y-m-d\TH:i'),
            'title' => 'Εντελώς νέος τίτλος',
        ])
        ->assertRedirect("/admin/articles/{$article->id}/edit")
        ->assertSessionHas('success', 'Το άρθρο αποθηκεύτηκε.');

    $article->refresh();

    expect($article->title)->toBe('Εντελώς νέος τίτλος')
        ->and($article->slug)->toBe($originalSlug)
        ->and($article->published_at?->equalTo($originalPublishedAt))->toBeTrue()
        ->and($article->is_visible)->toBeTrue();
});

it('keeps a live article public when its editor date field is emptied', function () {
    $publishedAt = now()->subDays(7)->seconds(0);
    $article = Article::factory()->published()->create([
        'excerpt' => 'Παλιά δημόσια σύνοψη.',
        'published_at' => $publishedAt,
        'title' => 'Δημόσιο άρθρο με ημερομηνία',
    ]);
    $originalPublishedAt = $article->published_at?->copy();

    $this->get('/news')
        ->assertInertia(fn (Assert $page) => $page
            ->where('articles.0.title', 'Δημόσιο άρθρο με ημερομηνία')
        );

    $this->actingAs(User::factory()->create())
        ->put("/admin/articles/{$article->id}", [
            'body' => editorBody('Το δημόσιο άρθρο ενημερώθηκε.'),
            'excerpt' => 'Νέα δημόσια σύνοψη.',
            'published_at' => '',
            'title' => 'Δημόσιο άρθρο με αλλαγμένο κείμενο',
        ])
        ->assertRedirect("/admin/articles/{$article->id}/edit")
        ->assertSessionHas('success', 'Το άρθρο αποθηκεύτηκε.');

    $article->refresh();

    expect($article->is_visible)->toBeTrue()
        ->and($article->published_at?->equalTo($originalPublishedAt))->toBeTrue()
        ->and($article->excerpt)->toBe('Νέα δημόσια σύνοψη.');

    $this->get('/news')
        ->assertInertia(fn (Assert $page) => $page
            ->where('articles.0.title', 'Δημόσιο άρθρο με αλλαγμένο κείμενο')
            ->where('articles.0.date', $originalPublishedAt?->locale('el')->translatedFormat('j F Y'))
        );

    $this->actingAs(User::factory()->create())
        ->get("/admin/articles/{$article->id}/edit")
        ->assertInertia(fn (Assert $page) => $page
            ->where('article.publishedAt', $originalPublishedAt?->format('Y-m-d\TH:i'))
        );
});

it('does not erase an existing publication date when editing an undrawn article with an empty date field', function () {
    $publishedAt = now()->subDays(12)->seconds(0);
    $article = Article::factory()->create([
        'is_visible' => false,
        'published_at' => $publishedAt,
        'title' => 'Κρυφό άρθρο με παλιά ημερομηνία',
    ]);
    $originalPublishedAt = $article->published_at?->copy();

    $this->actingAs(User::factory()->create())
        ->put("/admin/articles/{$article->id}", [
            'body' => editorBody('Το κρυφό άρθρο ενημερώθηκε.'),
            'excerpt' => 'Νέα σύνοψη για κρυφό άρθρο.',
            'published_at' => '',
            'title' => 'Κρυφό άρθρο με αλλαγμένο κείμενο',
        ])
        ->assertRedirect("/admin/articles/{$article->id}/edit");

    $article->refresh();

    expect($article->is_visible)->toBeFalse()
        ->and($article->published_at?->equalTo($originalPublishedAt))->toBeTrue()
        ->and($article->title)->toBe('Κρυφό άρθρο με αλλαγμένο κείμενο');
});

it('keeps the editor contract aligned with the server body renderer vocabulary', function () {
    $renderer = File::get(app_path('Services/ArticleBodyRenderer.php'));
    $editor = File::get(resource_path('js/Components/Admin/RichTextEditor.jsx'));

    foreach (ArticleBodyContract::editableNodes() as $node) {
        expect($renderer)->toContain("'{$node}'")
            ->and($editor)->toContain("'{$node}'");
    }

    foreach (ArticleBodyContract::editableMarks() as $mark) {
        expect($renderer)->toContain("'{$mark}'")
            ->and($editor)->toContain("'{$mark}'");
    }

    expect($editor)
        ->not->toContain('@tiptap/extension-image')
        ->not->toContain('youtube')
        ->toContain('EDITOR_BODY_CONTRACT');
});
