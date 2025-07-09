<?php

namespace App\Enums;

enum CategoryTypes: string
{
    case DOCUMENT = 'document';
    case INTERVENTION = 'intervention';
    case ASSET = 'asset';
}
