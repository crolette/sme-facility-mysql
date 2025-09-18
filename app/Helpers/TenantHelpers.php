<?php

use Illuminate\Database\Eloquent\Model;

if (!function_exists('generateCodeNumber')) {

    // $count : number of already existing items
    // $prefix : prefix user for the code 
    // $lengthCode : number of '0' put in the code
    function generateCodeNumber(int $count, string $prefix, int $lengthCode = 2): string
    {
        return $prefix . str_pad($count, $lengthCode, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('qrCodeHash')) {
    
    /**
     * generateQRCodeHash
     *
     * @param  mixed $modelType
     * @param  Model $model
     * @return string
     */
    function generateQRCodeHash(string $modelType, Model $model): string
    {
        $data = $modelType === 'assets'
            ?
            implode('::', [$model->code, tenant()->domain->domain])
            :
            implode('::', [$model->reference_code, tenant()->domain->domain]);

        return substr(hash('sha256', $data . config('app.key')), 0, 12);
    }
}
