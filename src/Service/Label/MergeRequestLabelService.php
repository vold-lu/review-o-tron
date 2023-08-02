<?php

namespace App\Service\Label;

use App\Entity\GitlabProject;
use App\Params\Event\MergeRequest\MergeRequestEventName;
use App\Params\Gitlab\MergeRequest;

interface MergeRequestLabelService
{
    /**
     * @param GitlabProject|null $gitlabProject
     * @param MergeRequest $mergeRequest
     * @param MergeRequestEventName $eventName
     * @return string[]
     */
    public function getLabels(GitlabProject|null $gitlabProject, MergeRequest $mergeRequest, MergeRequestEventName $eventName): array;
}