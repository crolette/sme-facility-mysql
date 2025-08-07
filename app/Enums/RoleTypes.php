<?php

namespace App\Enums;

enum RoleTypes: string
{
    case ADMIN = 'Admin';
    case MANAGER = 'Maintenance Manager';
    case PROVIDER = 'Provider';
}
