<?php

namespace Tests\Feature;

use App\Company;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    public function test_shows_all_company_index_page()
    {
        $this->withoutExceptionHandling();

        $companies = Company::all();
        $response = $this->actingAs($this->user)->get('/companies');

        $response->assertStatus(200);
        $response->assertViewIs('companies.index');
        $response->assertViewHas('companies', $companies);
    }

    public function test_show_authorize_only_when_access_token_is_not_set()
    {
        $this->withoutExceptionHandling();

        Config::set('services.google.client_id', 'test');
        Config::set('services.google.client_secret', 'test');

        $company = factory(Company::class)->create([
            'google_access_token' => '{"access_token":"AsdfSDFASDFASDFSDF"}',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/companies/edit/' . $company->id);

        $response->assertDontSee('Authorize Youtube API');
        $response->assertSee('Revoke Youtube Access');

        $company = factory(Company::class)->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('edit_company', ['company' => $company]));

        $response->assertSee('Authorize Youtube API');
        $response->assertDontSee('Revoke Youtube Access');
    }

    public function test_create_company()
    {
        Storage::fake('logos');

        $name = $this->faker->company;
        $file = UploadedFile::fake()->image(Str::random() . '.png', 100, 100);

        $response = $this->actingAs($this->user)->post('/companies/store', [
            'name' => $name,
            'logo' => $file
        ]);

        $response->assertSessionHas('status', 'Company created successfully');
        $response->assertRedirect('/companies');

        $this->assertDatabaseHas('companies', [
            'name' => $name,
        ]);

        Storage::assertExists('logos/' . $file->hashName());
    }
}
