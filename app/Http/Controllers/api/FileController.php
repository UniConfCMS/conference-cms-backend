<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\File;
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

    public function uploadFile(Request $request)
    {
        $this->checkPermission($request);
        
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,pdf,docx|max:10240', // max 10MB
        ]);

        $file = $request->file('file', null);
        
        $path = $file->store('file', 'public');
        File::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => $request->user()->id,
        ]);
        $url = Storage::url($path);

        return response()->json(['locations' => $url], Response::HTTP_CREATED);
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
}