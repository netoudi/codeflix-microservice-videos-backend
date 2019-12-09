<?php

namespace Tests\Feature\Traits;

use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves
{
    abstract protected function model(): string;

    protected function assertStore(array $sendData, array $testDatabase, array $testJsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->postJson($this->routeStore(), $sendData);

        if ($response->status() !== 201) {
            throw new \Exception("Response status must be 201, given {$response->status()}:\n{$response->content()}");
        }

        $model = $this->model();
        $table = (new $model())->getTable();
        $this->assertDatabaseHas($table, $testDatabase + ['id' => $response->json('id')]);

        $testResponse = $testJsonData ?? $testDatabase;
        $response->assertJsonFragment($testResponse + ['id' => $response->json('id')]);

        return $response;
    }
}
