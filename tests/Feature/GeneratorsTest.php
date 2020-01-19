<?php

namespace Tests\Feature;

use App\Company;
use App\Imager\GifGenerator;
use App\Imager\ImageGenerator;
use App\Imager\VideoGenerator;
use App\Imager\VideoInfo;
use App\Jobs\UploadToServer;
use App\Jobs\UploadToYoutube;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class GeneratorsTest extends TestCase
{
    use RefreshDatabase, GenerationTestHelpers;

    public $uploadsDirectory;

    public $outputDirectory;

    public $user;

    public $company;

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

    public function test_small_image_generation()
    {
        Queue::fake();

        $folder = Str::random();
        $preset = $this->createImagePreset('sm');

        $job = $this->createJob($folder);

        $this->createJobFile($job, 'test-img-001.jpg');
        $this->createJobFile($job, 'test-img-002.jpg');
        $this->createJobFile($job, 'test-img-003.jpg');
        $this->createJobFile($job, 'test-img-004.jpg');

        (new ImageGenerator($job, collect([$preset]), $job->files, 'sm'))->process();

        $this->assertDirectoryExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath()
        ])));

        $this->assertDirectoryExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath(),
            $preset->getSectionDirectoryName('sm')
        ])));

        $this->assertEquals($job->files->count(), count(Storage::files(implode(DIRECTORY_SEPARATOR, [
            $job->getOutputPath(),
            $preset->getSectionDirectoryName('sm')
        ]))));

        Queue::assertPushed(UploadToServer::class);
        Queue::assertNotPushed(UploadToYoutube::class);

        Storage::deleteDirectory($job->getOutputPath());
        Storage::deleteDirectory($job->getUploadPath());
    }

    public function test_gif_generator()
    {
        Queue::fake();

        $folder = Str::random();
        $preset = $this->createGifPreset();

        $job = $this->createJob($folder);

        $this->createJobFile($job, 'test-img-001.jpg');
        $this->createJobFile($job, 'test-img-002.jpg');
        $this->createJobFile($job, 'test-img-003.jpg');
        $this->createJobFile($job, 'test-img-004.jpg');

        (new GifGenerator($job, collect([$preset]), $job->files, 'gif'))->process();

        $this->assertDirectoryExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath()
        ])));

        $this->assertDirectoryExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath()
        ])));

        $this->assertFileExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath(),
            'gif.gif'
        ])));

        Queue::assertPushed(UploadToServer::class);
        Queue::assertNotPushed(UploadToYoutube::class);

        Storage::deleteDirectory($job->getOutputPath());
        Storage::deleteDirectory($job->getUploadPath());
    }

    public function test_video_generator()
    {
        Queue::fake();

        $folder = Str::random();
        $preset = $this->createVideoPreset();

        $job = $this->createJob($folder);

        $this->createJobFile($job, 'test-img-001.jpg');
        $this->createJobFile($job, 'test-img-002.jpg');
        $this->createJobFile($job, 'test-img-003.jpg');
        $this->createJobFile($job, 'test-img-004.jpg');

        (new VideoGenerator($job, collect([$preset]), $job->files, 'video'))->process([
            'video_info' => new VideoInfo('', '', 'private', '')
        ]);

        $this->assertDirectoryExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath()
        ])));

        $this->assertDirectoryExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath()
        ])));

        $this->assertFileExists(storage_path(implode(DIRECTORY_SEPARATOR, [
            'app',
            $job->getOutputPath(),
            'video.mp4'
        ])));

        Queue::assertPushed(UploadToServer::class);
        Queue::assertPushed(UploadToYoutube::class);

        Storage::deleteDirectory($job->getOutputPath());
        Storage::deleteDirectory($job->getUploadPath());
    }

    protected function tearDown(): void
    {
        Storage::delete('logos/' . $this->company->logo);

        parent::tearDown();
    }
}
