<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Support\ArticleSlug;
use App\Support\YouTubeVideo;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LocalNewsSeeder extends Seeder
{
    /**
     * Seed local-only article data for page building and visual QA.
     */
    public function run(): void
    {
        $articles = [
            [
                'body' => $this->document([
                    $this->heading(2, 'Τι κρατάμε από την ημέρα'),
                    $this->paragraph([
                        $this->text('Η ομάδα μας επέστρεψε από μια γεμάτη αγωνιστική ημέρα στην Καβάλα με '),
                        $this->text('δυνατές εμφανίσεις', ['bold']),
                        $this->text(', καθαρό μυαλό και πολύτιμες εμπειρίες για τη συνέχεια.'),
                    ]),
                    $this->bulletList([
                        'Σταθερή άμυνα σε όλους τους γύρους.',
                        'Καλύτερη διαχείριση της απόστασης.',
                        'Σεβασμός στον αντίπαλο και στο πλάνο του προπονητή.',
                    ]),
                    $this->siteImage('ring-training', 'Αθλητές της ομάδας πριν από αγωνιστική προπόνηση'),
                    $this->paragraph([
                        $this->text('Συγχαρητήρια σε όλα τα παιδιά και στους γονείς που στάθηκαν δίπλα τους με υπομονή και σεβασμό. Δείτε και την ενημέρωση της ομοσπονδίας ', []),
                        $this->text('εδώ', ['link' => 'https://www.pok.gr/']),
                        $this->text('.'),
                    ]),
                ]),
                'cover' => 'ring-training',
                'excerpt' => 'Δυνατές εμφανίσεις, εμπειρίες και καθαρό αγωνιστικό πλάνο για την ομάδα μας στην Καβάλα.',
                'published_at' => '2026-08-28 19:30:00',
                'title' => 'Αγωνιστική ημέρα στην Καβάλα',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Το παιδικό τμήμα δουλεύει κάθε εβδομάδα πάνω στην ισορροπία, την αυτοπεποίθηση και τον σεβασμό στους κανόνες της αίθουσας.',
                    'Μικρά βήματα, σταθερή παρουσία και πολλή χαρά στην προπόνηση. Αυτή είναι η βάση για να αγαπήσουν τα παιδιά τον αθλητισμό.',
                ]),
                'cover' => 'athlete-padwork',
                'excerpt' => 'Το παιδικό τμήμα συνεχίζει με στόχο την τεχνική, την αυτοπεποίθηση και τη χαρά της προπόνησης.',
                'published_at' => '2026-08-23 12:00:00',
                'title' => 'Οι μικροί μαχητές δυναμώνουν',
            ],
            [
                'body' => $this->document([
                    $this->paragraph('Νέα εβδομάδα, νέα προσπάθεια.'),
                    $this->paragraph([
                        $this->emojiImage('https://static.xx.fbcdn.net/images/emoji.php/v9/t8c/1/16/1f94a.png', '🥊'),
                        $this->text(' Σήμερα δουλέψαμε σκιά, στόχους, λακτίσματα και αρκετή φυσική κατάσταση.'),
                    ]),
                    $this->paragraph([
                        $this->text('Μπράβο σε όλους', ['bold']),
                        $this->text(' για την παρουσία και την ενέργεια. Κανείς δεν γεννιέται έτοιμος. Η συνέπεια κάνει τη διαφορά.'),
                    ]),
                    $this->paragraph([
                        $this->emojiImage('https://www.facebook.com/images/emoji.php/v9/t6c/1/16/1f4aa.png', '💪'),
                        $this->text(' Ραντεβού στην επόμενη προπόνηση!'),
                    ]),
                    $this->paragraph('#ΜαχητέςΕλευθερούπολης #Kickboxing #Eleftheroupoli #TrainingDay'),
                ]),
                'cover' => 'hero-kickboxing',
                'excerpt' => 'Ένα κείμενο όπως ανεβαίνει συχνά πρώτα στο Facebook: άμεσο, ανθρώπινο και γεμάτο ρυθμό προπόνησης.',
                'published_at' => '2026-08-18 21:15:00',
                'title' => 'Προπόνηση με ενέργεια και χαμόγελα',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Στο σημερινό μάθημα δουλέψαμε στην απόσταση, στην είσοδο μετά από άμυνα και στην ασφαλή έξοδο από την ανταλλαγή.',
                    'Η τεχνική δεν είναι μόνο κίνηση. Είναι timing, ψυχραιμία και επιλογή της σωστής στιγμής.',
                ]),
                'cover' => 'pad-work',
                'excerpt' => 'Τεχνική προπόνηση με έμφαση στην απόσταση, την άμυνα και την έξοδο από την ανταλλαγή.',
                'published_at' => '2026-08-12 20:00:00',
                'title' => 'Μάθημα τεχνικής στην απόσταση',
            ],
            [
                'body' => $this->document([
                    $this->heading(2, 'Πλάνο τεσσάρων εβδομάδων'),
                    $this->paragraph('Ξεκινά η προετοιμασία για τη νέα αγωνιστική περίοδο με πρόγραμμα που συνδυάζει φυσική κατάσταση, τεχνική και σταδιακή επαφή.'),
                    $this->orderedList([
                        'Βάση φυσικής κατάστασης και κινητικότητα.',
                        'Τεχνική σε στόχους με καθαρή επανάληψη.',
                        'Ελεγχόμενη επαφή σε ζευγάρια.',
                        'Αξιολόγηση και προσαρμογή για κάθε αθλητή.',
                    ]),
                    $this->blockquote('Οι αθλητές θα δουλέψουν σε μικρούς στόχους, ώστε κάθε εβδομάδα να φαίνεται καθαρά η πρόοδος.'),
                ]),
                'cover' => 'athlete-sparring',
                'excerpt' => 'Η νέα αγωνιστική περίοδος ξεκινά με σταδιακή προετοιμασία και καθαρούς εβδομαδιαίους στόχους.',
                'published_at' => '2026-08-05 18:45:00',
                'title' => 'Έναρξη αγωνιστικής προετοιμασίας',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Ο σύλλογος συμμετείχε σε κοινή προπόνηση με αθλητές από την περιοχή, σε ένα περιβάλλον συνεργασίας και σεβασμού.',
                    'Τέτοιες συναντήσεις δίνουν στα παιδιά εικόνες, εμπειρίες και κίνητρο για να επιστρέψουν στην αίθουσα πιο συγκεντρωμένα.',
                ]),
                'cover' => 'coaches-hero',
                'excerpt' => 'Κοινή προπόνηση με συλλόγους της περιοχής και πολύτιμες εμπειρίες για τους αθλητές.',
                'published_at' => '2026-07-29 19:00:00',
                'title' => 'Κοινή προπόνηση συλλόγων',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Η σωστή προθέρμανση προστατεύει το σώμα και προετοιμάζει τον αθλητή για πιο ποιοτική δουλειά.',
                    'Στο μάθημα εξηγήσαμε γιατί η κινητικότητα, οι βασικές ενεργοποιήσεις και η αναπνοή είναι μέρος της τεχνικής και όχι κάτι έξτρα.',
                ]),
                'cover' => 'schedule-rhythm',
                'excerpt' => 'Μάθημα αφιερωμένο στην προθέρμανση, την κινητικότητα και την ασφαλή είσοδο στην ένταση.',
                'published_at' => '2026-07-21 17:30:00',
                'title' => 'Προθέρμανση με σωστή σειρά',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Οι αρχάριοι αθλητές δούλεψαν στις βασικές στάσεις, στην κίνηση των ποδιών και στην πρώτη επαφή με τους στόχους.',
                    'Η αρχή θέλει υπομονή. Κάθε σωστή επανάληψη χτίζει σιγουριά.',
                ]),
                'cover' => 'athlete-bag',
                'excerpt' => 'Βασικές στάσεις, βηματισμοί και πρώτη επαφή με τους στόχους για τους νέους αθλητές.',
                'published_at' => '2026-07-14 20:10:00',
                'title' => 'Βασικές αρχές για αρχάριους',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Η σημερινή προπόνηση ήταν αφιερωμένη στα λακτίσματα με έμφαση στην ισορροπία, την επιστροφή του ποδιού και την καθαρή τεχνική.',
                    'Δεν μετρά μόνο η δύναμη. Μετρά ο έλεγχος.',
                ]),
                'cover' => 'athlete-kick',
                'excerpt' => 'Λακτίσματα με έμφαση στην ισορροπία, την επιστροφή και τον έλεγχο της τεχνικής.',
                'published_at' => '2026-07-07 19:40:00',
                'title' => 'Δουλειά στα λακτίσματα',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Ολοκληρώθηκε ακόμη ένας κύκλος προπονήσεων με πολύ καλή συμμετοχή από τα τμήματα ενηλίκων.',
                    'Η συνέπεια των αθλητών φαίνεται στην αντοχή, στη συγκέντρωση και στην καλύτερη συνεργασία μέσα στην αίθουσα.',
                ]),
                'cover' => 'sparring',
                'excerpt' => 'Ο κύκλος προπονήσεων ενηλίκων έκλεισε με συνέπεια, αντοχή και καλύτερη συνεργασία στην αίθουσα.',
                'published_at' => '2026-06-30 21:00:00',
                'title' => 'Συνέπεια στο τμήμα ενηλίκων',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Στο τέλος της χρονιάς κρατάμε τις στιγμές που οι αθλητές ξεπέρασαν τον εαυτό τους και στάθηκαν δίπλα στους συναθλητές τους.',
                    'Ο σύλλογος μεγαλώνει όταν κάθε παιδί νιώθει ότι ανήκει σε μια ομάδα.',
                ]),
                'cover' => 'about-story',
                'excerpt' => 'Απολογισμός χρονιάς με έμφαση στην προσπάθεια, την ομάδα και τις στιγμές που ξεχώρισαν.',
                'published_at' => '2026-06-22 18:00:00',
                'title' => 'Κλείσιμο χρονιάς με δυνατές στιγμές',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Η άμυνα ξεκινά πριν από το μπλοκ. Ξεκινά από τη θέση του σώματος, το βλέμμα και την απόσταση.',
                    'Οι αθλητές δούλεψαν σε ζευγάρια, με χαμηλή ένταση και καθαρό στόχο την κατανόηση.',
                ]),
                'cover' => 'coach-portrait',
                'excerpt' => 'Προπόνηση άμυνας με έμφαση στη θέση, την απόσταση και την κατανόηση της κίνησης.',
                'published_at' => '2026-06-15 19:20:00',
                'title' => 'Η άμυνα πριν από το μπλοκ',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Η ομάδα συμμετείχε σε φιλικά sparring με ελεγχόμενη ένταση και σαφείς οδηγίες για κάθε γύρο.',
                    'Σκοπός ήταν να δοκιμαστούν επιλογές χωρίς βιασύνη και χωρίς άγχος αποτελέσματος.',
                ]),
                'cover' => 'ring-training',
                'excerpt' => 'Φιλικά sparring με ελεγχόμενη ένταση, καθαρές οδηγίες και χρήσιμα συμπεράσματα.',
                'published_at' => '2026-06-08 20:35:00',
                'title' => 'Φιλικά sparring στην αίθουσα',
            ],
            [
                'body' => $this->document([
                    $this->heading(2, 'Για παιδιά και ενήλικες'),
                    $this->paragraph([
                        $this->text('Οι νέες εγγραφές για τα τμήματα της σχολής συνεχίζονται. Οι ενδιαφερόμενοι μπορούν να επικοινωνήσουν με τον προπονητή για ηλικιακό τμήμα και ώρες.'),
                    ]),
                    $this->bulletList([
                        'Παιδικά τμήματα με έμφαση στη βάση και την πειθαρχία.',
                        'Τμήματα ενηλίκων για αρχάριους και προχωρημένους.',
                        'Πρώτη γνωριμία με προσοχή στον ρυθμό κάθε αθλητή.',
                    ]),
                    $this->paragraph([
                        $this->text('Η πρώτη γνωριμία γίνεται πάντα με '),
                        $this->text('ασφάλεια και καθαρή καθοδήγηση', ['italic']),
                        $this->text('.'),
                    ]),
                ]),
                'cover' => 'schedule-hero',
                'excerpt' => 'Οι εγγραφές συνεχίζονται για παιδικά και ενήλικα τμήματα, με πρώτη γνωριμία στον ρυθμό κάθε αθλητή.',
                'published_at' => '2026-06-01 11:00:00',
                'title' => 'Συνεχίζονται οι νέες εγγραφές',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Η σημερινή προπόνηση είχε στόχο την καλύτερη συνεργασία ανάμεσα σε προπονητή και αθλητή στους στόχους.',
                    'Όταν η επικοινωνία είναι καθαρή, η τεχνική γίνεται πιο γρήγορα κατανοητή.',
                ]),
                'cover' => 'pad-work',
                'excerpt' => 'Δουλειά στους στόχους με έμφαση στη συνεργασία, την ακρίβεια και την καθαρή επικοινωνία.',
                'published_at' => '2026-05-25 18:50:00',
                'title' => 'Στόχοι, ακρίβεια και συνεργασία',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Το πρόγραμμα της εβδομάδας προσαρμόζεται λόγω αγωνιστικών υποχρεώσεων.',
                    'Οι αθλητές θα ενημερωθούν στην ομάδα για τις ώρες κάθε τμήματος.',
                ]),
                'cover' => 'schedule-rhythm',
                'excerpt' => 'Μικρή αλλαγή στο πρόγραμμα της εβδομάδας λόγω αγωνιστικών υποχρεώσεων.',
                'published_at' => '2026-05-18 13:00:00',
                'title' => 'Αλλαγή στο πρόγραμμα της εβδομάδας',
            ],
            [
                'body' => ArticleFactory::tiptapDocument([
                    'Το άρθρο αυτό είναι ορατό ως επιλογή στη διαχείριση, αλλά δεν έχει ώρα δημοσίευσης και δεν μπαίνει στη δημόσια σειρά ειδήσεων.',
                    'Υπάρχει για να δοκιμάζεται καθαρά ο διαχωρισμός ανάμεσα στην ορατότητα και τη δημοσίευση.',
                ]),
                'cover' => 'about-hero',
                'excerpt' => 'Δοκιμαστικό ορατό άρθρο χωρίς χρόνο δημοσίευσης, για έλεγχο της σωστής συμπεριφοράς.',
                'is_visible' => true,
                'published_at' => null,
                'title' => 'Ορατό χωρίς ημερομηνία δημοσίευσης',
            ],
            [
                'body' => $this->document([
                    $this->heading(2, 'Προσχέδιο'),
                    $this->paragraph('Αυτό το άρθρο υπάρχει για να δοκιμάζεται ότι ένα προσχέδιο μένει έξω από τη δημόσια λίστα ειδήσεων.'),
                    $this->paragraph('Μπορεί να έχει σώμα, εικόνες και τίτλο, αλλά όσο δεν είναι ορατό δεν πρέπει να φτάνει σε επισκέπτη.'),
                ]),
                'cover' => 'athlete-bag',
                'excerpt' => 'Κρυφό προσχέδιο για έλεγχο ότι η δημόσια λίστα δεν εμφανίζει μη ορατό περιεχόμενο.',
                'is_visible' => false,
                'published_at' => null,
                'title' => 'Προσχέδιο ανακοίνωσης για αγώνες',
            ],
        ];

        foreach ($articles as $index => $article) {
            $model = Article::factory()->create([
                'body' => $article['body'],
                'cover_image_height' => 1333,
                'cover_image_name' => $article['cover'],
                'cover_image_width' => 2000,
                'excerpt' => $article['excerpt'],
                'is_visible' => $article['is_visible'] ?? true,
                'published_at' => $article['published_at'] === null ? null : Carbon::parse($article['published_at'], 'Europe/Athens'),
                'slug' => ArticleSlug::uniqueForTitle($article['title']),
                'title' => $article['title'],
            ]);

            $this->seedMedia($model, $index);
        }
    }

    private function seedMedia(Article $article, int $index): void
    {
        $imageNames = array_values(array_keys(config('images.static.photos')));
        $firstImage = $imageNames[($index + 3) % count($imageNames)];
        $secondImage = $imageNames[($index + 7) % count($imageNames)];

        $article->images()->createMany([
            [
                'alt_text' => 'Στιγμιότυπο από τη δράση της ομάδας',
                'height' => 1333,
                'image_name' => $firstImage,
                'sort_order' => 0,
                'width' => 2000,
            ],
            [
                'alt_text' => 'Προπονητική στιγμή μέσα στην αίθουσα',
                'height' => 1333,
                'image_name' => $secondImage,
                'sort_order' => 1,
                'width' => 2000,
            ],
        ]);

        if ($index % 4 === 0) {
            $youtubeId = ['M7lc1UVf-VE', 'ysz5S6PUM-U', 'dQw4w9WgXcQ'][$index % 3];

            $article->videos()->create([
                'sort_order' => 0,
                'youtube_id' => YouTubeVideo::idFromUrl("https://www.youtube.com/watch?v={$youtubeId}"),
                'youtube_url' => "https://www.youtube.com/watch?v={$youtubeId}",
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $content
     * @return array<string, mixed>
     */
    private function document(array $content): array
    {
        return [
            'content' => $content,
            'type' => 'doc',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>|string  $content
     * @return array<string, mixed>
     */
    private function paragraph(array|string $content): array
    {
        return [
            'content' => is_string($content) ? [$this->text($content)] : $content,
            'type' => 'paragraph',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function heading(int $level, string $text): array
    {
        return [
            'attrs' => ['level' => $level],
            'content' => [$this->text($text)],
            'type' => 'heading',
        ];
    }

    /**
     * @param  array<int, string>  $items
     * @return array<string, mixed>
     */
    private function bulletList(array $items): array
    {
        return $this->list('bulletList', $items);
    }

    /**
     * @param  array<int, string>  $items
     * @return array<string, mixed>
     */
    private function orderedList(array $items): array
    {
        return $this->list('orderedList', $items);
    }

    /**
     * @param  array<int, string>  $items
     * @return array<string, mixed>
     */
    private function list(string $type, array $items): array
    {
        return [
            'content' => array_map(
                fn (string $item): array => [
                    'content' => [$this->paragraph($item)],
                    'type' => 'listItem',
                ],
                $items,
            ),
            'type' => $type,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blockquote(string $text): array
    {
        return [
            'content' => [$this->paragraph($text)],
            'type' => 'blockquote',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function siteImage(string $imageName, string $alt): array
    {
        return [
            'attrs' => [
                'alt' => $alt,
                'height' => 1333,
                'imageName' => $imageName,
                'width' => 2000,
            ],
            'type' => 'image',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emojiImage(string $src, string $alt): array
    {
        return [
            'attrs' => [
                'alt' => $alt,
                'src' => $src,
            ],
            'type' => 'image',
        ];
    }

    /**
     * @param  array<int|string, string>  $marks
     * @return array<string, mixed>
     */
    private function text(string $text, array $marks = []): array
    {
        $node = [
            'text' => $text,
            'type' => 'text',
        ];

        if ($marks !== []) {
            $node['marks'] = collect($marks)
                ->map(fn (string $value, int|string $key): array => is_string($key)
                    ? ['attrs' => ['href' => $value], 'type' => $key]
                    : ['type' => $value])
                ->values()
                ->all();
        }

        return $node;
    }
}
