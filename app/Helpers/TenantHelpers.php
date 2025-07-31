<?php

if (!function_exists('generateCodeNumber')) {

    // $count : number of already existing items
    // $prefix : prefix user for the code 
    // $lengthCode : number of '0' put in the code
    function generateCodeNumber(int $count, string $prefix, int $lengthCode = 2): string
    {
        return $prefix . str_pad($count, $lengthCode, '0', STR_PAD_LEFT);
    }
}
