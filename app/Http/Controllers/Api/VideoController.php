<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;

class VideoController extends BasicCrudController
{
    /**
     * @var array
     */
    private $rules = [];

    protected function model(): string
    {
        return Video::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }
}
