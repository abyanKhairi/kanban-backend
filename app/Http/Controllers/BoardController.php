<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    /**
     * column
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // if (!$user) {
        //     return response()->json([
        //         "status" => 401,
        //         "success" => false,
        //         "message" => "User Belum Login",
        //     ], 401);
        // }

        $boards = Board::whereHas('permission', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orderBy('updated_at', 'desc')->get();

        // if ($boards->isEmpty()) {
        //     return response()->json([
        //         "status" => 404,
        //         "success" => false,
        //         "message" => "Data Board Tidak Ada atau User Tidak Memiliki Permission",
        //     ], 404);
        // }

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Data Board",
            "data" => $boards
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            "name" => "required",
            "status" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth("")->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "massage" => "User Belum Login, User Tidak Dapat Menambahkan Board",
            ]);
        }

        $board = $user->board()->create([
            "name" => $request->name,
            "status" => $request->status,
        ]);

        if (!$board) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "massage" => "Gagal Menginputkan Data Board",
            ]);
        }

        $listTitle = ['Backlog', 'To Do', 'Doing', 'Done'];

        $posisi = 1;
        foreach ($listTitle as  $title) {
            $board->columns()->create([
                'name' => $title,
                'board_id' => $board->id,
                'position' =>  $posisi,
            ]);
            $posisi++;
        }

        $permission = Permission::create([
            "user_id" => $user->id,
            "board_id" => $board->id,
            "edit_cards" => true,
            "delete_cards" => true,
            "add_cards" => true,
            "add_members" => true,
            "manage_board" => true,
        ]);

        return response()->json([
            "status" => 200,
            "success" => true,
            "data" => $board,
            "permission" => $permission,
        ]);
    }




    /**
     * Display the specified resource.
     */
    public function show(Board $board)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "message" => "User Belum Login, Tidak Dapat Mengakses Board",
            ]);
        }

        $boards = Board::with('columns')->where('id', $board->id)->whereHas('permission', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if (!$boards) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "Data Board Tidak Ada atau User Tidak Memiliki Permission",
            ], 404);
        }

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Data Board",
            "data" => $boards
        ], 200);
    }


    public function showFourBoard()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    "status" => 400,
                    "success" => false,
                    "message" => "User Belum Login",
                ], 400);
            }

            $boards = Board::whereHas('permission', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->orderBy('updated_at', 'desc')->limit(4)->get();

            if ($boards->isEmpty()) {
                return response()->json([
                    "status" => 404,
                    "success" => false,
                    "message" => "Data Board Tidak Ada atau User Tidak Memiliki Permission",
                ], 404);
            }

            return response()->json([
                "status" => 200,
                "success" => true,
                "message" => "Data Board",
                "data" => $boards
            ], 200);
        } catch (\Exception $e) { // Catch the exception properly
            return response()->json([
                "status" => 500,
                "success" => false,
                "message" => "Error: " . $e->getMessage(), // Return actual error message for debugging
            ], 500);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Board $board)
    {
        $validator =  Validator::make($request->all(), [
            "name" => "required",
            "status" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth("")->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "massage" => "User Belum Login, User Tidak Dapat Mengupdate Board",
            ], 400);
        }

        if (!$user->permission()->where('board_id', $board->id)->where('manage_board', 1)->exists()) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "User Tidak Memiliki Akses Untuk Mengubah Board",
            ]);
        }


        if (!$board) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "massage" => "Board Tidak Ditemukan",
            ], 404);
        }

        $board->name = $request->name;
        $board->status = $request->status;
        $board->save();

        return response()->json([
            "status" => 200,
            "success" => true,
            "massage" => "Data Board Berhasil Diupdate",
            "data" => $board,
        ], 200);
    }


    public function addMember(Board $board, Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 422);
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "message" => "User Belum Login, User Tidak Dapat Mengupdate Board",
            ], 400);
        }

        if (!$user->permission()->where('board_id', $board->id)->where('manage_board', 1)->exists()) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "User Tidak Memiliki Akses Untuk Menambahkan Anggota Lain",
            ]);
        }

        $member = User::where('email', $request->email)->first();

        if (!$member) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "User Tidak Ditemukan",
            ], 404);
        }

        $existingPermission = Permission::where('user_id', $member->id)
            ->where('board_id', $board->id)
            ->first();

        if ($existingPermission) {
            return response()->json([
                "status" => 409,
                "success" => false,
                "message" => "User sudah merupakan anggota board ini",
            ], 409);
        }

        $permission = Permission::create([
            "user_id" => $member->id,
            "board_id" => $board->id,
            "edit_cards" => false,
            "delete_cards" => false,
            "add_cards" => false,
            "add_members" => false,
            "manage_board" => false,
        ]);

        $board->touch();

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Member Berhasil Ditambahkan",
            "data" => $member,
        ], 200);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "status" => 400,
                "success" => false,
                "message" => "User Belum Login, User Tidak Dapat Menghapus Board",
            ]);
        }



        if (!$user->permission()->where('board_id', $board->id)->where('manage_board', 1)->exists() && $user->id !== $board->user_id) {
            return response()->json([
                "status" => 404,
                "success" => false,
                "message" => "User Tidak Memiliki Akses Untuk menghapus board",
            ]);
        }

        $board->delete();

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "Board Berhasil Dihapus",
        ]);
    }
}
