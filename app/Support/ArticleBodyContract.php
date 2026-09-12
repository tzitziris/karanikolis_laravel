<?php

namespace App\Support;

class ArticleBodyContract
{
    /**
     * @return array<int, string>
     */
    public static function editableNodes(): array
    {
        return [
            'blockquote',
            'bulletList',
            'doc',
            'hardBreak',
            'heading',
            'listItem',
            'orderedList',
            'paragraph',
            'text',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function editableMarks(): array
    {
        return [
            'bold',
            'italic',
            'link',
        ];
    }

    /**
     * @return array<int, int>
     */
    public static function headingLevels(): array
    {
        return [2, 3];
    }

    /**
     * @return array<int, string>
     */
    public static function alignments(): array
    {
        return ['left', 'center', 'right', 'justify'];
    }
}
