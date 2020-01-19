<?php

namespace Tests\Feature;

use App\Company;
use App\Job;
use App\Preset;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class JobControllerTest extends TestCase
{
    use RefreshDatabase, GenerationTestHelpers;

    public $uploadsDirectory;

    public $outputDirectory;

    public $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $logo = UploadedFile::fake()->image('logo.png', 100, 100);
        Storage::put('logos', $logo);
        $this->company = factory(Company::class)->create([
            'logo' => $logo->hashName()
        ]);

        $this->uploadsDirectory = config('imager.job_upload_dir', 'jobs/uploads');
        $this->outputDirectory = config('imager.job_output_dir', 'jobs/output');
    }

    public function test_create_job_page_renders_correct_views()
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/jobs/create');

        $response->assertViewIs('jobs.create');
    }

    public function test_start_process_renders_correct_view_and_data()
    {
        factory(Job::class)->create();
        factory(Preset::class)->create();

        $response = $this
            ->actingAs($this->user)
            ->get('/jobs/process?jobs=1');

        $response->assertViewIs('jobs.process');
        $response->assertViewHas('jobs', Job::all());
        $response->assertViewHas('presets', Preset::all());
        $response->assertViewHas('selectedJobs', Job::whereId(1)->get());
    }

    private function prepareJobs()
    {
        $jobs = [];
        Storage::fake($this->uploadsDirectory);

        $job1 = $this->createJob(Str::random());

        $this->createJobFile($job1, 'image-001.jpg');
        $this->createJobFile($job1, 'image-002.jpg');
        $this->createJobFile($job1, 'image-003.jpg');
        $this->createJobFile($job1, 'image-006.jpg');

        $job2 = $this->createJob(Str::random());

        $this->createJobFile($job2, 'image-004.jpg');
        $this->createJobFile($job2, 'image-005.jpg');

        return [$job1, $job2];
    }

    private function preparePresets()
    {
        $presets = [];

        $presets[] = $this->createImagePreset('sm');
        $presets[] = $this->createImagePreset('lg');
        $presets[] = $this->createGifPreset(3);
        $presets[] = $this->createVideoPreset(60);

        return $presets;
    }

    public function test_job_process_generation()
    {
        Storage::fake('jobs');
        Queue::fake();

        $this->withoutExceptionHandling();

        $jobs = $this->prepareJobs();
        $presets = $this->preparePresets();

        $jobIds = [];
        foreach($jobs as $job) {
            $jobIds[] = $job->id;
        }

        $presetIds = [];
        foreach ($presets as $preset) {
            $presetIds[] = $preset->id;
        }

        $response = $this->actingAs($this->user)->post('/jobs/process', [
            'jobs' => implode(',', $jobIds),
            'presets' => implode(',', $presetIds)
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'status' => true,
        ]);

        foreach ($jobs as $job) {
            $this->assertTrue($job->fresh()->is_processed);
            $this->assertFalse($job->fresh()->has_errors);
        }
    }

    protected function tearDown(): void
    {
        Storage::delete('logos/' . $this->company->logo);

        parent::tearDown();
    }
}
