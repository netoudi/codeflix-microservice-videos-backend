<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Feature\Traits\TestValidations;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

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
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test_name',
        ]);

        $genreId = $response->json('id');
        $genre = Genre::find($genreId);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());

        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test_name',
            'is_active' => false,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test_name',
                'is_active' => false,
            ]);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'test_name',
            'is_active' => false,
        ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name' => 'test_name_updated',
            'is_active' => true,
        ]);

        $genreId = $response->json('id');
        $genre = Genre::find($genreId);

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'test_name_updated',
                'is_active' => true,
            ]);
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
}
