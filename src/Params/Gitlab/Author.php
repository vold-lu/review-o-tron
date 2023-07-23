<?php

namespace App\Params\Gitlab;

class Author
{
    public function __construct(public readonly string $name,
                                public readonly string $email)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['name'],
            $data['email']
        );
    }
}