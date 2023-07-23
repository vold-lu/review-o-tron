<?php

namespace App\Params\Gitlab;

class MergeRequestEvent
{
    public function __construct(public readonly string       $object_kind,
                                public readonly string       $event_type,
                                public readonly User         $user,
                                public readonly Project      $project,
                                public readonly MergeRequest $object_attributes,
                                public readonly array        $labels,
                                public readonly array        $changes,
                                public readonly Repository   $repository)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['object_kind'],
            $data['object_type'],
            User::fromJson($data['user']),
            Project::fromJson($data['project']),
            MergeRequest::fromJson($data['object_attributes']),
            $data['labels'],
            $data['changes'],
            Repository::fromJson($data['repository'])
        );
    }
}