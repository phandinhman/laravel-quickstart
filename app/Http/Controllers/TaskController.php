<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TaskRepository;
use App\Task;
use App\Jobs\CreateTasks;

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
        $deadlineToday = $this->tasks->deadlineToday($request->user());
        return view('tasks.index', ['tasks' => $tasks, 'deadlineToday' => $deadlineToday]);
    }

    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required|max:25',
            'deadline' => 'required|date|after_or_equal:today',
        ]);

        $request->user()->tasks()->create([
            'name' => $request->name,
            'deadline' => Carbon::now()
        ]);

        return redirect('/tasks');
    }

    public function destroy(Request $request, Task $task) {
        try {
            $taskJob = new CreateTasks($request->user());
            $this->dispatch($taskJob);
            $this->authorize('destroy', $task);
            $task->delete();
        } catch (Exception $e) {
            dd($e);
        }
        return redirect('/tasks');
    }
}
