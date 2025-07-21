<?php

namespace App\Enums;

enum CategoryTypes: string
{
    case DOCUMENT = 'document';
    case INTERVENTION = 'intervention';
    case ACTION = 'action';
    case ASSET = 'asset';
}
