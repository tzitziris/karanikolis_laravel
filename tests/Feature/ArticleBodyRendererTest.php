<?php

use App\Models\Article;
use App\Services\ArticleBodyRenderer;
use Database\Seeders\LocalNewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function renderArticleBody(array $body): string
{
    return app(ArticleBodyRenderer::class)->render($body);
}

it('renders known TipTap nodes into constructed HTML', function () {
    $html = renderArticleBody([
        'content' => [
            [
                'attrs' => ['level' => 2, 'textAlign' => 'center'],
                'content' => [['text' => 'Πρόγραμμα ημέρας', 'type' => 'text']],
                'type' => 'heading',
            ],
            [
                'content' => [
                    ['text' => 'Πρώτα ', 'type' => 'text'],
                    ['marks' => [['type' => 'bold']], 'text' => 'τεχνική', 'type' => 'text'],
                    ['text' => ' και μετά ', 'type' => 'text'],
                    ['marks' => [['type' => 'italic']], 'text' => 'ρυθμός', 'type' => 'text'],
                    ['type' => 'hardBreak'],
                    [
                        'marks' => [
                            [
                                'attrs' => ['href' => 'https://www.pok.gr/'],
                                'type' => 'link',
                            ],
                        ],
                        'text' => 'Ομοσπονδία',
                        'type' => 'text',
                    ],
                ],
                'type' => 'paragraph',
            ],
            [
                'content' => [
                    ['content' => [['content' => [['text' => 'Στόχοι', 'type' => 'text']], 'type' => 'paragraph']], 'type' => 'listItem'],
                ],
                'type' => 'bulletList',
            ],
            [
                'content' => [
                    ['content' => [['content' => [['text' => 'Προθέρμανση', 'type' => 'text']], 'type' => 'paragraph']], 'type' => 'listItem'],
                ],
                'type' => 'orderedList',
            ],
            [
                'content' => [
                    ['content' => [['text' => 'Η συνέπεια κάνει τη διαφορά.', 'type' => 'text']], 'type' => 'paragraph'],
                ],
                'type' => 'blockquote',
            ],
            [
                'attrs' => [
                    'alt' => 'Αθλητές στην προπόνηση',
                    'imageName' => 'ring-training',
                ],
                'type' => 'image',
            ],
        ],
        'type' => 'doc',
    ]);

    expect($html)->toContain('<h2 style="text-align:center">Πρόγραμμα ημέρας</h2>')
        ->and($html)->toContain('<strong>τεχνική</strong>')
        ->and($html)->toContain('<em>ρυθμός</em>')
        ->and($html)->toContain('<br>')
        ->and($html)->toContain('<a href="https://www.pok.gr/">Ομοσπονδία</a>')
        ->and($html)->toContain('<ul><li><p>Στόχοι</p></li></ul>')
        ->and($html)->toContain('<ol><li><p>Προθέρμανση</p></li></ol>')
        ->and($html)->toContain('<blockquote><p>Η συνέπεια κάνει τη διαφορά.</p></blockquote>')
        ->and($html)->toContain('class="article-inline-image"')
        ->and($html)->toContain('/images/static/ring-training-')
        ->and($html)->toContain('.webp')
        ->and($html)->not->toContain('.jpg');
});

it('keeps unknown structure out while preserving its words', function () {
    $html = renderArticleBody([
        'content' => [
            [
                'attrs' => ['onclick' => 'alert(1)'],
                'content' => [
                    ['text' => 'Κείμενο από άγνωστο κόμβο', 'type' => 'text'],
                ],
                'type' => 'unknownWidget',
            ],
        ],
        'type' => 'doc',
    ]);

    expect($html)->toBe('Κείμενο από άγνωστο κόμβο')
        ->and($html)->not->toContain('unknownWidget')
        ->and($html)->not->toContain('onclick');
});

