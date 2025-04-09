<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Editor;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;


class EditorController extends Controller
{
    private function checkAdmin(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized');
        }
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

    
    public function deleteEditor(Request $request, $id)
    {
        $this->checkAdmin($request);

        $editor = Editor::find($id);

        if (!$editor) {
            return response()->json(['message' => 'Editor not found'], Response::HTTP_NOT_FOUND);
        }

        $editor->delete();

        return response()->json(['message' => 'Editor deleted successfully']);
    }
// Отримати редакторів для конференції
    public function getEditorsByConference(Request $request, $conference_id)
    {
        $this->checkAdmin($request);

        $editors = Editor::with('user')
            ->where('conference_id', $conference_id)
            ->get()
            ->pluck('user');

        return response()->json($editors);
    }
    
}