<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Auth::user()->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Task created successfully.');
    }


    public function update(Request $request, Task $task)
    {
        // Check if user owns this task
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        // Check if user owns this task
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }
        
        $task->delete();

        return redirect()->route('dashboard')->with('success', 'Task deleted successfully.');
    }

    public function toggle(Task $task)
    {
        // Check if user owns this task
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }
        
        $task->update(['completed' => !$task->completed]);

        return back()->with('success', 'Task status updated.');
    }
}
