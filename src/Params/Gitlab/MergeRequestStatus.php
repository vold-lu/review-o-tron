<?php

namespace App\Params\Gitlab;

// @see https://docs.gitlab.com/ee/api/merge_requests.html#merge-status
enum MergeRequestStatus: string
{
    case BLOCKED_STATUS = 'blocked_status'; // Blocked by another merge request.
    case BROKEN_STATUS = 'broken_status'; // Can’t merge into the target branch due to a potential conflict.
    case CHECKING = 'checking'; //  Git is testing if a valid merge is possible.
    case UNCHECKED = 'unchecked'; // Git has not yet tested if a valid merge is possible.
    case CI_MUST_PASS = 'ci_must_pass'; // A CI/CD pipeline must succeed before merge.
    case CI_STILL_RUNNING = 'ci_still_running'; // A CI/CD pipeline is still running.
    case DISCUSSIONS_NOT_RESOLVED = 'discussions_not_resolved'; // All discussions must be resolved before merge.
    case DRAFT_STATUS = 'draft_status'; // Can’t merge because the merge request is a draft.
    case EXTERNAL_STATUS_CHECKS = 'external_status_checks'; // All status checks must pass before merge.
    case MERGEABLE = 'mergeable'; // The branch can merge cleanly into the target branch.
    case NOT_APPROVED = 'not_approved'; // Approval is required before merge.
    case NOT_OPEN = 'not_open'; // The merge request must be open before merge.
    case POLICIES_DENIED = 'policies_denied'; //  The merge request contains denied policies.
}