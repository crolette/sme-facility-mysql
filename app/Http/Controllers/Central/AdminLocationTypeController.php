<?php

namespace App\Http\Controllers\Central;

use Exception;
use Inertia\Inertia;
use App\Enums\LevelTypes;
use Illuminate\Support\Str;
use App\Models\BuildingType;
use App\Models\LocationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\TypeRequest;
use App\Http\Requests\Central\BuildingTypeRequest;
use App\Http\Requests\Central\LocationTypeRequest;

class AdminLocationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd(LocationType::all());
        $types = LocationType::all()->groupBy('level');
        // $types = LocationType::all();

        return Inertia::render('central/types/index', ['types' => $types ?? null, 'routeName' => 'locations']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = array_map(fn($case) => "{$case->value}", LevelTypes::cases());
        return Inertia::render('central/types/create', ['types' => $types, 'routeName' => 'locations']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LocationTypeRequest $request)
    {
        $translations = $request->validated('translations');
        try {
            DB::beginTransaction();
            $slug = Str::slug($request->validated('translations.en'));
            $buildingType = LocationType::create([
                'prefix' => $request->validated('prefix'),
                'level' => $request->validated('level'),
                'slug' => $slug
            ]);

            foreach ($translations as $locale => $label) {
                $buildingType->translations()->create([
                    'locale' => $locale,
                    'label' => $label
                ]);
            }

            DB::commit();
            return redirect()->route('central.locations.index')->with(['message' => 'Location type created', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return back()->withInput()->with(['message' => 'Error during creation : ' . $e->getMessage(), 'type' => 'error']);
        }

        return back()->withInput()->with(['message' => 'Something went wrong', 'type' => 'warning']);
    }

    /**
     * Display the specified resource.
     */
    public function show(LocationType $locationType)
    {
        return Inertia::render('central/types/show', ['type' => $locationType->load('translations')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LocationType $locationType)
    {
        $types = array_map(fn($case) => "{$case->value}", LevelTypes::cases());
        return Inertia::render('central/types/create', ['type' => $locationType, 'types' => $types, 'routeName' => 'locations']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LocationTypeRequest $request, LocationType $locationType)
    {
        if ($request->validated('prefix') !== $locationType->prefix) {
            $errors = new MessageBag([
                'prefix' => ['You cannot change the prefix of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        if ($request->validated('level') !== $locationType->level) {
            $errors = new MessageBag([
                'level' => ['You cannot change the level of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        $translations = $request->validated('translations');

        try {
            DB::beginTransaction();
            $slug = Str::slug($request->validated('translations.en'));
            $locationType->update([
                'prefix' => $request->validated('prefix'),
                'level' => $request->validated('level'),
                'slug' => $slug
            ]);

            foreach ($translations as $locale => $label) {
                $translation = $locationType->translations()->where('locale', $locale)->first();

                if ($translation) {
                    $translation->update(['label' => $label]);
                } else {
                    $locationType->translations()->create([
                        'locale' => $locale,
                        'label' => $label
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('central.locations.index');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LocationType $locationType)
    {
        if (!$locationType)
            return back();

        //BUG Should be prevented to be deleted if a tenant uses it
        try {
            $locationType->delete();
            return redirect()->route('central.locations.index');
        } catch (Exception $e) {
            Log::info($e->getMessage());

            return back();
        }
    }
}
