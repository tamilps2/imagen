<?php

namespace App\Providers;

use App\Imager\VideoInfo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Imager\VideoInfo', function ($app) {
            $metaInfo = session()->get('meta_info', null);

            if (empty($metaInfo)) {
                $request = request();

                if ($request->routeIs('process') && $request->isMethod('POST')) {
                    session()->put('meta_info', $request->only(['title', 'description', 'visibility', 'tags']));

                    return new VideoInfo(
                        $request->get('title', ''),
                        $request->get('description', ''),
                        $request->get('visibility', 'private'),
                        $request->get('tags', '')
                    );
                }

                return new VideoInfo('', '', 'private', '');
            }

            return new VideoInfo($metaInfo['title'], $metaInfo['description'], $metaInfo['visibility'], $metaInfo['tags']);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
