<?php

namespace App\Http\Controllers;

use App\Models\Todo;

use App\Events\TodoSend;
use App\Events\TodoDelete;
use App\Events\TodoToggle;

use Illuminate\Http\Request;

use App\Http\Resources\TodoResource;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TodoController extends Controller
{
    public function index()
    {
        Gate::authorize('read_todo');
        
        return TodoResource::collection(Todo::with('user')->get());
    }

    public function store(Request $request)
    {
        Gate::authorize('create_todo');
        
        $request->validate([
            'content' => 'required|string|max:255',
        ]);

        $todo = Todo::create([
            'content' => $request->content,
            'user_id' =>  Auth::user()->id
        ]);
        broadcast(new TodoSend($todo->load('user')))->toOthers();

        return new TodoResource($todo->load('user'));
    }

    public function show(Todo $todo)
    {
        Gate::authorize('read_todo');
        
        return new TodoResource($todo);
    }

    public function update(Request $request, Todo $todo)
    {
        Gate::authorize('update_todo');
        
        $request->validate([
            'content' => 'sometimes|required|string|max:255',
            'done' => 'sometimes|required|boolean'
        ]);

        $todo->update($request->only(['content', 'done']));

        return new TodoResource($todo);
    }

    public function destroy(Todo $todo)
    {
        Gate::authorize('delete_todo');
        
        $todo->delete();
        broadcast(new TodoDelete($todo))->toOthers();

        return response()->json($todo, 204);
    }

    public function toggledone(Request $request, Todo $todo)
    {
        Gate::authorize('update_todo');
        
        $request->validate([
            'done' => 'required|boolean',
        ]);
    
        $todo->update([
            'done' => $request->done,
        ]);
        
        TodoToggle::dispatch($todo->load('user'));

        return $todo->load('user');
    }
}
