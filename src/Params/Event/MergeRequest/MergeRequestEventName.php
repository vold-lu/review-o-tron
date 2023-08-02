<?php

namespace App\Params\Event\MergeRequest;

enum MergeRequestEventName: string
{
    case APPROVED = 'approved';
    case CLOSED = 'closed';
    case MERGED = 'merged';
    case OPENED = 'opened';
    case REJECTED = 'rejected';
    case UPDATED = 'updated';
}
