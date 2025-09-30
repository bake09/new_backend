<?php

namespace App\Http\Controllers;

use App\Models\Todo;

use App\Models\User;
use App\Events\TodoSend;
use App\Events\TodoDelete;

use App\Events\TodoToggle;

use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use App\Notifications\TodoCreated;
use App\Http\Resources\TodoResource;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use App\Events\ActivityCreated;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('read_todo');

        $perPage = $request->get('per_page', 15); // Standard: 5 pro Seite, kann vom Frontend überschrieben werden

        $todos = Todo::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return TodoResource::collection($todos);
    }

    public function store(Request $request)
    {
        Gate::authorize('create_todo');
        
        $request->validate([
            'content' => 'required|string|max:255',
        ]);

        $dueDate = Carbon::now()->addWeek(); // Setzt due_date auf 7 Tage später
        
        $todo = Todo::create([
            'due_date' => $dueDate, // due_date on-the-fly erstellen
            'content' => $request->content,
            'user_id' =>  Auth::user()->id
        ]);

        // Benutzer mit aktiven Subscriptions abrufen
        $usersWithSubscriptions = User::whereHas('pushSubscriptions')->get();

        // Benachrichtigung senden
        Notification::send($usersWithSubscriptions, new TodoCreated($todo, Auth::user()->name));
        // $request->user()->notify(new TodoCreated($todo));
        
        broadcast(new TodoSend($todo->load('user')))->toOthers();
        $activity = activity()
            ->causedBy(Auth::user())
            ->performedOn($todo)
            ->withProperties(['item' => $todo->content, 'causer' => Auth::user()->name, 'action' => ['type' => 'created']])
            ->log("Neu-Erstellung des Todos's : " . $todo->content . " von " . Auth::user()->name);
        event(new \App\Events\ActivityCreated($activity));

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
            'done' => 'sometimes|required|boolean',
            'due_date' => 'sometimes|required|date'
        ]);

        $todo->update($request->only(['content', 'done', 'due_date']));
        TodoToggle::dispatch($todo->load('user'));
        
        $activity = activity()
            ->causedBy(Auth::user())
            ->performedOn($todo)
            ->withProperties(['item' => $todo->content, 'causer' => Auth::user()->name, 'action' => ['type' => 'updated']])
            ->log("Neu-Erstellung des Todos's : " . $todo->content . " von " . Auth::user()->name);
        event(new \App\Events\ActivityCreated($activity));
        

        return new TodoResource($todo);
    }

    public function destroy(Todo $todo)
    {
        Gate::authorize('delete_todo');
        
        $todo->delete();
        broadcast(new TodoDelete($todo))->toOthers();
        $activity = activity()
            ->causedBy(Auth::user())
            ->performedOn($todo)
            ->withProperties(['item' => $todo->content, 'causer' => Auth::user()->name, 'action' => ['type' => 'deleted']])
            ->log("Neu-Erstellung des Todos's : " . $todo->content . " von " . Auth::user()->name);
        event(new \App\Events\ActivityCreated($activity));

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
        $activity = activity()
            ->causedBy(Auth::user())
            ->performedOn($todo)
            ->withProperties(['item' => $todo->content, 'causer' => Auth::user()->name, 'action' => ['type' => 'toggeled', 'done' => $request->done]])
            ->log("Status-Änderung des Todos's : " . $todo->content . " von " . Auth::user()->name);
        event(new ActivityCreated($activity));

        return $todo->load('user');
    }
}
