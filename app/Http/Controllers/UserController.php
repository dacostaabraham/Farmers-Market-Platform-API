<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /** List users — admin sees supervisors, supervisor sees their operators */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $users = User::where('role', 'supervisor')->get();
        } elseif ($user->isSupervisor()) {
            $users = User::where('role', 'operator')
                         ->where('supervisor_id', $user->id)
                         ->get();
        } else {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $users]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $authUser = $request->user();
        $role     = $authUser->isAdmin() ? 'supervisor' : 'operator';

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'role'          => $role,
            'supervisor_id' => $authUser->isSupervisor() ? $authUser->id : null,
        ]);

        return response()->json(['message' => 'User created.', 'data' => $user], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => $user->load('supervisor')]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return response()->json(['message' => 'User updated.', 'data' => $user]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->update(['is_active' => false]);
        return response()->json(['message' => 'User deactivated.']);
    }
}
