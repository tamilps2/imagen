<?php

namespace App\Imager;

interface GeneratorInterface
{

    /**
     * Process the job with the list of presets passed
     *
     * @param $data
     * @return mixed
     * @throws GeneratorException
     */
    public function process($data = []);

}