<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        // create
        $response = $this->json('POST', route('categories.store'), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a',
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        // update
        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a',
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name']),
            ]);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255]),
            ]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active']),
            ]);
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
}
