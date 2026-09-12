<?php

namespace App\Exceptions;

use RuntimeException;

class ArticleImageUploadException extends RuntimeException
{
    public static function ownerMessage(string $message): self
    {
        return new self($message);
    }
}
