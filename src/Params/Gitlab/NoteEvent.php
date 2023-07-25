<?php

namespace App\Params\Gitlab;

// TODO: map all fields?
class NoteEvent
{
    public function __construct(public readonly string       $object_kind,
                                public readonly string       $event_type,
                                public readonly User         $user,
                                public readonly Project      $project,
                                public readonly MergeRequest $merge_request,
                                public readonly Repository   $repository)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['object_kind'],
            $data['event_type'],
            User::fromJson($data['user']),
            Project::fromJson($data['project']),
            MergeRequest::fromJson($data['merge_request']),
            Repository::fromJson($data['repository'])
        );
    }
}