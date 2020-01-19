<?php

namespace App\Jobs;

use App\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshAccessTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $client = $company->getGoogleClient();

            if (!empty($client)) {
                if ($client->isAccessTokenExpired()) {
                    if (array_key_exists('refresh_token', $company->google_access_token)) {
                        // Refresh the access token
                        $client->refreshToken($company->google_access_token['refresh_token']);

                        // Save the access token
                        $company->google_access_token = $client->getAccessToken();
                        $company->save();
                    }
                }
            }
        }
    }
}
