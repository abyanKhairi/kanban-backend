<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{

    public function getPermissions(Request $request, $board_id)
    {
        $userId = $request->user()->id;
        $permissions = Permission::where('board_id', $board_id)
            ->where('user_id', $userId)
            ->first();

        return response()->json($permissions);
    }



    public function show(Request $request, $board_id)
    {
        $user = auth("")->user();

        $hasManageBoardPermission = Permission::where('user_id', $user->id)
            ->where('board_id', $board_id)
            ->where('manage_board', true)
            ->exists();

        if (!$hasManageBoardPermission) {
            return response()->json([
                'status' => 403,
                'success' => false,
                'message' => 'Tidak Ada Access',
            ], 403);
        }

        $permissions = Permission::where('board_id', $board_id)
            ->with('user')
            ->get();

        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $permissions,
        ]);
    }


    public function ShowOnBoard(Request $request, $board_id)
    {
        $user = auth("")->user();

        $hasManageBoardPermission = Permission::where('user_id', $user->id)
            ->where('board_id', $board_id)
            ->exists();

        if (!$hasManageBoardPermission) {
            return response()->json([
                'status' => 403,
                'success' => false,
                'message' => 'Tidak Ada Access',
            ], 403);
        }

        $permissions = Permission::where('board_id', $board_id)
            ->with('user')
            ->get();

        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $permissions,
        ]);
    }

    public function deleteUser(Request $request, $board_id, $permission_id)
    {
        $user = $request->user();

        $hasManageBoardPermission = Permission::where('user_id', $user->id)
            ->where('board_id', $board_id)
            ->where('manage_board', true)
            ->exists();

        if (!$hasManageBoardPermission) {
            return response()->json([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied: You do not have permission to manage this board.',
            ], 403);
        }

        $permission = Permission::where('id', $permission_id)->first();

        if ($permission) {
            $permission->delete();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'User successfully removed from the board.',
            ]);
        }

        return response()->json([
            'status' => 404,
            'success' => false,
            'message' => 'Permission not found or already removed.',
        ], 404);
    }

    // public function CollaboratorPermission($board_id, $permission_id)
    // {
    //     $permission = Permission::where('board_id', $board_id)
    //         ->where('id', $permission_id)
    //         ->first();

    //     if (!$permission) {
    //         return response()->json([
    //             'status' => 404,
    //             'success' => false,
    //             'message' => 'Permission not found.',
    //         ], 404);
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'success' => true,
    //         'data' => [
    //             'add_cards' => $permission->add_cards,
    //             'delete_cards' => $permission->delete_cards,
    //             'edit_cards' => $permission->edit_cards,
    //             'manage_board' => $permission->manage_board,
    //             "add_members" => $permission->add_members,
    //         ],
    //     ], 200);
    // }

    public function updatePermissions(Request $request, Permission $permission)
    {
        $user = auth()->user();

        $hasManageBoardPermission = Permission::where('user_id', $user->id)
            ->where('board_id', $permission->board_id)
            ->where('manage_board', true)
            ->exists();

        if (!$hasManageBoardPermission) {
            return response()->json([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. You do not have permission to update these settings.',
            ], 403);
        }

        $validatedData = $request->validate([
            'edit_cards' => 'boolean',
            'delete_cards' => 'boolean',
            'add_cards' => 'boolean',
            'add_members' => 'boolean',
            'manage_board' => 'boolean',
        ]);

        $permission->update($validatedData);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Permissions successfully updated.',
            'data' => $permission,
        ]);
    }
}
