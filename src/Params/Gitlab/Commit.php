<?php

namespace App\Params\Gitlab;

class Commit
{
    public function __construct(public readonly string $id,
                                public readonly string $message,
                                public readonly string $title,
                                public readonly string $timestamp,
                                public readonly string $url,
                                public readonly Author $author)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['id'],
            $data['message'],
            $data['title'],
            $data['timestamp'],
            $data['url'],
            Author::fromJson($data['author']),
        );
    }
}