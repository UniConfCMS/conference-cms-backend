<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    private function checkPermission(Request $request)
    {
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'editor') {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized');
        }
    }

    public function uploadFile(Request $request, $conference_id, $page_id)
    {
        $this->checkPermission($request);
        
        $request->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,pdf,docx|max:10240', // max 10MB
        ]);

        $page = Page::findOrFail($page_id);
        if ($page->conference_id != $conference_id) {
            return response()->json(['message' => 'Page does not belong to this conference'], Response::HTTP_FORBIDDEN);
        }

        $urls = [];
        $files = $request->file('files', []);
        
        foreach ($files as $file) {
            $path = $file->store('files', 'public');
            $fileRecord = File::create([
                'page_id' => $page_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => $request->user()->id,
            ]);
            $urls[] = Storage::url($path);
        }

        // If uploading one file, return { location: "url" } for TinyMCE
        if (count($urls) === 1) {
            return response()->json(['location' => $urls[0]], Response::HTTP_CREATED);
        }

        return response()->json(['locations' => $urls], Response::HTTP_CREATED);
    }

    public function deleteFile(Request $request, $conference_id, $page_id, $file_id)
    {
        $this->checkPermission($request);
        
        $file = File::findOrFail($file_id);
        if ($file->page->conference_id != $conference_id || $file->page_id != $page_id) {
            return response()->json(['message' => 'File does not belong to this page'], Response::HTTP_FORBIDDEN);
        }
        
        Storage::disk('public')->delete($file->file_path);
        $file->delete();
        
        return response()->json(['message' => 'File deleted'], Response::HTTP_OK);
    }

    public function getFilesByPage(Request $request, $conference_id, $page_id)
    {
        $this->checkPermission($request);
        
        $page = Page::findOrFail($page_id);
        if ($page->conference_id != $conference_id) {
            return response()->json(['message' => 'Page does not belong to this conference'], Response::HTTP_FORBIDDEN);
        }
        
        $files = $page->files->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->file_name,
                'url' => Storage::url($file->file_path),
            ];
        });
        
        return response()->json($files, Response::HTTP_OK);
    }
}