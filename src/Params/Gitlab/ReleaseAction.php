<?php

namespace App\Params\Gitlab;

enum ReleaseAction: string
{
    case CREATE = 'create';
    case UPDATED = 'update';
}
