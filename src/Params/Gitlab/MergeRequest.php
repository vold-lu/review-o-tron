<?php

namespace App\Params\Gitlab;

class MergeRequest
{
    public function __construct(public readonly int                     $assignee_id,
                                public readonly int                     $author_id,
                                public readonly string                  $created_at,
                                public readonly string                  $description,
                                public readonly ?int                    $head_pipeline_id,
                                public readonly int                     $id,
                                public readonly int                     $iid,
                                public readonly ?string                 $last_edited_at,
                                public readonly ?int                    $last_edited_by_id,
                                public readonly ?string                 $merge_commit_sha,
                                public readonly ?string                 $merge_error,
                                public readonly array                   $merge_params,
                                public readonly string                  $merge_status,
                                public readonly ?int                    $merge_user_id,
                                public readonly bool                    $merge_when_pipeline_succeeds,
                                public readonly ?int                    $milestone_id,
                                public readonly string                  $source_branch,
                                public readonly int                     $source_project_id,
                                public readonly int                     $state_id,
                                public readonly ?string                 $target_branch,
                                public readonly int                     $target_project_id,
                                public readonly int                     $time_estimate,
                                public readonly string                  $title,
                                public readonly string                  $updated_at,
                                public readonly ?int                    $updated_by_id,
                                public readonly string                  $url,
                                public readonly Project                 $source,
                                public readonly Project                 $target,
                                public readonly Commit                  $last_commit,
                                public readonly bool                    $work_in_progress,
                                public readonly int                     $total_time_spent,
                                public readonly int                     $time_change,
                                public readonly ?string                 $human_total_time_spent,
                                public readonly ?string                 $human_time_change,
                                public readonly ?string                 $human_time_estimate,
                                public readonly array                   $assignee_ids,
                                public readonly array                   $reviewer_ids,
                                public readonly array                   $labels,
                                public readonly string                  $state,
                                public readonly bool                    $blocking_discussions_resolved,
                                public readonly bool                    $first_contribution,
                                public readonly string                  $detailed_merge_status,
                                public readonly MergeRequestAction|null $action)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['assignee_id'],
            $data['author_id'],
            $data['created_at'],
            $data['description'],
            $data['head_pipeline_id'],
            $data['id'],
            $data['iid'],
            $data['last_edited_at'],
            $data['last_edited_by_id'],
            $data['merge_commit_sha'],
            $data['merge_error'],
            $data['merge_params'],
            $data['merge_status'],
            $data['merge_user_id'],
            $data['merge_when_pipeline_succeeds'],
            $data['milestone_id'],
            $data['source_branch'],
            $data['source_project_id'],
            $data['state_id'],
            $data['target_branch'],
            $data['target_project_id'],
            $data['time_estimate'],
            $data['title'],
            $data['updated_at'],
            $data['updated_by_id'],
            $data['url'],
            Project::fromJson($data['source']),
            Project::fromJson($data['target']),
            Commit::fromJson($data['last_commit']),
            $data['work_in_progress'],
            $data['total_time_spent'],
            $data['time_change'],
            $data['human_total_time_spent'],
            $data['human_time_change'],
            $data['human_time_estimate'],
            $data['assignee_ids'],
            $data['reviewer_ids'],
            $data['labels'],
            $data['state'],
            $data['blocking_discussions_resolved'],
            $data['first_contribution'],
            $data['detailed_merge_status'],
            MergeRequestAction::tryFrom($data['action'] ?? ''),
        );
    }

    /**
     * @return string[]
     */
    public function getLabelTitles(): array
    {
        return array_values(array_map(fn($label) => $label['title'], $this->labels));
    }
}