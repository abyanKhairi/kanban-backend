<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\column;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ColumnController extends Controller
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

        $columns = column::with('task')->whereHas('board.permission', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orderBy('position', 'asc')->get();

        if (!$columns) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "Data Board Tidak Ada atau User Tidak Memiliki Permission",
            ], 404);
        }

        if ($columns->isEmpty()) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "Data Board Tidak Ada atau User Tidak Memiliki Permission",
            ], 404);
        }

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Data Columns",
            "data" => $columns
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Board $board)
    {

        $validator =  Validator::make($request->all(), [
            "name" => "required",
            "position" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth()->user();

        if (!$user) {
            if (!$user) {
                return response()->json([
                    "status" => 400,
                    "success" => false,
                    "message" => "User Belum Login, Tidak Dapat Mengakses Board",
                ]);
            }
        }

        $userPermission = $user->permission()->where('board_id', $board->id)->where('manage_board', true)->exists();

        if (!$userPermission) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Tidak Memiliki Akses Untuk Menambah Column',
            ], 400);
        }


        $column = column::create([
            'name' => $request->name,
            'position' => $request->position,
            'board_id' => $board->id,
        ]);

        $board->touch();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => ' Column Berhasil Ditambahakn ',
            'data' => $column,
        ], 200);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, column $column)
    {

        Log::info('Request data: ', $request->all());
        $validator =  Validator::make($request->all(), [
            "name" => "required",
            "board_id" => "required|exists:boards,id",
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth()->user();

        if (!$user) {
            if (!$user) {
                return response()->json([
                    "status" => 400,
                    "success" => false,
                    "message" => "User Belum Login, Tidak Dapat Mengakses Board",
                ]);
            }
        }


        $userPermission = $user->permission()->where('board_id', $request->board_id)->where('manage_board', true)->exists();

        if (!$userPermission) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Tidak Memiliki Akses Untuk Menambah Column',
            ], 400);
        }

        $column->name = $request->name;
        // $column->position = $request->position;
        $column->save();
        $column->board()->touch();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Column Berhasil Diupdate',
            'data' => $column,
        ], 200);
    }


    public function position(Request $request, column $column)
    {
        $validator =  Validator::make($request->all(), [
            "position" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth()->user();

        if (!$user) {
            if (!$user) {
                return response()->json([
                    "status" => 400,
                    "success" => false,
                    "message" => "User Belum Login",
                ]);
            }
        }

        $userPermission = $user->permission()->where('board_id', $column->board_id)->where('manage_board', 1)->exists();

        if (!$userPermission) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Tidak Memiliki Akses Untuk Memindahkan Column',
            ], 400);
        }

        $column->position = $request->position;
        $column->save();

        $column->board()->touch();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Column Berhasil Diupdate',
            'data' => $column,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(column $column)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "message" => "User Belum Login, User Tidak Dapat Menghapus Board",
            ]);
        }



        if (!$user->permission()->where('board_id', $column->board_id)->where('manage_board', 1)->exists()) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "User Tidak Memiliki Akses Untuk menghapus Column",
            ]);
        }

        $column->delete();

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Column Berhasil Dihapus",
        ]);
    }
}
