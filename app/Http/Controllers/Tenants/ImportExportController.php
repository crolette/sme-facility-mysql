<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImportExportController extends Controller
{
    public function show() {

        return Inertia::render('settings/import-export');
    }
}
