<?php

namespace App\Params\Event;

use App\Params\Gitlab\MergeRequest;
use App\Params\Gitlab\MergeRequestEvent;
use App\Params\Gitlab\Project;
use App\Params\Gitlab\User;

class MergeRequestOpened
{
    public function __construct(public readonly User         $user,
                                public readonly Project      $project,
                                public readonly MergeRequest $mergeRequest)
    {
    }

    public static function fromEvent(MergeRequestEvent $event): self
    {
        return new self(
            $event->user,
            $event->project,
            $event->object_attributes
        );
    }
}