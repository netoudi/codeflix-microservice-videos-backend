<?php

namespace Tests\Feature\Stubs\Models\Traits;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;

class UploadFilesStub extends Model
{
    use UploadFiles;

    protected function uploadDir()
    {
        return '1';
    }
}
