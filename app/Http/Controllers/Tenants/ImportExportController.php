<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImportExportController extends Controller
{
    public function show()
    {

        if (!Gate::allows('export excel')) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }

        return Inertia::render('settings/import-export');
    }
}
