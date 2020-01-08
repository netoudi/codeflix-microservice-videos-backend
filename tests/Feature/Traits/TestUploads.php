<?php

namespace Tests\Feature\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

trait TestUploads
{
    protected function assertInvalidationFile($field, $extension, $maxSize, $rule, $ruleParams = [])
    {
        \Storage::fake();

        $routes = [
            ['method' => 'POST', 'route' => $this->routeStore()],
            ['method' => 'PUT', 'route' => $this->routeUpdate()],
        ];

        foreach ($routes as $route) {
            // file extension
            $file = UploadedFile::fake()->create("$field.1$extension");
            $response = $this->json($route['method'], $route['route'], [$field => $file]);

            $this->assertInvalidationFields($response, [$field], $rule, $ruleParams);

            // file max size
            $file = UploadedFile::fake()->create("$field.$extension")->size($maxSize + 1);
            $response = $this->json($route['method'], $route['route'], [$field => $file]);

            $this->assertInvalidationFields($response, [$field], 'max.file', ['max' => $maxSize]);
        }
    }

    protected function assertFilesExistsInStorage(Model $model, array $files)
    {
        /** @var UploadedFile $file */
        foreach ($files as $file) {
            \Storage::assertExists($model->relativeFilePath($file->hashName()));
        }
    }
}
