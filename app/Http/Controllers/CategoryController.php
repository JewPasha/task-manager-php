<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Auth::user()->categories()->withCount('tasks')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        Auth::user()->categories()->create([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        // If the form was submitted from dashboard, go back there
        if ($request->boolean('from_dashboard')) {
            return redirect()->route('dashboard')->with('success', 'Category created successfully.');
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);
        
        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        // If the form was submitted from dashboard, go back there
        if ($request->boolean('from_dashboard')) {
            return redirect()->route('dashboard')->with('success', 'Category updated successfully.');
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        
        $category->delete();

        // If the request came from dashboard, go back there
        if (request()->header('referer') && str_contains(request()->header('referer'), '/dashboard')) {
            return redirect()->route('dashboard')->with('success', 'Category deleted successfully.');
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
