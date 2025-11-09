<?php

namespace App\Http\Controllers;
use App\Jobs\ProductImportJob;
use App\Models\Upload;
use Illuminate\Http\Request;
use App\Http\Resources\UploadResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class UploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt|max:40960',
            ]);

            $file = $request->file('file');

            // Calculate the hash of file content (to prevent duplicate uploads)
            $fileHash = md5_file($file->getRealPath());
            $originalName = $file->getClientOriginalName();

            // Check for existing file (idempotency)
            $existingUpload = Upload::where('file_hash', $fileHash)->first();
            if ($existingUpload) {
                return response()->json([
                    'success' => true,
                    'message' => 'Duplicate file detected, skipping re-upload.',
                    'data' => new UploadResource($existingUpload),
                ], 200);
            }

            // Clean non-UTF-8 characters
            $content = file_get_contents($file->getRealPath());
            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            }

            // Store the file (after cleaning encoding)
            $fileName = time() . '_' . $originalName;
            $storagePath = 'uploads/' . $fileName;
            Storage::disk('public')->put($storagePath, $content);

            Log::info('$fileHash = ' . $fileHash);

            // Create database record
            $upload = Upload::create([
                'file_name' => $fileName,
                'file_hash' => $fileHash,
                'status' => 'pending',
            ]);

            // Dispatch background job
            ProductImportJob::dispatch($upload);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully and is being processed.',
                'data' => new UploadResource($upload),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function status()
    {
        try {
            $uploads = Upload::latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Upload list retrieved successfully',
                'data' => UploadResource::collection($uploads),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load uploads: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }

    }
}
