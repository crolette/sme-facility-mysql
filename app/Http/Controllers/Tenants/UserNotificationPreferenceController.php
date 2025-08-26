<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Tenants\User;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Contract;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Enums\ContractRenewalTypesEnum;

class UserNotificationPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        dd($preferences = config('notifications'));
        // dd(Auth::user()->notification_preferences);

        return Inertia::render('tenants/contracts/index', []);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {


        return Inertia::render('tenants/contracts/create', []);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Contract $contract)
    {


        return Inertia::render('tenants/contracts/create', []);
    }


    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {


        return Inertia::render('tenants/contracts/show', []);
    }
}
