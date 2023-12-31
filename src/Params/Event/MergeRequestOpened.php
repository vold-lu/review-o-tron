<?php

namespace App\Params\Event;

use App\Params\Gitlab\MergeRequest;
use App\Params\Gitlab\MergeRequestEvent;
use App\Params\Gitlab\Project;
use App\Params\Gitlab\User;


/**
 * @property User[] $assignees
 * @property User[] $reviewers
 */
class MergeRequestOpened
{
    public static EventName $NAME = EventName::OPENED;

    public function __construct(public readonly User         $user,
                                public readonly Project      $project,
                                public readonly MergeRequest $mergeRequest,
                                public readonly array        $assignees,
                                public readonly array        $reviewers)
    {
    }

    public static function fromEvent(MergeRequestEvent $event): self
    {
        return new self(
            $event->user,
            $event->project,
            $event->object_attributes,
            $event->assignees,
            $event->reviewers
        );
    }
}