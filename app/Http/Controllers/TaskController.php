<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TaskRepository;
use App\Task;

class TaskController extends Controller
{

    protected $tasks;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TaskRepository $tasks)
    {
        $this->middleware('auth');
        $this->tasks = $tasks;
    }

    public function index(Request $request) {
        $tasks = $this->tasks->forUser($request->user());
        return view('tasks.index', ['tasks' => $tasks]);
    }

    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required|max:25',
        ]);

        $request->user()->tasks()->create([
            'name' => $request->name,
        ]);

        return redirect('/tasks');
    }

    public function destroy(Request $request, Task $task) {
        $this->authorize('destroy', $task);
        $task->delete();
        return redirect('/tasks');
    }
}