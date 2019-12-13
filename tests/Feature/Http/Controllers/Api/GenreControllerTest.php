<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\Feature\Traits\TestDeletes;
use Tests\Feature\Traits\TestSaves;
use Tests\Feature\Traits\TestValidations;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestDeletes;

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
        $data = ['name' => '', 'categories_id' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

        $data = ['categories_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['categories_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testStore()
    {
        $categoryId = factory(Category::class)->create()->id;

        $data = ['name' => 'test_name'];
        $response = $this->assertStore(
            $data + ['categories_id' => [$categoryId]],
            $data + ['is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertHasCategory($response->json('id'), $categoryId);

        $data = ['name' => 'test_name', 'is_active' => false];
        $response = $this->assertStore($data + ['categories_id' => [$categoryId]], $data);
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testUpdate()
    {
        $categoryId = factory(Category::class)->create()->id;

        $this->genre = factory(Genre::class)->create([
            'name' => 'test_name',
            'is_active' => false,
        ]);

        $data = ['name' => 'test_name_updated', 'is_active' => true];
        $response = $this->assertUpdate($data + ['categories_id' => [$categoryId]], $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertHasCategory($response->json('id'), $categoryId);

        $data['is_active'] = false;
        $this->assertUpdate($data + ['categories_id' => [$categoryId]], $data + ['deleted_at' => null]);
    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn(['name' => 'test_name']);

        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);

        try {
            $controller->store($request);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
        }
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn(['name' => 'test_name']);

        $controller
            ->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);

        try {
            $controller->update($request, 1);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
        }
    }

    public function testDestroy()
    {
        $this->assertDestroy();
    }

    protected function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', ['genre_id' => $genreId, 'category_id' => $categoryId]);
    }

    protected function routeStore(): string
    {
        return route('genres.store');
    }

    protected function routeUpdate(): string
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function routeDestroy(Model $model): string
    {
        return route('genres.destroy', ['genre' => $model->id]);
    }

    protected function model(): string
    {
        return Genre::class;
    }
}
