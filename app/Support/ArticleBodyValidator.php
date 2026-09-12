<?php

namespace App\Support;

use Illuminate\Support\Arr;

class ArticleBodyValidator
{
    /**
     * @param  array<string, mixed>  $body
     * @return array<int, string>
     */
    public function errors(array $body): array
    {
        $errors = [];

        $this->validateNode($body, '$', $errors);

        if (($body['type'] ?? null) !== 'doc') {
            $errors[] = 'Το σώμα του άρθρου δεν έχει έγκυρη δομή.';
        }

        if (! $this->hasText($body)) {
            $errors[] = 'Γράψτε το κείμενο του άρθρου.';
        }

        return array_values(array_unique($errors));
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array<int, string>  $errors
     */
    private function validateNode(array $node, string $path, array &$errors): void
    {
        $type = is_string($node['type'] ?? null) ? $node['type'] : '';

        if (! in_array($type, ArticleBodyContract::editableNodes(), true)) {
            $errors[] = "Το κείμενο περιέχει μορφοποίηση που δεν υποστηρίζεται ({$type}).";

            return;
        }

        if ($type === 'text' && ! is_string($node['text'] ?? null)) {
            $errors[] = 'Το κείμενο περιέχει άκυρο τμήμα.';
        }

        if ($type === 'heading') {
            $level = (int) Arr::get($node, 'attrs.level', 2);

            if (! in_array($level, ArticleBodyContract::headingLevels(), true)) {
                $errors[] = 'Οι επικεφαλίδες μπορούν να είναι μόνο επίπεδο 2 ή 3.';
            }
        }

        $textAlign = Arr::get($node, 'attrs.textAlign');

        if ($textAlign !== null && ! in_array($textAlign, ArticleBodyContract::alignments(), true)) {
            $errors[] = 'Η στοίχιση του κειμένου δεν είναι έγκυρη.';
        }

        $marks = is_array($node['marks'] ?? null) ? $node['marks'] : [];

        foreach ($marks as $mark) {
            if (! is_array($mark)) {
                $errors[] = 'Το κείμενο περιέχει άκυρη μορφοποίηση.';

                continue;
            }

            $markType = is_string($mark['type'] ?? null) ? $mark['type'] : '';

            if (! in_array($markType, ArticleBodyContract::editableMarks(), true)) {
                $errors[] = "Το κείμενο περιέχει μορφοποίηση που δεν υποστηρίζεται ({$markType}).";
            }

            if ($markType === 'link' && ! $this->isHttpUrl(Arr::get($mark, 'attrs.href'))) {
                $errors[] = 'Οι σύνδεσμοι πρέπει να ξεκινούν με http ή https.';
            }
        }

        $children = is_array($node['content'] ?? null) ? $node['content'] : [];

        foreach ($children as $index => $child) {
            if (! is_array($child)) {
                $errors[] = 'Το κείμενο περιέχει άκυρο τμήμα.';

                continue;
            }

            $this->validateNode($child, "{$path}.content.{$index}", $errors);
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function hasText(array $node): bool
    {
        if (($node['type'] ?? null) === 'text' && trim((string) ($node['text'] ?? '')) !== '') {
            return true;
        }

        $children = is_array($node['content'] ?? null) ? $node['content'] : [];

        foreach ($children as $child) {
            if (is_array($child) && $this->hasText($child)) {
                return true;
            }
        }

        return false;
    }

    private function isHttpUrl(mixed $value): bool
    {
        if (! is_string($value) || preg_match('/[\x00-\x20\x7f]/', $value) === 1) {
            return false;
        }

        $parts = parse_url($value);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        return is_array($parts) && isset($parts['host']) && in_array($scheme, ['http', 'https'], true);
    }
}
