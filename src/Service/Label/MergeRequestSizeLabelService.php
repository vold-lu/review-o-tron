<?php

namespace App\Service\Label;

use App\Entity\GitlabProject;
use App\Params\Event\EventName;
use App\Params\Gitlab\MergeRequest;
use Gitlab\Client;

class MergeRequestSizeLabelService implements MergeRequestLabelService
{
    public function __construct(private readonly Client $client)
    {
    }

    public function getLabels(?GitlabProject $gitlabProject, MergeRequest $mergeRequest, EventName $eventName): array
    {
        if (!$this->isSizeFeatureEnabled($gitlabProject)) {
            return [];
        }

        $changedLines = $this->countMergeRequestChangedLines($gitlabProject->getGitlabId(), $mergeRequest->iid);
        if ($changedLines < 60) {
            return [$gitlabProject->getGitlabLabelSmallChanges()];
        }
        if ($changedLines < 120) {
            return [$gitlabProject->getGitlabLabelMediumChanges()];
        }
        if ($changedLines < 300) {
            return [$gitlabProject->getGitlabLabelLargeChanges()];
        }

        return [$gitlabProject->getGitlabLabelExtraLargeChanges()];
    }

    private function countMergeRequestChangedLines(int $projectId, int $mergeRequestIid): int
    {
        // TODO: improve (deletion would be more easier to read than addition)

        $lines = 0;
        $changes = $this->client->mergeRequests()->changes($projectId, $mergeRequestIid)['changes'];

        foreach ($changes as $change) {
            $diff = $change['diff'];
            $lines += preg_match_all('/^\+/m', $diff);
            $lines += preg_match_all('/^-/m', $diff);
        }

        return $lines;
    }

    /**
     * Determinate if size feature is enabled for given gitlab project
     *
     * @param GitlabProject|null $gitlabProject the project to check for
     * @return bool true if the gitlab project use the size feature. false otherwise
     */
    private function isSizeFeatureEnabled(?GitlabProject $gitlabProject): bool
    {
        return $gitlabProject !== null &&
            $gitlabProject->getGitlabLabelSmallChanges() &&
            $gitlabProject->getGitlabLabelMediumChanges() &&
            $gitlabProject->getGitlabLabelLargeChanges() &&
            $gitlabProject->getGitlabLabelExtraLargeChanges();
    }
}