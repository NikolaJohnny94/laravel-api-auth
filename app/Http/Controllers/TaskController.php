<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\Repositories\TaskRepository;

/**
 * @OA\Info(
 *     title="Task API",
 *     version="1.0.0"
 * )
 * 
 *  @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization"
 * )
 */
class TaskController extends Controller
{
    // protected $taskRepository;
    // public function __construct(TaskRepository $taskRepository) {
    //     $this->taskRepository = $taskRepository;
    // }
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Get list of tasks",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error occurred while trying to retrieve tasks from DB"
     *     )
     * )
     */
    public function index()
    {
        try {
            $tasks = Task::all();

            // switch ($tasks->isEmpty()) {
            //     case 1:
            //         $message = 'There are no tasks in the DB';
            //         break;
            //     case 0:
            //         $message = 'Tasks successfully retrieved from the DB';
            //         break;
            // }

            $message = $tasks->isEmpty() ? 'There are no tasks in the DB' : 'Tasks successfully retrieved from the DB';

            return response(['success' => true, 'message' => $message, 'data' => $tasks], 200);
        } catch (Exception $e) {
            return response(['success' => false, 'message' => 'Error occured while trying to retrieve tasks from DB', 'error_message' => $e->getMessage()], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Create a new task",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="New task successfully created",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed while trying to create new task"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error occurred while trying to create new task"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {

            $validatedData =  $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'finished' => 'required|boolean',
                'category' => 'required|in:work,personal,other',
            ]);

            $newTask = Task::create(array_merge($validatedData, ['user_id' => Auth::id()]));
            return response(['success' => true, 'message' => 'New task successfully created', 'data' => $newTask], 201);
        } catch (ValidationException $e) {
            return response(['success' => false, 'message' => 'Validation failed while trying to create new task', 'error_message' => $e->errors()], 422);
        } catch (Exception $e) {
            return response(["success" => false, 'message' => "Error occured while trying create new task", 'error_message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Get a specific task by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task with specified ID successfully retrieved",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task with specified ID not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error occurred while trying to retrieve task with specified ID"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $task = Task::findOrFail($id);
            return response(['success' => true, 'message' => "Task with id: $id successfully retrieved from the DB", 'data' => $task], 200);
        } catch (ModelNotFoundException $e) {
            return response(["success" => false, 'message' => "Task with id $id not found in the DB.", 'error_message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response(["success" => false, 'message' => "Error occured while trying to retrive task with id: $id.", 'error_message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Update a specific task by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task with specified ID successfully updated",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed while trying to update task with specified ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task with specified ID not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error occurred while trying to update task with specified ID"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'finished' => 'sometimes|required|boolean',
                'category' => 'sometimes|required|in:work,personal,other',
            ]);

            $task = Task::findOrFail($id);
            $task->update($validatedData);

            return response(['success' => true, 'message' => "Task with id $id successfully updated", 'data' => $task], 200);
        } catch (ValidationException $e) {
            return response(['success' => false, 'message' => "Validation failed while trying to update task with id: $id.", 'error_message' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response(['success' => false, 'message' => "Task with id $id not found in the DB.", 'error_message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response(['success' => false, 'message' => "An error occurred while updating the task with id:", 'error_message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Delete a specific task by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task with specified ID successfully deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task with specified ID not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error occurred while trying to delete task with specified ID"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->delete();
            return response(['success' => true, 'message' => "Task with id: $id successfully deleted from the DB"], 200);
        } catch (ModelNotFoundException $e) {
            return response(["success" => false, 'message' => "Task with id $id not found in the DB.", 'error_message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response(["success" => false, 'message' => "Error occured while trying to delete task with id: $id.", 'error_message' => $e->getMessage()], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/tasks/search/{name}",
     *     tags={"Tasks"},
     *     summary="Search tasks by name",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks that match search criteria successfully retrieved",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error occurred while processing search request"
     *     )
     * )
     */
    public function search($name)
    {
        try {
            $tasks = Task::whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($name) . '%'])->get();

            // switch ($tasks->isEmpty()) {
            //     case 1:
            //         $message = "No tasks found matching the search criteria: '$name'";
            //         break;
            //     case 0:
            //         $message = "Tasks that match search criteria: '$name' successfully retrieved from the DB";
            //         break;
            // }
            $message = $tasks->isEmpty() ? "No tasks found matching the search criteria: '$name'" : "Tasks that match search criteria: '$name' successfully retrieved from the DB";
            return response(['success' => true, 'message' => $message, 'data' => $tasks], 200);
        } catch (Exception $e) {
            return response(['success' => false, 'message' => 'Unexpected error occurred while processing search request', 'error_message' => $e->getMessage()], 500);
        }
    }
}
