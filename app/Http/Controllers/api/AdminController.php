<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conference;
use App\Models\Editor;
use App\Models\Page;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
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

    public function store(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:editor',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], Response::HTTP_CREATED);
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

    public function getAllConferences(Request $request)
    {
        $this->checkAdmin($request);
    
        $conferences = Conference::all();
        return response()->json($conferences);
    }
    

    public function createConference(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'required|integer|unique:conferences,year',
        ]);

        $conference = Conference::create([
            'title' => $request->title,
            'year' => $request->year,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($conference, Response::HTTP_CREATED);
    }

    public function deleteConference(Request $request, $id)
    {
        $this->checkAdmin($request);

        $conference = Conference::findOrFail($id);
        $conference->delete();

        return response()->json(['message' => 'Conference deleted successfully']);
    }

    public function updateConference(Request $request, $id)
    {
        $this->checkAdmin($request);

        $conference = Conference::find($id);

        if (!$conference) {
            return response()->json(['message' => 'Conference not found'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'year' => 'sometimes|integer|unique:conferences,year,' . $id,
        ]);

        
        $conference->title = $request->title ?? $conference->title;
        $conference->year = $request->year ?? $conference->year;

        $conference->save();

        return response()->json(['message' => 'Conference updated successfully', 'conference' => $conference]);
    }

    public function assignEditor(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'conference_id' => 'required|exists:conferences,id',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->role !== 'editor') {
            return response()->json(['message' => 'User is not an editor'], Response::HTTP_FORBIDDEN);
        }

        $existing = Editor::where('user_id', $request->user_id)
            ->where('conference_id', $request->conference_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Editor already assigned to this conference'], Response::HTTP_CONFLICT);
        }

        $assignment = Editor::create([
            'user_id' => $request->user_id,
            'conference_id' => $request->conference_id,
            'assigned_by' => $request->user()->id,
        ]);

        return response()->json($assignment, Response::HTTP_CREATED);
    }

    public function getPagesByConference(Request $request, $conference_id)
    {
        $this->checkAdmin($request);
        
        $conference = Conference::find($conference_id);
        
        if (!$conference) {
            return response()->json(['message' => 'Conference not found'], Response::HTTP_NOT_FOUND);
        }

        $pages = Page::where('conference_id', $conference_id)->get();
        return response()->json($pages);
    }


    public function createPage(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'conference_id' => 'required|exists:conferences,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug,NULL,id,conference_id,' . $request->conference_id,
            'content' => 'required|string',
        ]);
    
        
        $slug = $request->slug ?? Str::slug($request->title);
    
        
        $page = Page::create([
            'conference_id' => $request->conference_id,
            'title' => $request->title,
            'slug' => $slug,  
            'content' => $request->content,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($page, Response::HTTP_CREATED);
    }

    public function deletePage(Request $request, $conference_id, $id)
    {
        $this->checkAdmin($request);

        
        $page = Page::findOrFail($id);

        if ($page->conference_id != $conference_id) {
            return response()->json(['message' => 'Page does not belong to this conference'], Response::HTTP_FORBIDDEN);
        }

        
        $page->delete();

        return response()->json(['message' => 'Page deleted']);
    }



    public function updatePageContent(Request $request, $conferenceId, $id)
    {
        $this->checkAdmin($request);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'required|string',
            'conference_id' => 'required|exists:conferences,id',
            'created_by' => 'sometimes|exists:users,id',
        ]);

        $page = Page::findOrFail($id);

        if ($page->conference_id != $conferenceId) {
            return response()->json(['message' => 'Page does not belong to this conference'], Response::HTTP_FORBIDDEN);
        }

        $page->content = $request->content;
        $page->title = $request->title ?? $page->title;
        $page->save();

        return response()->json(['message' => 'Page content updated', 'page' => $page]);
    }
}
