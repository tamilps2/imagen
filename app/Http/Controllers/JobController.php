<?php

namespace App\Http\Controllers;

use App\Imager\VideoGenerator;
use App\Imager\VideoInfo;
use App\Job;
use App\Preset;
use App\Imager\GeneratorException;
use App\Imager\GifGenerator;
use App\Imager\ImageGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{

    public function process(Request $request, VideoInfo $videoInfo)
    {
        $jobs = Job::whereIn('id', explode(',', $request->get('jobs')))->get();
        $presets = Preset::whereIn('id', explode(',', $request->get('presets')))->get();

        $jobs->each->update([
            'progress' => 0,
            'section_progress' => 0,
            'progress_message' => ''
        ]);

        if (!empty($jobs) && !empty($presets)) {
            foreach ($jobs as $job) {
                $job->fresh()->update([
                    'progress' => 0,
                    'progress_message' => sprintf('Beginning process for job - "%s"', $job->folder)
                ]);

                try {
                    (new ImageGenerator($job, $presets, $job->files, 'sm'))->process();
                    (new ImageGenerator($job, $presets, $job->files, 'lg'))->process();
                    (new GifGenerator($job, $presets, $job->files, 'gif'))->process();
                    (new VideoGenerator($job, $presets, $job->files, 'video'))->process([
                        'video_info' => $videoInfo
                    ]);
                } catch (GeneratorException $e) {
                    $job->update([
                        'is_processing' => false,
                        'progress' => 0,
                        'has_errors' => true
                    ]);

                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage()
                    ], 422);
                }

                $job->update([
                    'is_processing' => false,
                    'is_processed' => true,
                    'progress' => 100,
                    'progress_message' => sprintf('Finished processing all presets for job - "%s"', $job->folder),
                    'processed_at' => Carbon::now(),
                    'has_errors' => false
                ]);
            }

            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false
        ]);
    }

    public function progress(Request $request)
    {
        $progress = Job::selectRaw('AVG(`progress`) as progress, AVG(`section_progress`) as section_progress')
            ->whereIn('id', explode(',', $request->get('jobs')))
            ->first();

        $job = Job::whereIn('id', explode(',', $request->get('jobs')))
            ->orderBy('updated_at', 'desc')
            ->first();

        return response()->json([
            'progress' => $progress->progress,
            'section_progress' => $progress->section_progress,
            'message' => $job->progress_message
        ]);
    }

    public function startProcess(Request $request, VideoInfo $videoInfo)
    {
        $selectedJobs = Job::whereIn('id', explode(',', $request->get('jobs')))->get();

        // If this is the first time job is created or clicking on process again.
        $selectedJobs->each(function ($job) {
            $job->update(['is_processed' => 0]);
        });

        $jobs = Job::where('is_processed', 0)->where('is_processing', 0)->where('has_errors', 0)->get();
        $presets = Preset::all();

        return view('jobs.process', [
            'selectedJobs' => $selectedJobs,
            'jobs' => $jobs,
            'presets' => $presets,
            'metaInfo' => $videoInfo
        ]);
    }

    public function index()
    {
        $jobs = Job::all();

        return view('jobs.index', [
            'jobs' => $jobs
        ]);
    }

    public function create()
    {
        return view('jobs.create');
    }

    public function show(Job $job)
    {
        return view('jobs.show', compact('job'));
    }

    public function destroy(Job $job)
    {
        $job->presets()->detach();
        $job->files()->delete();

        if ($job->delete()) {
            if (Storage::exists($job->getUploadPath())) {
                Storage::deleteDirectory($job->getUploadPath());
            }

            if (Storage::exists($job->getOutputPath())) {
                Storage::deleteDirectory($job->getOutputPath());
            }
        }

        return redirect(route('jobs'));
    }

    public function store(Request $request)
    {
        $inputs = $request->validate([
            'title' => 'required',
            'preset_id' => 'required|exists:presets,id',
            'directory_prefix' => 'sometimes|alpha_num'
        ], [
            'preset_id.required' => 'Preset is required.'
        ]);

        $job = Job::create([
            'title' => $inputs['title'],
            'preset_id' => $inputs['preset_id'],
            'directory_prefix' => $inputs['directory_prefix'] ?? 'job'
        ]);

        return redirect(route('view_job', ['job' => $job]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Job $job
     * @return \Illuminate\Http\Response
     */
    public function edit(Job $job)
    {
        $presets = Preset::all();

        return view('jobs.edit', compact('job', 'presets'));
    }

    public function update(Request $request, Job $job)
    {
        $inputs = $request->validate([
            'title' => 'required',
            'preset_id' => 'required|exists:presets,id',
            'directory_prefix' => 'sometimes|alpha_num'
        ], [
            'preset_id.required' => 'Preset is required.'
        ]);

        $job->update([
            'title' => $inputs['title'],
            'preset_id' => $inputs['preset_id'],
            'directory_prefix' => $inputs['directory_prefix'] ?? 'job'
        ]);

        return redirect(route('view_job', ['job' => $job]));
    }
}
