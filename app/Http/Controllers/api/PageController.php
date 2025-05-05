<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Conference;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    private function checkPermission(Request $request)
    {
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'editor') {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized');
        }
    }

    public function getPagesByConference(Request $request, $conference_id)
    {
        $this->checkPermission($request);

        $conference = Conference::find($conference_id);

        if (!$conference) {
            return response()->json(['message' => 'Conference not found'], Response::HTTP_NOT_FOUND);
        }

        $pages = Page::where('conference_id', $conference_id)->get();
        return response()->json($pages);
    }

    public function createPage(Request $request)
    {
        $this->checkPermission($request);

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
        $this->checkPermission($request);

        $page = Page::findOrFail($id);

        if ($page->conference_id != $conference_id) {
            return response()->json(['message' => 'Page does not belong to this conference'], Response::HTTP_FORBIDDEN);
        }

        $page->delete();

        return response()->json(['message' => 'Page deleted']);
    }

    public function updatePageContent(Request $request, $conferenceId, $id)
    {
        $this->checkPermission($request);

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