it('allows only http and https links to reach visitors', function (string $href) {
    $html = renderArticleBody([
        'content' => [
            [
                'content' => [
                    [
                        'marks' => [
                            [
                                'attrs' => ['href' => $href],
                                'type' => 'link',
                            ],
                        ],
                        'text' => 'επικίνδυνος σύνδεσμος',
                        'type' => 'text',
                    ],
                ],
                'type' => 'paragraph',
            ],
        ],
        'type' => 'doc',
    ]);

    expect($html)->toBe('<p>επικίνδυνος σύνδεσμος</p>')
        ->and($html)->not->toContain('<a')
        ->and($html)->not->toContain('href=')
        ->and($html)->not->toContain('javascript:')
        ->and($html)->not->toContain('data:');
})->with([
    'javascript URL' => 'javascript:alert(document.domain)',
    'mixed case javascript URL' => 'JaVaScRiPt:alert(1)',
    'data URL' => 'data:text/html,<script>alert(1)</script>',
    'protocol relative URL' => '//example.com/path',
    'relative URL' => '/admin',
    'control character URL' => "https://example.com/\njavascript:alert(1)",
]);

it('escapes safe link addresses after deciding they are web URLs', function () {
    $html = renderArticleBody([
        'content' => [
            [
                'content' => [
                    [
                        'marks' => [
                            [
                                'attrs' => ['href' => 'https://example.com/?q="δοκιμή"&page=1'],
                                'type' => 'link',
                            ],
                        ],
                        'text' => 'ασφαλής σύνδεσμος',
                        'type' => 'text',
                    ],
                ],
                'type' => 'paragraph',
            ],
        ],
        'type' => 'doc',
    ]);

    expect($html)->toBe('<p><a href="https://example.com/?q=&quot;δοκιμή&quot;&amp;page=1">ασφαλής σύνδεσμος</a></p>');
});

it('turns Facebook emoji images into characters and blocks other pasted remote images', function () {
    $html = renderArticleBody([
        'content' => [
            [
                'content' => [
                    [
                        'attrs' => [
                            'alt' => '🥊',
                            'src' => 'https://static.xx.fbcdn.net/images/emoji.php/v9/t8c/1/16/1f94a.png',
                        ],
                        'type' => 'image',
                    ],
                    ['text' => ' Προπόνηση ', 'type' => 'text'],
                    [
                        'attrs' => [
                            'alt' => '💪',
                            'src' => 'https://www.facebook.com/images/emoji.php/v9/t6c/1/16/1f4aa.png',
                        ],
                        'type' => 'image',
                    ],
                ],
                'type' => 'paragraph',
            ],
            [
                'attrs' => [
                    'alt' => 'Ξένη εικόνα',
                    'src' => 'https://example.com/photo.jpg',
                ],
                'type' => 'image',
            ],
        ],
        'type' => 'doc',
    ]);

    expect($html)->toContain('<span class="article-emoji" role="img" aria-label="🥊">🥊</span>')
        ->and($html)->toContain('<span class="article-emoji" role="img" aria-label="💪">💪</span>')
        ->and($html)->toContain('<span class="article-removed-image">Εικόνα: Ξένη εικόνα</span>')
        ->and($html)->not->toContain('<img')
        ->and($html)->not->toContain('fbcdn.net')
        ->and($html)->not->toContain('facebook.com')
        ->and($html)->not->toContain('example.com/photo.jpg');
});

it('does not render youtube nodes inside article body HTML', function () {
    $html = renderArticleBody([
        'content' => [
            [
                'attrs' => ['src' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE'],
                'type' => 'youtube',
            ],
        ],
        'type' => 'doc',
    ]);

    expect($html)->toBe('');
});

it('renders the seeded Facebook paste article without remote image fetches', function () {
    $this->seed(LocalNewsSeeder::class);

    $article = Article::query()
        ->where('title', 'Προπόνηση με ενέργεια και χαμόγελα')
        ->firstOrFail();
    $html = app(ArticleBodyRenderer::class)->render($article->body);

    expect($html)->toBe('<p>Νέα εβδομάδα, νέα προσπάθεια.</p><p><span class="article-emoji" role="img" aria-label="🥊">🥊</span> Σήμερα δουλέψαμε σκιά, στόχους, λακτίσματα και αρκετή φυσική κατάσταση.</p><p><strong>Μπράβο σε όλους</strong> για την παρουσία και την ενέργεια. Κανείς δεν γεννιέται έτοιμος. Η συνέπεια κάνει τη διαφορά.</p><p><span class="article-emoji" role="img" aria-label="💪">💪</span> Ραντεβού στην επόμενη προπόνηση!</p><p>#ΜαχητέςΕλευθερούπολης #Kickboxing #Eleftheroupoli #TrainingDay</p>')
        ->and($html)->not->toContain('<img')
        ->and($html)->not->toContain('fbcdn.net')
        ->and($html)->not->toContain('facebook.com');
});
