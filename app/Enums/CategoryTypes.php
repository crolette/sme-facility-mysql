<?php

namespace App\Enums;

enum CategoryTypes: string
{
    case DOCUMENT = 'document';
    case INTERVENTION = 'intervention';
    case ACTION = 'action';
    case ASSET = 'asset';
    case PROVIDER = 'provider';
    case MAT_OUTDOOR = 'outdoor_materials';
    case MAT_FLOOR = 'floor_materials';
    case MAT_WALL = 'wall_materials';
}
