<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     description="Task model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title of the task"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the task"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="string",
 *         description="Category of the task"
 *     ),
 *     @OA\Property(
 *         property="finished",
 *         type="boolean",
 *         description="Task completion status"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID of the user who created the task"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug generated from the title"
 *     )
 * )
 */
class Task extends Model
{
    protected $fillable = ['title', 'description', 'category', 'finished', 'user_id'];
    use HasFactory;
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            $task->slug = Str::slug($task->title);
        });
    }
}
