<?php

namespace App\Enums;

enum LevelTypes: string
{
    case SITE = 'site';
    case BUILDING = 'building';
    case FLOOR = 'floor';
    case ROOM = 'room';
}
