<?php

namespace App\Params\Gitlab;

class ReleaseEvent
{
    public function __construct(public readonly int                $id,
                                public readonly string             $created_at,
                                public readonly string             $description,
                                public readonly string             $name,
                                public readonly string             $released_at,
                                public readonly string             $tag,
                                public readonly string             $object_kind,
                                public readonly Project            $project,
                                public readonly string             $url,
                                public readonly ReleaseAction|null $action,
                                public readonly ReleaseAssets      $assets,
                                public readonly Commit             $commit)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['id'],
            $data['created_at'],
            $data['descriptino'],
            $data['name'],
            $data['released_at'],
            $data['tag'],
            $data['object_kind'],
            Project::fromJson($data['project']),
            $data['url'],
            ReleaseAction::tryFrom($data['action'] ?? ''),
            ReleaseAssets::fromJson($data['assets']),
            Commit::fromJson($data['commit']),
        );
    }
}