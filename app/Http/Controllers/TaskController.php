<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            "title" => "required",
            "column_id" => "required|exists:columns,id"
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth("api")->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "massage" => "User Belum Login, User Tidak Dapat Menambahkan Board",
            ]);
        }

        $task = $user->task()->create([
            "title" => $request->title,
            "column_id" => $request->column_id,
        ]);

        return response()->json([
            "status" => 200,
            "success" => true,
            "massage" => "task Berhasil Ditambahkan",
            "data" => $task,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validator =  Validator::make($request->all(), [
            "title" => "required",
            // "column_id" => "required|exists:columns,id",
            "description" => "required",
            "deadline" => "required",
            "status" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth("api")->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "massage" => "User Belum Login, User Tidak Dapat Menambahkan Board",
            ]);
        }

        $task->title = $request->title;
        $task->description = $request->description;
        $task->deadline = $request->deadline;
        $task->status = $request->status;
        $task->save();

        return response()->json([
            "status" => 200,
            "success" => true,
            "massage" => "Task Berhasil Diupdate",
            "data" => $task,
        ], 200);

        // $task->column_id = $request->column_id;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
