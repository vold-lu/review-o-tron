<?php

namespace App\Params\Event\Release;

use App\Params\Gitlab\Project;
use App\Params\Gitlab\ReleaseAssets;
use App\Params\Gitlab\ReleaseEvent;

class ReleaseCreated
{
    public static ReleaseEventName $NAME = ReleaseEventName::CREATED;

    public function __construct(public readonly int           $id,
                                public readonly string        $created_at,
                                public readonly string        $description,
                                public readonly string        $name,
                                public readonly string        $released_at,
                                public readonly string        $tag,
                                public readonly string        $object_kind,
                                public readonly Project       $project,
                                public readonly string        $url,
                                public readonly ReleaseAssets $assets)
    {
    }

    public static function fromEvent(ReleaseEvent $releaseEvent): self
    {
        return new self(
            $releaseEvent->id,
            $releaseEvent->created_at,
            $releaseEvent->description,
            $releaseEvent->name,
            $releaseEvent->released_at,
            $releaseEvent->tag,
            $releaseEvent->object_kind,
            $releaseEvent->project,
            $releaseEvent->url,
            $releaseEvent->assets
        );
    }
}