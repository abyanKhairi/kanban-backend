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
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "message" => "User Belum Login",
            ], 400);
        }

        $tasks = Task::whereHas('column.board.permission', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->latest()->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "Data Task Tidak Ada atau User Tidak Memiliki Permission",
            ], 404);
        }

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Data Tasks",
            "data" => $tasks
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            "title" => "required",
            "column_id" => "required|exists:columns,id",
            "position" => "required",
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

        $permissionUser = $user->permission()->where("add_cards", 1)->exists();
        $isBoardManager = $user->permission()->where("manage_board", 1)->exists();
        if (!$permissionUser || !$isBoardManager) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "Pesan" => "Tidak Memiliki Akses Untuk Menambahkan task",
            ]);
        }

        $task = $user->task()->create([
            "title" => $request->title,
            "column_id" => $request->column_id,
            "position" => $request->position
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
                "massage" => "User Belum Login",
            ]);
        }

        $permissionUser = $user->permission()->where("edit_cards", 1)->exists();
        $isBoardManager = $user->permission()->where("manage_board", 1)->exists();
        if ($task->user_id !== $user->id || !$permissionUser || !$isBoardManager) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "Pesan" => "Task Yang Ingin Diupdate Tidak Ada, Atau User Tidak Memiliki Akses Untuk Mengupdate Task",
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


    public function position(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            "position" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = auth("")->user();

        $permissionUser = $user->permission()->where("manage_board", 1)->exists();

        if ($task->user_id !== $user->id && !$permissionUser) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "Massage" => "Posisi Tidak Bisa Diubah",
            ], 400);
        };
        $task->position = $request->position;
        $task->save();
        return response()->json([
            "status" => 200,
            "success" => true,
            "Massage" => "Posisi Task Berhasi Diubah",
            "data" => $task
        ]);
    }


    public function column(Request $request, Task $task)
    {

        if (!$task) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "massage" => "Task Tidak Ada",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            "column_id" => "exists:columns,id",
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = auth("")->user();

        $permissionUser = $user->permission()->where("manage_board", 1)->exists();

        if ($task->user_id !== $user->id && !$permissionUser) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "massage" => "Tidak Dapat Memindahkan Task Ke Column Lain, Cihuyy",
            ]);
        }

        $taskColumn = $task->update([
            'column_id' => $request->column_id,
        ]);

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Task berhasil dipindahkan ke kolom lain",
            "data" => $taskColumn
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $user = auth("api")->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "massage" => "User Belum Login, User Tidak Dapat Menambahkan Board",
            ]);
        }

        if (!$task) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "Pesan" => "Task Yang Ingin dihapus tidak ada",
            ]);
        }

        $permissionUser = $user->permission()->where("delete_cards", 1)->exists();
        $isBoardManager = $user->permission()->where("manage_board", 1)->exists();

        if ($task->user_id !== $user->id || !$permissionUser || !$isBoardManager) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "Pesan" => "Task Yang Ingin dihapus tidak ada, Atau User Tidak Memiliki akses untuk menghapus Task",
            ]);
        }


        $task->delete();

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Task Berhasil Dihapus",
        ]);
    }
}
