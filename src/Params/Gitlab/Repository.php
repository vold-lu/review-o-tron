<?php

namespace App\Params\Gitlab;

class Repository
{
    public function __construct(public readonly string  $name,
                                public readonly string  $url,
                                public readonly ?string $description,
                                public readonly string  $homepage)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['name'],
            $data['url'],
            $data['description'],
            $data['homepage'],
        );
    }
}