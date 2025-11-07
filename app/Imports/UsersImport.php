<?php

namespace App\Imports;

use App\Imports\UsersDataImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsersImport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            'Users' => new UsersDataImport(),
        ];
    }
}
