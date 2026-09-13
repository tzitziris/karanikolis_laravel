<?php

use App\Models\Article;
use App\Models\ArticleVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function addVideoTo(Article $article, string $url)
{
    return test()
        ->actingAs(User::factory()->create())
        ->post("/admin/articles/{$article->id}/videos", ['youtube_url' => $url]);
}

function makeVideo(Article $article, string $youtubeId): ArticleVideo
{
    return ArticleVideo::create([
        'article_id' => $article->id,
        'sort_order' => (int) (ArticleVideo::where('article_id', $article->id)->max('sort_order') ?? -1) + 1,
        'youtube_url' => "https://youtu.be/{$youtubeId}",
    ]);
}

/**
 * @return array<int, string>
 */
function videoIdsInOrder(Article $article): array
{
    return DB::table('article_videos')
        ->where('article_id', $article->id)
        ->orderBy('sort_order')
        ->pluck('youtube_id')
        ->all();
}

it('reads the video id out of every YouTube address shape the owner might copy', function (string $url) {
    $article = Article::factory()->create();

    addVideoTo($article, $url)
        ->assertRedirect()
        ->assertSessionHas('success', 'Το βίντεο προστέθηκε στο άρθρο.');

    expect(videoIdsInOrder($article))->toBe(['dQw4w9WgXcQ']);
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ',
    'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'https://www.youtube.com/shorts/dQw4w9WgXcQ',
    'https://m.youtube.com/watch?v=dQw4w9WgXcQ&t=30s',
]);

it('refuses an address it cannot read a video out of, in Greek, storing nothing', function (string $url) {
    $article = Article::factory()->create();

    addVideoTo($article, $url)
        ->assertRedirect()
        ->assertSessionHasErrors([
            'youtube_url' => 'Ο σύνδεσμος δεν είναι βίντεο YouTube. Αντιγράψτε τη διεύθυνση από το YouTube.',
        ]);

    // A stored row with an empty youtube_id would render as nothing at all.
    expect(videoIdsInOrder($article))->toBe([]);
})->with([
    'https://vimeo.com/76979871',
    'https://www.youtube.com/',
    'https://www.youtube.com/watch?v=short',
    'https://example.com/watch?v=dQw4w9WgXcQ',
    'όχι σύνδεσμος',
]);

it('asks for an address when the field is empty', function () {
    $article = Article::factory()->create();

    addVideoTo($article, '')
        ->assertSessionHasErrors(['youtube_url' => 'Επικολλήστε τον σύνδεσμο του βίντεο.']);
});

it('keeps the order of the remaining videos when one is removed from the middle', function () {
    $article = Article::factory()->create();

    foreach (['aaaaaaaaaaa', 'bbbbbbbbbbb', 'ccccccccccc', 'ddddddddddd'] as $id) {
        addVideoTo($article, "https://youtu.be/{$id}");
    }

    expect(videoIdsInOrder($article))->toBe(['aaaaaaaaaaa', 'bbbbbbbbbbb', 'ccccccccccc', 'ddddddddddd']);

    $second = ArticleVideo::where('article_id', $article->id)->orderBy('sort_order')->skip(1)->first();

    $this->actingAs(User::factory()->create())
        ->delete("/admin/articles/{$article->id}/videos/{$second->id}")
        ->assertRedirect()
        ->assertSessionHas('success', 'Το βίντεο αφαιρέθηκε από το άρθρο.');

    expect(videoIdsInOrder($article))->toBe(['aaaaaaaaaaa', 'ccccccccccc', 'ddddddddddd'])
        ->and(DB::table('article_videos')->where('article_id', $article->id)->orderBy('sort_order')->pluck('sort_order')->all())
        ->toBe([0, 1, 2]);
});

