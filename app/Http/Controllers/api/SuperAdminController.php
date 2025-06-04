<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\InviteUser;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    private function checkSAdmin(Request $request)
    {
        if ($request->user()->role !== 'super_admin') {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $this->checkSAdmin($request);

        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        Log::info('SuperAdmin store request:', $request->all());
        if ($request->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,editor',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        $user->notify(new InviteUser($request->role));
        return response()->json(['message' => 'User created successfully', 'user' => $user], Response::HTTP_CREATED);
    }

    public function show(Request $request, $id)
    {
        $this->checkSAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $this->checkSAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,editor',
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role ?? $user->role,
        ]);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy(Request $request, $id)
    {
        $this->checkSAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function assignRole(Request $request, $id)
    {
        $this->checkSAdmin($request);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot change own role'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'role' => 'required|in:admin,editor',
        ]);

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'User role updated successfully', 'user' => $user]);
    }
}
