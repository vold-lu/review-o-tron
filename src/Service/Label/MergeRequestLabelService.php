<?php

namespace App\Service\Label;

use App\Entity\GitlabProject;
use App\Params\Event\EventName;
use App\Params\Gitlab\MergeRequest;

interface MergeRequestLabelService
{
    /**
     * @param GitlabProject|null $gitlabProject
     * @param MergeRequest $mergeRequest
     * @param EventName $eventName
     * @return string[]
     */
    public function getLabels(GitlabProject|null $gitlabProject, MergeRequest $mergeRequest, EventName $eventName): array;
}