it('saves an order the owner rearranged', function () {
    $article = Article::factory()->create();

    foreach (['aaaaaaaaaaa', 'bbbbbbbbbbb', 'ccccccccccc'] as $id) {
        addVideoTo($article, "https://youtu.be/{$id}");
    }

    $ids = ArticleVideo::where('article_id', $article->id)->orderBy('sort_order')->pluck('id')->all();

    $this->actingAs(User::factory()->create())
        ->put("/admin/articles/{$article->id}/videos", [
            'videos' => [['id' => $ids[2]], ['id' => $ids[0]], ['id' => $ids[1]]],
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Η σειρά των βίντεο αποθηκεύτηκε.');

    expect(videoIdsInOrder($article))->toBe(['ccccccccccc', 'aaaaaaaaaaa', 'bbbbbbbbbbb']);
});

it('refuses an order that does not describe exactly this article\'s videos', function () {
    $article = Article::factory()->create();
    $other = Article::factory()->create();
    addVideoTo($article, 'https://youtu.be/aaaaaaaaaaa');
    addVideoTo($article, 'https://youtu.be/bbbbbbbbbbb');
    addVideoTo($other, 'https://youtu.be/ccccccccccc');

    $mine = ArticleVideo::where('article_id', $article->id)->pluck('id')->all();
    $theirs = ArticleVideo::where('article_id', $other->id)->value('id');

    $this->actingAs(User::factory()->create())
        ->put("/admin/articles/{$article->id}/videos", [
            'videos' => [['id' => $mine[0]], ['id' => $theirs]],
        ])
        ->assertSessionHasErrors(['videos' => 'Η σειρά των βίντεο δεν είναι έγκυρη.']);

    expect(videoIdsInOrder($article))->toBe(['aaaaaaaaaaa', 'bbbbbbbbbbb']);
});

it('will not let one article delete another article\'s video', function () {
    $article = Article::factory()->create();
    $other = Article::factory()->create();
    addVideoTo($other, 'https://youtu.be/ccccccccccc');
    $theirs = ArticleVideo::where('article_id', $other->id)->value('id');

    $this->actingAs(User::factory()->create())
        ->delete("/admin/articles/{$article->id}/videos/{$theirs}")
        ->assertNotFound();

    expect(videoIdsInOrder($other))->toBe(['ccccccccccc']);
});

it('hands the editing screen the videos it already has', function () {
    $article = Article::factory()->create();
    addVideoTo($article, 'https://youtu.be/dQw4w9WgXcQ');

    $this->actingAs(User::factory()->create())
        ->get("/admin/articles/{$article->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ArticleForm')
            ->has('article.videos', 1)
            ->where('article.videos.0.youtubeId', 'dQw4w9WgXcQ')
            ->where('article.videos.0.sortOrder', 0)
        );
});

it('changes nothing when a video address is reached by GET', function (string $uri) {
    $article = Article::factory()->create();
    $video = makeVideo($article, 'aaaaaaaaaaa');

    // Seventeen links on this site prefetch on hover; a GET that mutates would fire on its own.
    $this->actingAs(User::factory()->create())
        ->get(str_replace(['{article}', '{video}'], [$article->id, $video->id], $uri));

    expect(videoIdsInOrder($article))->toBe(['aaaaaaaaaaa']);
})->with([
    '/admin/articles/{article}/videos',
    '/admin/articles/{article}/videos/{video}',
]);

it('sends an unauthenticated video request to the sign-in page before it changes anything', function (string $method, string $uri) {
    $article = Article::factory()->create();
    $video = makeVideo($article, 'aaaaaaaaaaa');

    $this->{$method}(str_replace(['{article}', '{video}'], [$article->id, $video->id], $uri))
        ->assertRedirect('/admin/login');

    expect(videoIdsInOrder($article))->toBe(['aaaaaaaaaaa']);
})->with([
    ['post', '/admin/articles/{article}/videos'],
    ['put', '/admin/articles/{article}/videos'],
    ['delete', '/admin/articles/{article}/videos/{video}'],
]);
