<?php

namespace App\Service\Label;

use App\Entity\GitlabProject;
use App\Params\Event\MergeRequest\MergeRequestEventName;
use App\Params\Gitlab\MergeRequest;

class MergeRequestStatusLabelService implements MergeRequestLabelService
{
    public function getLabels(GitlabProject|null $gitlabProject, MergeRequest $mergeRequest, MergeRequestEventName $eventName): array
    {
        if (!$this->isTagFeatureEnabled($gitlabProject)) {
            return [];
        }

        $label = match ($eventName) {
            MergeRequestEventName::APPROVED => $this->handleMergeRequestApproved($gitlabProject, $mergeRequest),
            MergeRequestEventName::CLOSED => $this->handleMergeRequestClosed($gitlabProject, $mergeRequest),
            MergeRequestEventName::MERGED => $this->handleMergeRequestMerged($gitlabProject, $mergeRequest),
            MergeRequestEventName::OPENED => $this->handleMergeRequestOpened($gitlabProject, $mergeRequest),
            MergeRequestEventName::REJECTED => $this->handleMergeRequestRejected($gitlabProject, $mergeRequest),
            MergeRequestEventName::UPDATED => $this->handleMergeRequestUpdated($gitlabProject, $mergeRequest),
        };

        return !empty($label) ? [$label] : [];
    }

    private function handleMergeRequestApproved(GitlabProject $gitlabProject, MergeRequest $mergeRequest): string
    {
        return $gitlabProject->getGitlabLabelApproved();
    }

    private function handleMergeRequestClosed(GitlabProject $gitlabProject, MergeRequest $mergeRequest): string
    {
        return $gitlabProject->getGitlabLabelRejected();
    }

    private function handleMergeRequestMerged(GitlabProject $gitlabProject, MergeRequest $mergeRequest): string
    {
        return $gitlabProject->getGitlabLabelApproved();
    }

    private function handleMergeRequestOpened(GitlabProject $gitlabProject, MergeRequest $mergeRequest): string
    {
        return $mergeRequest->work_in_progress && $gitlabProject->getGitlabLabelDraft() ?
            $gitlabProject->getGitlabLabelDraft() : $gitlabProject->getGitlabLabelOpened();
    }

    private function handleMergeRequestRejected(GitlabProject $gitlabProject, MergeRequest $mergeRequest): string
    {
        return $gitlabProject->getGitlabLabelRejected();
    }

    private function handleMergeRequestUpdated(GitlabProject $gitlabProject, MergeRequest $mergeRequest): string
    {
        // Update can allow happens if merge request is ready to be merged, other case are handled by other handlers
        if (!$mergeRequest->blocking_discussions_resolved) {
            return '';
        }

        // Prevent from running if event is triggered from approval request (onMergeRequestMerged)
        // after onMergeRequestMerged handler is run onMergeRequestUpdated will be fired because of tag changes
        // therefore we must be able to detect that tag being applied is the approved one, and therefore discard event
        $mergeRequestLabels = $mergeRequest->getLabelTitles();
        if (in_array($gitlabProject->getGitlabLabelApproved(), $mergeRequestLabels)) {
            return $gitlabProject->getGitlabLabelApproved();
        }

        // Determinate which label we need to apply on the PR depending on context
        return $mergeRequest->work_in_progress && $gitlabProject->getGitlabLabelDraft() ?
            $gitlabProject->getGitlabLabelDraft() : $gitlabProject->getGitlabLabelOpened();
    }

    /**
     * Determinate if tag feature is enabled for given gitlab project
     *
     * @param GitlabProject|null $gitlabProject the project to check for
     * @return bool true if the gitlab project use the tag feature. false otherwise
     */
    private function isTagFeatureEnabled(?GitlabProject $gitlabProject): bool
    {
        return $gitlabProject !== null &&
            $gitlabProject->getGitlabLabelOpened() &&
            $gitlabProject->getGitlabLabelApproved() &&
            $gitlabProject->getGitlabLabelRejected();
    }
}