<?php

use App\Enums\TicketStatus;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/statistics/tickets')->group(
    function () {

        Route::get('/by-period', function (Request $request) {

            $begin = '2025-01-01';
            $end = '2025-12-31';
            if ($request->query('period') === 'week') {
                $ticketsByPeriod = Ticket::query()
                    ->where('status', '<>', TicketStatus::CLOSED)
                    ->where('created_at', '>', $begin)->where('created_at', '<', $end)
                    ->selectRaw('WEEK(created_at) AS week, COUNT(*) as count_week')
                    ->groupBy('week')
                    ->orderBy('week')
                    ->pluck('count_week', 'week');
            } else {
                $ticketsByPeriod = Ticket::query()
                    ->where('status', '<>', TicketStatus::CLOSED)
                    ->where('created_at', '>', $begin)->where('created_at', '<', $end)
                    ->selectRaw('DATE_FORMAT(created_at, \'%m-%Y\') AS month, COUNT(*) as count_month')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('count_month', 'month');
            }

            return ApiResponse::success($ticketsByPeriod);
        })->name('api.statistics.tickets.by-period');


        Route::get('/by-items', function (Request $request) {

            $ticketsByAssetOrLocations = Ticket::query()
                ->where('status', '<>', TicketStatus::CLOSED)
                ->selectRaw('ticketable_type, ticketable_id, COUNT(*) as count')
                ->groupBy('ticketable_type', 'ticketable_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            $ticketsByAssetOrLocations = $ticketsByAssetOrLocations->map(function ($item) {
                if (class_basename($item->ticketable_type) === class_basename(Asset::class)) {
                    $ticketable = $item->ticketable_type::withTrashed()->find($item->ticketable_id);
                } else {
                    $ticketable = $item->ticketable_type::find($item->ticketable_id);
                }

                return [
                    'id' => $ticketable->id,
                    'reference_code' => $ticketable?->reference_code  ?? 'Unknown', // Adapte selon ton attribut
                    'name' => $ticketable?->name  ?? 'Unknown', // Adapte selon ton attribut
                    'type' => class_basename($item->ticketable_type),
                    'count' => $item->count
                ];
            });

            return ApiResponse::success($ticketsByAssetOrLocations);
        })->name('api.statistics.tickets.by-items');
    }
);
