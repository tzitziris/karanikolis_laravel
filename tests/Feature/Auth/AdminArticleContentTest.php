<?php

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleBodyRenderer;
use App\Support\ArticleBodyContract;
use App\Support\ArticleSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\Process\Process;

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
        ->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'Το άρθρο αποθηκεύτηκε.')
            && str_contains($message, 'Εμφανίζεται στη δημόσια σελίδα ειδήσεων.'));

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
        ->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'κρατήθηκε η ημερομηνία που είχε ήδη'));

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

it('renders every node and mark the contract declares, as the element it means', function () {
    // This used to grep ArticleBodyRenderer.php for the word 'blockquote'. The
    // name being present in the source says nothing about what a visitor gets;
    // these are the elements the renderer must actually produce.
    $elements = [
        'blockquote' => ['<blockquote', ['type' => 'blockquote', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Απόσπασμα']]]]]],
        'bulletList' => ['<ul', ['type' => 'bulletList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Στοιχείο']]]]]]]],
        'hardBreak' => ['<br', ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Πριν'], ['type' => 'hardBreak'], ['type' => 'text', 'text' => 'Μετά']]]],
        'heading' => ['<h2', ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Τίτλος']]]],
        'listItem' => ['<li', ['type' => 'bulletList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Στοιχείο']]]]]]]],
        'orderedList' => ['<ol', ['type' => 'orderedList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Πρώτο']]]]]]]],
        'paragraph' => ['<p', ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Παράγραφος']]]],
    ];

    // doc and text are the document itself and its words; there is no element
    // for them and nothing optional about them.
    expect(array_diff(ArticleBodyContract::editableNodes(), [...array_keys($elements), 'doc', 'text']))
        ->toBe([], 'The contract declares a node this test does not render.');

    $renderer = app(ArticleBodyRenderer::class);

    foreach ($elements as $node => [$tag, $content]) {
        $html = $renderer->render(['type' => 'doc', 'content' => [$content]]);

        expect(str_contains($html, $tag))
            ->toBeTrue("The renderer does not produce {$tag} for the {$node} the editor can write. It gave: {$html}");
    }

    $marks = [
        'bold' => '<strong',
        'italic' => '<em',
        'link' => '<a ',
    ];

    expect(array_diff(ArticleBodyContract::editableMarks(), array_keys($marks)))
        ->toBe([], 'The contract declares a mark this test does not render.');

    foreach ($marks as $mark => $tag) {
        $attrs = $mark === 'link' ? ['href' => 'https://example.com'] : [];

        $html = $renderer->render(['type' => 'doc', 'content' => [[
            'type' => 'paragraph',
            'content' => [['type' => 'text', 'text' => 'Σημειωμένο', 'marks' => [['type' => $mark, 'attrs' => $attrs]]]],
        ]]]);

        expect(str_contains($html, $tag))
            ->toBeTrue("The renderer drops the {$mark} mark the editor can write. It gave: {$html}");
    }
});

it('gives the editor exactly the vocabulary the server declares, and nothing of its own', function () {
    $schema = inspectEditorSchema();

    expect($schema['nodes'])->toBe(collect(ArticleBodyContract::editableNodes())->sort()->values()->all())
        ->and($schema['marks'])->toBe(collect(ArticleBodyContract::editableMarks())->sort()->values()->all())
        ->and($schema['headingLevels'])->toBe(ArticleBodyContract::headingLevels())
        ->and($schema['canToggleHeadingOne'])->toBeFalse()
        ->and($schema['canToggleHeadingTwo'])->toBeTrue()
        ->and($schema['hasToggleUnderlineCommand'])->toBeFalse();
});

it('takes its heading levels and alignments from the contract it is handed', function () {
    // These two the editor really is built from, so a narrower contract must
    // produce a narrower editor. If it did not, the server and the toolbar could
    // drift apart without anything saying so.
    $narrowed = inspectEditorSchema([
        'alignments' => ['left'],
        'headingLevels' => [3],
        'marks' => ArticleBodyContract::editableMarks(),
        'nodes' => ArticleBodyContract::editableNodes(),
    ]);

    expect($narrowed['headingLevels'])->toBe([3])
        ->and($narrowed['canToggleHeadingTwo'])->toBeFalse()
        ->and($narrowed['matchesContract'])->toBeTrue();
});

it('refuses to open rather than offer a vocabulary the server will not render', function () {
    // The node and mark lists are not what the editor is assembled from — the
    // StarterKit is configured by hand — so the protection is that a mismatch is
    // detected. When it is, RichTextEditor shows a Greek warning instead of the
    // toolbar, rather than letting the owner write something that would vanish.
    $mismatched = inspectEditorSchema([
        'alignments' => ArticleBodyContract::alignments(),
        'headingLevels' => ArticleBodyContract::headingLevels(),
        'marks' => ['bold'],
        'nodes' => ['doc', 'paragraph', 'text'],
    ]);

    expect($mismatched['matchesContract'])->toBeFalse();

    expect(File::get(resource_path('js/Components/Admin/RichTextEditor.jsx')))
        ->toContain('Το πρόγραμμα επεξεργασίας δεν συμφωνεί');
});

/**
 * @return array{
 *     canToggleHeadingOne: bool,
 *     canToggleHeadingTwo: bool,
 *     hasToggleUnderlineCommand: bool,
 *     headingLevels: array<int, int>,
 *     matchesContract: bool,
 *     marks: array<int, string>,
 *     nodes: array<int, string>
 * }
 */
function inspectEditorSchema(?array $contract = null): array
{
    $contract ??= [
        'alignments' => ArticleBodyContract::alignments(),
        'headingLevels' => ArticleBodyContract::headingLevels(),
        'marks' => ArticleBodyContract::editableMarks(),
        'nodes' => ArticleBodyContract::editableNodes(),
    ];

    $script = <<<'JS'
        import { Editor } from '@tiptap/core';
        import { createArticleEditorExtensions, schemaMatchesContract, schemaVocabulary } from './resources/js/Components/Admin/articleEditorSchema.js';

        const contract = JSON.parse(process.argv[1]);
        const editor = new Editor({
            content: {
                type: 'doc',
                content: [
                    {
                        type: 'paragraph',
                        content: [{ type: 'text', text: 'Έλεγχος editor' }],
                    },
                ],
            },
            extensions: createArticleEditorExtensions(contract),
        });
        const heading = editor.extensionManager.extensions.find((extension) => extension.name === 'heading');
        const matchesContract = schemaMatchesContract(editor, contract);
        const canToggleHeadingOne = editor.commands.toggleHeading({ level: 1 });
        const canToggleHeadingTwo = editor.commands.toggleHeading({ level: 2 });

        process.stdout.write(JSON.stringify({
            ...schemaVocabulary(editor),
            canToggleHeadingOne,
            canToggleHeadingTwo,
            hasToggleUnderlineCommand: typeof editor.commands.toggleUnderline === 'function',
            headingLevels: heading.options.levels,
            matchesContract,
        }));
        editor.destroy();
    JS;

    $process = new Process(['node', '--input-type=module', '-e', $script, json_encode($contract, JSON_THROW_ON_ERROR)], base_path());
    $process->mustRun();

    /** @var array<string, mixed> $schema */
    $schema = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);

    return $schema;
}
