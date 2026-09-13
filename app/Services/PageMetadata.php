<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * What each page tells a search engine and a link preview.
 *
 * Every page had the same title and no description at all, so a link posted to
 * Facebook came out bare. The copy here is the copy on the pages themselves,
 * shortened — nothing is claimed that the site does not say.
 */
class PageMetadata
{
    public const SITE_NAME = 'Μαχητές Ελευθερούπολης';

    /**
     * Route name => [title without the site name, description].
     *
     * @var array<string, array{0: string|null, 1: string}>
     */
    private const PAGES = [
        'home' => [
            null,
            'Σχολή kickboxing στην Ελευθερούπολη Καβάλας. Η δύναμη δεν χαρίζεται· χτίζεται, μία προπόνηση τη φορά. Τμήματα από τα πρώτα βήματα μέχρι την αγωνιστική προετοιμασία.',
        ],
        'coaches' => [
            'Προπονητές & Αθλητές',
            'Η ομάδα της σχολής: προπονητές και αθλητές που προπονούνται με κοινό στόχο και εξελίσσονται μαζί.',
        ],
        'schedule' => [
            'Πρόγραμμα Μαθημάτων',
            'Το εβδομαδιαίο πρόγραμμα των τμημάτων στην Ελευθερούπολη. Από τα πρώτα βήματα μέχρι την αγωνιστική προετοιμασία, βρες το τμήμα που ταιριάζει στο επίπεδο και τον ρυθμό σου.',
        ],
        'news' => [
            'Νέα & Ιστορίες',
            'Νέα από αγώνες, διακρίσεις, σεμινάρια και την καθημερινή προπόνηση της σχολής.',
        ],
        'about' => [
            'Σχετικά με τη Σχολή',
            'Μια σχολή που χτίζει τεχνική, πειθαρχία και χαρακτήρα. Από την πρώτη προπόνηση μέχρι το αγωνιστικό επίπεδο, η εξέλιξη είναι πάντα συλλογική.',
        ],
    ];

    public function __construct(private readonly ShareImage $shareImage) {}

    /**
     * @return array<string, mixed>
     */
    public function forRequest(Request $request): array
    {
        $name = $request->route()?->getName();

        if ($name === null || ! array_key_exists($name, self::PAGES)) {
            return $this->hidden($request);
        }

        [$title, $description] = self::PAGES[$name];
        $page = $name === 'news' ? max(1, (int) $request->query('page', 1)) : 1;

        // Page two of the archive is a different list; giving it the same title
        // and the same canonical would tell a search engine they are one page.
        if ($page > 1) {
            $title = ($title ?? self::SITE_NAME).' · Σελίδα '.$page;
        }

        return [
            'canonical' => $page > 1 ? $request->url().'?page='.$page : $request->url(),
            'description' => $description,
            'image' => $this->shareImage->fallback(),
            'index' => true,
            'publishedTime' => null,
            'siteName' => self::SITE_NAME,
            'title' => $this->fullTitle($title),
            'type' => 'website',
        ];
    }

    /**
     * @param  array<string, mixed>  $article
     * @return array<string, mixed>
     */
    public function forArticle(array $article, Request $request): array
    {
        $image = $this->shareImage->forCover(
            $article['coverImageName'] ?? null,
            $article['coverImageWidth'] ?? null,
            $article['coverImageHeight'] ?? null,
        );

        return [
            'canonical' => $request->url(),
            'description' => trim((string) ($article['excerpt'] ?? '')) ?: self::PAGES['news'][1],
            'image' => $image ?? $this->shareImage->fallback(),
            'index' => true,
            'publishedTime' => $article['publishedAt'] ?? null,
            'siteName' => self::SITE_NAME,
            'title' => $this->fullTitle((string) $article['title']),
            'type' => 'article',
        ];
    }

    /**
     * The admin, the login form and anything that answered 404. None of it
     * belongs in a search result, and none of it should carry a description
     * written for visitors.
     *
     * @return array<string, mixed>
     */
    private function hidden(Request $request): array
    {
        $isAdmin = $request->is('admin') || $request->is('admin/*');

        return [
            'canonical' => null,
            'description' => null,
            'image' => null,
            'index' => false,
            'publishedTime' => null,
            'siteName' => self::SITE_NAME,
            'title' => $this->fullTitle($isAdmin ? 'Διαχείριση' : 'Η σελίδα δεν βρέθηκε'),
            'type' => 'website',
        ];
    }

    private function fullTitle(?string $title): string
    {
        if ($title === null || $title === '' || $title === self::SITE_NAME) {
            return self::SITE_NAME.' · Σχολή Kickboxing στην Ελευθερούπολη Καβάλας';
        }

        return $title.' · '.self::SITE_NAME;
    }
}
