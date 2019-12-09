<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Feature\Traits\TestSaves;
use Tests\Feature\Traits\TestValidations;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    /**
     * @var Genre
     */
    private $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->genre = factory(Genre::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testStore()
    {
        $data = ['name' => 'test_name'];
        $this->assertStore($data, $data + ['is_active' => true, 'deleted_at' => null]);

        $data = ['name' => 'test_name', 'is_active' => false];
        $response = $this->assertStore($data, $data);
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testUpdate()
    {
        $this->genre = factory(Genre::class)->create([
            'name' => 'test_name',
            'is_active' => false,
        ]);

        $data = ['name' => 'test_name_updated', 'is_active' => true];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data['is_active'] = false;
        $this->assertUpdate($data, $data + ['deleted_at' => null]);
    }

    public function testDestroy()
    {
        // reset database
        $this->runDatabaseMigrations();

        $genres = factory(Genre::class, 3)->create();
        $genre = $genres->first();

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $genre->id]));

        $response->assertStatus(204);
        $this->assertCount(2, Genre::all());
        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $genre->id]));

        $response->assertStatus(404);
        $this->assertCount(2, Genre::all());
        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));
    }

    protected function routeStore(): string
    {
        return route('genres.store');
    }

    protected function routeUpdate(): string
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model(): string
    {
        return Genre::class;
    }
}
