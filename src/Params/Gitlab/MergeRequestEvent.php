<?php

namespace App\Params\Gitlab;

/**
 * @property User[] $assignees
 * @property User[] $reviewers
 */
class MergeRequestEvent
{
    public function __construct(public readonly string       $object_kind,
                                public readonly string       $event_type,
                                public readonly User         $user,
                                public readonly Project      $project,
                                public readonly MergeRequest $object_attributes,
                                public readonly array        $labels,
                                public readonly array        $changes,
                                public readonly Repository   $repository,
                                public readonly array        $assignees,
                                public readonly array        $reviewers)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['object_kind'],
            $data['event_type'],
            User::fromJson($data['user']),
            Project::fromJson($data['project']),
            MergeRequest::fromJson($data['object_attributes']),
            $data['labels'],
            $data['changes'],
            Repository::fromJson($data['repository']),
            array_map(fn($user) => User::fromJson($user), $data['assignees'] ?? []),
            array_map(fn($user) => User::fromJson($user), $data['reviewers'] ?? [])
        );
    }
}