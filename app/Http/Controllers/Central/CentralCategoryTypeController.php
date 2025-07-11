<?php

namespace App\Http\Controllers\Central;

use Exception;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Enums\CategoryTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Http\Requests\DocumentTypeRequest;
use App\Http\Requests\Central\CategoryTypeRequest;

class CentralCategoryTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documentTypes = CategoryType::all();
        return Inertia::render('central/types/index', ['types' => $documentTypes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = array_map(fn($case) => "{$case->value}", CategoryTypes::cases());

        return Inertia::render('central/types/create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryTypeRequest $request)
    {

        $translations = $request->validated('translations');

        try {
            DB::beginTransaction();

            $slug = Str::slug($request->validated('translations.en'));

            // the category is a string (values from enum)
            $categoryType = CategoryType::create([
                'category' => $request->validated('category'),
                'slug' => $slug
            ]);

            foreach ($translations as $locale => $label) {
                $categoryType->translations()->create([
                    'locale' => $locale,
                    'label' => $label
                ]);
            }


            DB::commit();

            return redirect()->route('central.types.index')->with(['message' => 'Category type created', 'type' => 'success']);
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
    public function show(CategoryType $categoryType)
    {
        return Inertia::render('central/types/show', ['type' => $categoryType]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CategoryType $categoryType)
    {
        $categories = array_map(fn($case) => "{$case->value}", CategoryTypes::cases());

        return Inertia::render('central/types/create', ['type' => $categoryType, 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryTypeRequest $request, CategoryType $categoryType)
    {
        $translations = $request->validated('translations');

        try {
            DB::beginTransaction();

            $slug = Str::slug($request->validated('translations.en'));

            // the category is a string (values from enum)
            $categoryType->update([
                'category' => $request->validated('category'),
                'slug' => $slug
            ]);

            foreach ($translations as $locale => $label) {
                $translation = $categoryType->translations()->where('locale', $locale)->first();

                if ($translation) {
                    $translation->update(['label' => $label]);
                } else {
                    $categoryType->translations()->create([
                        'locale' => $locale,
                        'label' => $label
                    ]);
                }
            }


            DB::commit();

            return redirect()->route('central.types.index')->with(['message' => 'Category type created', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            return back()->withInput()->with(['message' => 'Error during creation : ' . $e->getMessage(), 'type' => 'error']);
        }

        return back()->withInput()->with(['message' => 'Something went wrong', 'type' => 'warning']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategoryType $categoryType)
    {
        if (!$categoryType)
            return back();

        //BUG Should be prevented to be deleted if a tenant uses it
        try {
            $categoryType->delete();
            return redirect()->route('central.types.index');
        } catch (Exception $e) {
            Log::info($e->getMessage());

            return back();
        }
    }
}
