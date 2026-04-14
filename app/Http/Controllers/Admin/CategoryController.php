<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'min:2', 'max:100', Rule::unique('categories', 'name')],
                'description' => ['nullable', 'string', 'max:500'],
            ],
            [
                'name.required' => 'Enter a category name.',
                'name.unique' => 'That category already exists.',
            ]
        )->validateWithBag('createCategory');

        Category::create([
            'name' => trim($validated['name']),
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
        ]);

        return back()->with('status', 'Category created successfully.');
    }
}
