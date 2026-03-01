<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Colocation;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request, Colocation $colocation)
    {
       
        if ($colocation->owner_id !== auth()->id() || $colocation->status !== 'active') {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        
        $data['name'] = trim($data['name']);

        $colocation->categories()->create($data);

        return back()->with('success', 'Category added.');
    }

    public function destroy(Colocation $colocation, Category $category)
    {
       
        if ($colocation->owner_id !== auth()->id() || $colocation->status !== 'active') {
            abort(403);
        }

       
        if ($category->colocation_id !== $colocation->id) {
            abort(404);
        }

    
        $category->expenses()->update(['category_id' => null]);

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}