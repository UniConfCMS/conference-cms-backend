<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Conference;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use HTMLPurifier;
use HTMLPurifier_Config;

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

    public function createPage(Request $request, $conference_id)
    {
        $this->checkPermission($request);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug,null,id,conference_id,' . $conference_id,
            'content' => 'required|string',
        ]);

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $data['content'] = $purifier->purify($data['content']);

        $data['conference_id'] = $conference_id;
        $data['created_by'] = $request->user()->id;
        $page = Page::create($data);
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

    public function updatePageContent(Request $request, $conference_id, $id)
    {
        $this->checkPermission($request);
        $page = Page::findOrFail($id);
        if ($page->conference_id != $conference_id) {
            return response()->json(['message' => 'Page does not belong to this conference'], Response::HTTP_FORBIDDEN);
        }
        $data = $request->validate([
            'content' => 'required|string', // support HTML from CKEditor 5
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|nullable|string|unique:pages,slug,' . $id . ',id,conference_id,' . $conference_id,
        ]);

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $data['content'] = $purifier->purify($data['content']);

        $page->update($data);
        return response()->json($page);
    }
}