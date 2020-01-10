<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Feature\Traits\TestDeletes;
use Tests\Feature\Traits\TestSaves;
use Tests\Feature\Traits\TestValidations;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestDeletes;

    /**
     * @var Category
     */
    private $category;

    private $serializedFields = [
        'id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
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
        $response = $this->assertStore($data,
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $data = ['name' => 'test_name', 'description' => 'test_description', 'is_active' => false];
        $response = $this->assertStore($data, $data);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
    }

    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'name' => 'test_name',
            'description' => 'test_description',
            'is_active' => false,
        ]);

        $data = ['name' => 'test_name_updated', 'description' => 'test_description_updated', 'is_active' => true];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data['is_active'] = false;
        $this->assertUpdate($data, $data + ['deleted_at' => null]);

        $data = ['name' => 'test_name', 'description' => ''];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test_description';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test_description']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));
    }

    public function testDestroy()
    {
        $this->assertDestroy();
    }

    protected function routeStore(): string
    {
        return route('categories.store');
    }

    protected function routeUpdate(): string
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function routeDestroy(Model $model): string
    {
        return route('categories.destroy', ['category' => $model->id]);
    }

    protected function model(): string
    {
        return Category::class;
    }
}
