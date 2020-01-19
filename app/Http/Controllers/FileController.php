<?php

namespace App\Http\Controllers;

use App\File;
use App\Job;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{

    public $uploadsDirectory;

    public function __construct()
    {
        $this->uploadsDirectory = config('imager.job_upload_dir', 'jobs/uploads');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image',
            'file_folder' => 'required|string|regex:/^[A-Za-z0-9_\-\s]+$/',
        ], [
            'file_folder.required' => 'File directory is required.',
            'file_folder.regex' => 'Invalid directory name provided.',
        ]);

        $folderPath = $this->uploadsDirectory . DIRECTORY_SEPARATOR . $request->get('file_folder');

        $job = $this->createOrResetJob($request->get('file_folder'));

        $filePath = Storage::putFileAs(
            $folderPath,
            $request->file('file'),
            $request->file('file')->getClientOriginalName()
        );

        if (Storage::exists($filePath)) {
            $file = $this->checkAndCreateFile($job, $request, $folderPath);

            return response()->json([
                'status' => true,
                'job_id' => $job->id,
                'file' => $file,
                'file_path' => $filePath,
                'folder_path' => $folderPath,
                'folder' => $request->get('file_folder')
            ]);
        } else {
            return response()->json([
                'status' => false,
            ]);
        }
    }

    /**
     * Check to see if this image is already preset and process
     * accordingly.
     *
     * @param $job
     * @param $request
     * @param $folderPath
     * @return mixed
     */
    private function checkAndCreateFile($job, $request, $folderPath)
    {
        $file = File::where('job_id', $job->id)
            ->where('original_name', $request->file('file')->getClientOriginalName())
            ->where('storage_path', $folderPath)
            ->first();

        if (!$file) {
            $file = File::create([
                'job_id' => $job->id,
                'name' => pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME),
                'extension' => $request->file('file')->getClientOriginalExtension(),
                'folder' => $request->get('file_folder'),
                'original_name' => $request->file('file')->getClientOriginalName(),
                'storage_path' => $folderPath,
                'full_path' => storage_path($folderPath)
            ]);
        }

        return $file;
    }

    /**
     * Create a job or reset it.
     *
     * @param $folder
     * @return mixed
     */
    private function createOrResetJob($folder)
    {
        $job = Job::where('folder', $folder)->first();

        if ($job) {
            $job->update([
                'is_processed' => false,
                'is_processing' => false,
                'has_errors' => false
            ]);
        } else {
            $job = Job::create([
                'title' => $folder,
                'folder' => $folder,
                'is_processed' => 0
            ]);
        }

        return $job;
    }

    public function check(Request $request)
    {
        $request->validate([
            'file_folders' => 'required|array|nullable'
        ]);

        $uploadsDirectory = config('imager.job_upload_dir', 'jobs/uploads');

        $exists = [];

        foreach ($request->file_folders as $folder) {
            if (Storage::exists($uploadsDirectory . DIRECTORY_SEPARATOR . $folder)) {
                $exists[] = $folder;
            }
        }

        if (count($exists) > 0) {
            return response()->json([
                'exists' => true,
                'folders' => $exists,
            ]);
        } else {
            return response()->json([
                'exists' => false,
                'folders' => []
            ]);
        }
    }

    public function remove(Request $request)
    {
        $request->validate([
            'file_name' => 'required'
        ]);

        $file = File::where('original_name', $request->file_name)->first();

        if ($file) {
            if (Storage::delete($file->getStoragePath())) {
                $file->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'File has been removed successfully.'
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'File has been removed successfully.'
        ]);
    }
}
