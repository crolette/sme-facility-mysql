<?php

namespace App\Http\Controllers\Central;

use Exception;
use Inertia\Inertia;
use App\Models\AssetType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\AssetCategory;
use App\Http\Requests\Central\AssetCategoryRequest;

class CentralAssetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = AssetCategory::all();
        return Inertia::render('central/assets/index', ['categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('central/assets/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetCategoryRequest $categoryRequest)
    {
        $translations = $categoryRequest->validated('translations');

        try {
            DB::beginTransaction();

            $slug = Str::slug($categoryRequest->validated('translations.en'));

            $category = AssetCategory::create([
                'slug' => $slug
            ]);

            foreach ($translations as $locale => $label) {
                $category->translations()->create([
                    'locale' => $locale,
                    'label' => $label
                ]);
            }

            DB::commit();

            return redirect()->route('central.assets.index')->with(['message' => 'Asset category created', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            return back()->withInput()->with(['message' => 'Error during creation : ' . $e->getMessage(), 'type' => 'error']);
        }

        return back()->withInput()->with(['message' => 'Something went wrong', 'type' => 'warning']);
    }

    /**
     * Display the specified resource.
     */
    public function show(AssetCategory $assetCategory)
    {
        return Inertia::render('central/assets/show', ['category' => $assetCategory->load('translations')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetCategory $assetCategory)
    {
        return Inertia::render('central/assets/create', ['category' => $assetCategory->load('translations')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetCategoryRequest $categoryRequest, AssetCategory $assetCategory)
    {
        $translations = $categoryRequest->validated('translations');

        try {
            DB::beginTransaction();

            $slug = Str::slug($categoryRequest->validated('translations.en'));

            $category = $assetCategory->update([
                'slug' => $slug
            ]);

            foreach ($translations as $locale => $label) {
                $translation = $assetCategory->translations()->where('locale', $locale)->first();

                if ($translation) {
                    $translation->update(['label' => $label]);
                } else {
                    $assetCategory->translations()->create([
                        'locale' => $locale,
                        'label' => $label
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('central.assets.index')->with(['message' => 'Asset category updated', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            return back()->withInput()->with(['message' => 'Error during update : ' . $e->getMessage(), 'type' => 'error']);
        }

        return back()->withInput()->with(['message' => 'Something went wrong', 'type' => 'warning']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetCategory $assetCategory)
    {
        if (!$assetCategory)
            return back();

        //BUG Should be prevented to be deleted if a tenant uses it
        try {
            $assetCategory->delete();
            return redirect()->route('central.assets.index')->with(['message' => 'Asset category deleted', 'type' => 'success']);
        } catch (Exception $e) {
            Log::info($e->getMessage());

            return back()->withInput()->with(['message' => 'Error during deletion : ' . $e->getMessage(), 'type' => 'error']);
        }
    }
}
