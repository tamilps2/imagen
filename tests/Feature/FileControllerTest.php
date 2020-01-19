<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    public $uploadsDirectory;

    public $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->uploadsDirectory = config('imager.job_upload_dir', 'jobs/uploads');
    }

    public function uploadsDataProvider()
    {
        $folder = Str::random();
        $folder2 = Str::random();

        return [
            [
                'image001.jpg',
                'file_folder' => $folder
            ],
            [
                'image002.jpg',
                'file_folder' => $folder
            ],
            [
                'image003.jpg',
                'file_folder' => $folder
            ],
            [
                'image001.jpg',
                'file_folder' => $folder2
            ],
            [
                'image004.jpg',
                'file_folder' => $folder2
            ],
            [
                'image005.jpg',
                'file_folder' => $folder2
            ],
        ];
    }

    public function test_upload_inputs_are_validated()
    {
        $response = $this->actingAs($this->user)->post('/api/file/upload', [
            'file' => 'test',
            'file_folder' => 'test/folede/test'
        ]);

        $response->assertSessionHasErrors(['file', 'file_folder']);
    }

    /**
     * @dataProvider uploadsDataProvider
     */
    public function test_can_upload_file_with_folder_structure($fileName, $folder)
    {
        Storage::fake($this->uploadsDirectory);

        $file = UploadedFile::fake()->image($fileName);

        $response = $this->actingAs($this->user)->post('/api/file/upload', [
            'file' => $file,
            'file_folder' => $folder,
        ]);

        $response->assertJsonFragment([
            'status' => true,
            'folder' => $folder,
            'folder_path' => $this->uploadsDirectory . '/' . $folder,
        ]);

        Storage::assertExists("{$this->uploadsDirectory}/{$folder}/{$fileName}");

        $this->assertDatabaseHas('jobs', [
            'folder' => $folder
        ]);

        $this->assertDatabaseHas('files', [
            'original_name' => $fileName
        ]);
    }

    public function test_file_folder_exists_check_endpoint()
    {
        $uploadsDirectory = config('imager.job_upload_dir', 'jobs/uploads');

        Storage::makeDirectory($uploadsDirectory . '/' . 'job002');
        Storage::deleteDirectory($uploadsDirectory . '/' . 'job001');

        $response = $this->actingAs($this->user)->json('post', '/api/file/check', [
            'file_folders' => ['job001', 'job002']
        ]);

        $response->assertExactJson([
            'exists' => true,
            'folders' => ['job002']
        ]);
    }

    public function test_can_remove_uploaded_file()
    {
        $fileName = 'image001.jpg';
        $folder = 'test001';

        Storage::fake($this->uploadsDirectory);

        $file = UploadedFile::fake()->image($fileName);

        $this->actingAs($this->user)->post('/api/file/upload', [
            'file' => $file,
            'file_folder' => $folder,
        ]);

        Storage::assertExists("{$this->uploadsDirectory}/{$folder}/{$fileName}");

        $this->assertDatabaseHas('files', [
            'original_name' => $fileName
        ]);

        $response = $this->post('/api/file/remove', [
            'file_name' => $fileName
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('files', [
            'original_name' => $fileName
        ]);

        Storage::assertMissing("{$this->uploadsDirectory}/{$folder}/{$fileName}");
    }
}
