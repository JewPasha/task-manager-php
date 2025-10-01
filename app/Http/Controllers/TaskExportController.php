<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskExportController extends Controller
{
    public function index()
    {
        return Inertia::render('Tasks/Export');
    }

    public function export(Request $request)
    {
        $query = Auth::user()->tasks()->with('category');

        // Apply filters if provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('completed')) {
            $query->where('completed', $request->boolean('completed'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        $filename = 'tasks_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($tasks) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Title',
                'Description',
                'Status',
                'Due Date',
                'Category',
                'Created At',
                'Updated At'
            ]);

            // CSV data
            foreach ($tasks as $task) {
                fputcsv($file, [
                    $task->title,
                    $task->description,
                    $task->completed ? 'Completed' : 'Incomplete',
                    $task->due_date ? $task->due_date->format('Y-m-d') : '',
                    $task->category ? $task->category->name : '',
                    $task->created_at->format('Y-m-d H:i:s'),
                    $task->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        
        $imported = 0;
        $errors = [];

        // Skip header row
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            try {
                $title = $data[0] ?? '';
                $description = $data[1] ?? '';
                $status = strtolower(trim($data[2] ?? ''));
                $dueDate = $data[3] ?? '';
                $categoryName = $data[4] ?? '';

                if (empty($title)) {
                    continue;
                }

                // Find or create category
                $categoryId = null;
                if (!empty($categoryName)) {
                    $category = Auth::user()->categories()->firstOrCreate(
                        ['name' => $categoryName],
                        ['color' => '#3B82F6']
                    );
                    $categoryId = $category->id;
                }

                // Create task
                Auth::user()->tasks()->create([
                    'title' => $title,
                    'description' => $description,
                    'completed' => $status === 'completed',
                    'due_date' => $dueDate ?: null,
                    'category_id' => $categoryId,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + 1) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        return redirect()->route('dashboard')->with([
            'success' => "Successfully imported {$imported} tasks.",
            'errors' => $errors
        ]);
    }
}
