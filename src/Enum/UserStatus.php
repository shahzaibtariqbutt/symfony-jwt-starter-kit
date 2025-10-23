<?php

declare(strict_types=1);

namespace App\Enum;

enum UserStatus: string
{
    case Active = 'active';
    case DeletionRequested = 'deletion_requested';
    case Deleted = 'deleted';
}
