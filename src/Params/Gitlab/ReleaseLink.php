<?php

namespace App\Params\Gitlab;

class ReleaseLink
{
    public function __construct(public readonly int    $id,
                                public readonly string $link_type,
                                public readonly string $name,
                                public readonly string $url)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['id'],
            $data['link_type'],
            $data['name'],
            $data['url'],
        );
    }
}