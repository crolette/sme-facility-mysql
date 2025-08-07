<?php

namespace App\Enums;

enum CategoryTypes: string
{
    case DOCUMENT = 'document';
    case INTERVENTION = 'intervention';
    case ACTION = 'action';
    case ASSET = 'asset';
    case PROVIDER = 'provider';
    case MAT_OUTDOOR = 'outdoor materials';
    case MAT_FLOOR = 'floor materials';
    case MAT_WALL = 'wall materials';
}
