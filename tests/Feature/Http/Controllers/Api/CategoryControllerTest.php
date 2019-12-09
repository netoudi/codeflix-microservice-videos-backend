<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Feature\Traits\TestValidations;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

    /**
     * @var Category
     */
    private $category;

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
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test_name',
        ]);

        $categoryId = $response->json('id');
        $category = Category::find($categoryId);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());

        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test_name',
            'description' => 'test_description',
            'is_active' => false,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test_name',
                'description' => 'test_description',
                'is_active' => false,
            ]);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'name' => 'test_name',
            'description' => 'test_description',
            'is_active' => false,
        ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active' => true,
        ]);

        $categoryId = $response->json('id');
        $category = Category::find($categoryId);

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'name' => 'test_name_updated',
                'description' => 'test_description_updated',
                'is_active' => true,
            ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test_name',
            'description' => '',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'description' => null,
            ]);

        $category->description = 'test_description';
        $category->save();

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test_name',
            'description' => null,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'description' => null,
            ]);
    }

    public function testDestroy()
    {
        // reset database
        $this->runDatabaseMigrations();

        $categories = factory(Category::class, 3)->create();
        $category = $categories->first();

        $response = $this->json('DELETE', route('categories.destroy', ['category' => $category->id]));

        $response->assertStatus(204);
        $this->assertCount(2, Category::all());
        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));

        $response = $this->json('DELETE', route('categories.destroy', ['category' => $category->id]));

        $response->assertStatus(404);
        $this->assertCount(2, Category::all());
        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }

    protected function routeStore(): string
    {
        return route('categories.store');
    }

    protected function routeUpdate(): string
    {
        return route('categories.update', ['category' => $this->category->id]);
    }
}
