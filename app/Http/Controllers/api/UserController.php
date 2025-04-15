<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Editor;
use App\Models\User;
use App\Notifications\InviteUser;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function checkAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin($request);
        $users = User::all();
        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        $this->checkAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($user);
    }


    public function store(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:editor',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        $user->notify(new InviteUser($request->role));

        return response()->json(['message' => 'User created successfully', 'user' => $user], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user->role === 'super_admin' || ($user->role === 'admin' && $user->id !== $request->user()->id)) {
            return response()->json(['message' => 'Cannot edit super_admin or other admin users'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => 'sometimes|in:editor',
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
        $this->checkAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user->role === 'super_admin' || ($user->role === 'admin' && $user->id !== $request->user()->id)) {
            return response()->json(['message' => 'Cannot delete super_admin or other admin users'], Response::HTTP_FORBIDDEN);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function assignRole(Request $request, $id)
    {
        $this->checkAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return response()->json(['message' => 'Cannot assign role to super_admin or admin users'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'role' => 'required|in:editor',
        ]);

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'User role updated successfully', 'user' => $user]);
    }


}
