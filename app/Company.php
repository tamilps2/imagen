<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    protected $fillable = [
        'name', 'logo',
        'ftp_host', 'ftp_username', 'ftp_password', 'ftp_upload_path',
    ];

    protected $casts = [
        'google_access_token' => 'array'
    ];

    public function hasValidAccessToken()
    {
        if (
            !empty($this->google_access_token) &&
            strpos($this->getOriginal('google_access_token'), 'access_token') !== false
        ) {
            return true;
        }

        return false;
    }

    public function getAccessToken($fullJson = false)
    {
        if (
            !empty($this->google_access_token) &&
            isset($this->google_access_token['access_token'])
        ) {
            return ($fullJson ?
                $this->getOriginal('google_access_token') :
                $this->google_access_token['access_token']
            );
        }

        return '';
    }

    /**
     * Get the google client for the company client details
     *
     * @return \Google_Client|null
     * @throws \Exception
     */
    public function getGoogleClient()
    {
        $client_id = config('services.google.client_id', '');
        $client_secret = config('services.google.client_secret', '');
        $scopes = config('services.google.youtube.scopes', '');

        if (empty($client_id) && empty($client_secret)) {
            throw new \Exception('Google client credentials not configured.');
        }

        $client = new \Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setScopes($scopes);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setRedirectUri(url('companies/authorize'));
        $client->setState('company_id=' . $this->id);

        if (!empty($this->getAccessToken(true))) {
            $client->setAccessToken($this->getAccessToken(true));
        }

        return $client;
    }

    public function companyLogo()
    {
        $logoDirectory = config('imager.logos_dir', 'logos');

        return Storage::url($logoDirectory . DIRECTORY_SEPARATOR . $this->logo);
    }

    public function getCompanyLogo()
    {
        $logoDirectory = config('imager.logos_dir', 'logos');

        return Storage::get($logoDirectory . DIRECTORY_SEPARATOR . $this->logo);
    }
}
