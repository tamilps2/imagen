<?php

namespace App\Jobs;

use App\Company;
use App\Imager\VideoInfo;
use App\YoutubeVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;
use Google_Http_MediaFileUpload;

class UploadToYoutube implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $company;

    public $videoPath;

    public $videoInfo;

    /**
     * UploadToYoutube constructor.
     *
     * @param Company $company
     * @param $videoPath
     * @param VideoInfo $videoInfo
     */
    public function __construct(Company $company, $videoPath, VideoInfo $videoInfo)
    {
        $this->company = $company;
        $this->videoPath = $videoPath;
        $this->videoInfo = $videoInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if (!file_exists($this->videoPath)) {
            $this->fail(new \Exception('Video file does not exist at path: "' . $this->videoPath . '"'));
            return;
        }

        $client = $this->company->getGoogleClient();
        $accessToken = $this->company->getAccessToken(true);

        $client->setAccessToken($accessToken);

        # Check if access token is expired
        if ($client->isAccessTokenExpired()) {
            if (array_key_exists('refresh_token', $this->company->google_access_token)) {
                // Refresh the access token
                $client->refreshToken($this->company->google_access_token['refresh_token']);

                // Save the access token
                $this->company->google_access_token = $client->getAccessToken();
                $this->company->save();
            }
        }

        try {
            // Define service object for making API requests.
            $service = new Google_Service_YouTube($client);

            // Define the $video object, which will be uploaded as the request body.
            $video = new Google_Service_YouTube_Video();

            // Add 'snippet' object to the $video object.
            $videoSnippet = new Google_Service_YouTube_VideoSnippet();
            $videoSnippet->setCategoryId('22');
            $videoSnippet->setTitle($this->videoInfo->title ?? $this->company->name);
            $videoSnippet->setDescription($this->videoInfo->description ?? $this->company->name);
            $videoSnippet->setTags($this->videoInfo->tags ?? $this->company->name);
            $video->setSnippet($videoSnippet);

            // Add 'status' object to the $video object.
            $videoStatus = new Google_Service_YouTube_VideoStatus();
            $videoStatus->setPrivacyStatus(($this->videoInfo->visibility ?? 'private'));
            $video->setStatus($videoStatus);

            $chunkSizeBytes = 1 * 1024 * 1024;

            // Call the API with the media upload, defer so it doesn't immediately return.
            $client->setDefer(true);

            $videoRequest = $service->videos->insert('status,snippet', $video);

            // Create a media file upload to represent our upload process.
            $media = new Google_Http_MediaFileUpload(
                $client,
                $videoRequest,
                'video/*',
                null,
                true,
                $chunkSizeBytes
            );

            // Set the file size
            $media->setFileSize(filesize($this->videoPath));

            // Read the file and upload in chunks
            $status = false;
            $handle = fopen($this->videoPath, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);

            $client->setDefer(false);

            YoutubeVideo::create([
                'video_id' => $status['id'],
                'title' => $status['snippet']['title'],
                'description' => $status['snippet']['description'],
                'url' => sprintf('https://youtu.be/%s', $status['id']),
                'snippet' => @json_encode($status['snippet'])
            ]);
        } catch (\Google_Service_Exception $gse) {
            $this->fail($gse);
        } catch (\Google_Exception $ge) {
            $this->fail($ge);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
}
