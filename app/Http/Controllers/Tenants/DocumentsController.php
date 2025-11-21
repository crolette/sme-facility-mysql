<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tenants\Document;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
            'type' => 'nullable|integer|gt:0'
        ]);

        $validatedFields = $validator->validated();
        $documents = Document::query();


        if (Auth::user()->hasRole('Maintenance Manager')) {
            $documents->withManager(Auth::user());
        }

        if (isset($validatedFields['type'])) {
            $documents->where('category_type_id', $validatedFields['type']);
        };

        if (isset($validatedFields['q'])) {
            $documents->where(function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['q'] . '%')
                    ->orWhere('description', 'like', '%' . $validatedFields['q'] . '%');
            });
        }

        $types = CategoryType::where('category', 'document')->get();

        return Inertia::render('tenants/documents/IndexDocuments', ['items' => $documents->orderBy($validatedFields['orderBy'] ?? 'created_at', $validatedFields['sortBy'] ?? 'desc')->paginate()->withQueryString(), 'filters' => $validator->safe()->only(['q', 'sortBy', 'status', 'orderBy', 'type', 'priority']), 'types' => $types]);
    }
}
