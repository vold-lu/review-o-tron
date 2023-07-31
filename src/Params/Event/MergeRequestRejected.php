<?php

namespace App\Params\Event;

use App\Params\Gitlab\MergeRequest;
use App\Params\Gitlab\NoteEvent;
use App\Params\Gitlab\Project;
use App\Params\Gitlab\User;

class MergeRequestRejected
{
    public static EventName $NAME = EventName::REJECTED;

    public function __construct(public readonly User         $user,
                                public readonly Project      $project,
                                public readonly MergeRequest $mergeRequest)
    {
    }

    public static function fromEvent(NoteEvent $event): self
    {
        return new self(
            $event->user,
            $event->project,
            $event->merge_request
        );
    }
}