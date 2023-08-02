<?php

namespace App\Params\Gitlab;

/**
 * @property ReleaseLink[] $links
 * @property ReleaseSource[] $sources
 */
class ReleaseAssets
{
    public function __construct(public readonly int   $count,
                                public readonly array $links,
                                public readonly array $sources)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['count'],
            array_map(fn($link) => ReleaseLink::fromJson($link), $data['links']),
            array_map(fn($source) => ReleaseSource::fromJson($source), $data['sources'])
        );
    }
}