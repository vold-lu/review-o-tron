<?php

namespace App\Params\Gitlab;

class ReleaseSource
{
    public function __construct(public readonly string $format, public readonly string $url)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['format'],
            $data['url'],
        );
    }
}