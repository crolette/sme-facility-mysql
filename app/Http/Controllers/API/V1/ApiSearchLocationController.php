<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;

class ApiSearchLocationController extends Controller
{
    public function index(Request $request)
    {

        $search = $request->query('q');
        if (!$search) {
            return response()->json([]);
        }
        Debugbar::info('search API', $search);

        return response()->json(
            collect()
                ->merge($this->searchEntity(Site::class, 'site',  $search))
                ->merge($this->searchEntity(Building::class, 'building',  $search))
                ->merge($this->searchEntity(Floor::class, 'floor',  $search))
                ->merge($this->searchEntity(Room::class, 'room',  $search))
        );
    }

    private function searchEntity($modelClass, $type, $search)
    {
        return $modelClass::where(function ($q) use ($search) {
            $q->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereRaw('LOWER(reference_code) LIKE ?', ['%' . strtolower($search) . '%']);
        })
            ->orWhereHas('maintainable', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
            })
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => $type,
                'name' => $item->maintainable->name,
                'reference_code' => $item->reference_code,
                'code' => $item->code,
            ]);
    }
}
