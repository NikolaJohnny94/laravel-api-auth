<?php

namespace App\Repositories;

// use Illuminate\Http\Request;

use App\Models\Task;

class TaskRepository
{
    public function getTasks()
    {
        // $user = $request->user();
        // $tasks = Task::where('user_id', $user->id)->get();
        return Task::all();
    }

    public function findTaskById(int $id)
    {
        return Task::findOrFail($id);
    }

    public function createTask(array $data)
    {
        return Task::create($data);
    }

    public function updateTask($id, array $data)
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function findOrFail($id)
    {
        return Task::findOrFail($id);
    }

    public function deleteTask(int $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return $task;
    }

    public function searchTaskByTitle(string $title)
    {
        return Task::whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($title) . '%'])->get();
    }
}
