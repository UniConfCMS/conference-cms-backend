<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ConferenceController extends Controller
{
    private function checkAdmin(Request $request)
    {
        if (!$request->user()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Authentication required');
        }

        if ($request->user()->role !== 'admin') {
            abort(Response::HTTP_FORBIDDEN, 'Admin access required');
        }
    }

    public function getAllConferences(Request $request)
    {
        $conferences = Conference::all();
        return response()->json($conferences);
    }

    public function createConference(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'title' => 'required|string|max:255|unique:conferences,title',
            'year' => 'required|integer',
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
            'title' => 'required|string|max:255|unique:conferences,title,' . $id,
            'year' => 'required|integer',
        ]);

        $conference->title = $request->title;
        $conference->year = $request->year;

        $conference->save();

        return response()->json([
            'message' => 'Conference updated successfully',
            'conference' => $conference,
        ]);
    }
    public function getConference(Request $request, $id)
    {
        $conference = Conference::find($id);

        if (!$conference) {
            return response()->json(['message' => 'Conference not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($conference);
    }

}
