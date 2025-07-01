<?php

namespace Laraveltoolkit\Tests;

use Illuminate\Auth\Access\Response;
use Laraveltoolkit\ACL\HasDenyResponse;

enum UserRole: string implements HasDenyResponse
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function denyResponse(): Response
    {
        return match ($this) {
            self::ADMIN => Response::denyWithStatus(403),
            self::USER => Response::denyAsNotFound(),
        };
    }
}
