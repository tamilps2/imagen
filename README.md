# Setup

### Create Symbolic Link

There should be a symbolic link for the following folders.

storage/logos -> public/logos

### Running FTP and Youtube Upload

For FTP and Youtube upload to work, the laravel queue should be running. Read following for more,

- [Robust background job processing](https://laravel.com/docs/queues).

## Youtube

To use the youtube, you need client credentials and paste it in the .env file.

The redirect endpoint for authorization is http://example.com/companies/authorize

GOOGLE_CLIENT_ID=

GOOGLE_CLIENT_SECRET=

## Imager Configs

For more config options, see config/imager.php

## Image Manipulation

By default, intervention image uses GD. You can change it to imageick from config/image.php

## Improvements

Currently the image manipulation is done by looping over jobs and presets. This way is slow
because everytime the original image should be loaded in memory and processed for each of the section.
If we could loop throught he files first and then the jobs and presets, that would improve the process time.