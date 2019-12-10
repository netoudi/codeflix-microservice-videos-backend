<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Feature\Traits\TestDeletes;
use Tests\Feature\Traits\TestSaves;
use Tests\Feature\Traits\TestValidations;
use Tests\TestCase;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestDeletes;

    /**
     * @var CastMember
     */
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->castMember = factory(CastMember::class)->create();
    }

    public function testIndex()
    {
        $response = $this->getJson(route('cast-members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->getJson(route('cast-members.show', ['cast_member' => $this->castMember->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => '', 'type' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['type' => 'DIRECTOR'];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            ['name' => 'test_name', 'type' => CastMember::TYPE_DIRECTOR],
            ['name' => 'test_name', 'type' => CastMember::TYPE_ACTOR],
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
        }
    }

    public function testUpdate()
    {
        $this->castMember = factory(CastMember::class)->create([
            'name' => 'test_name',
            'type' => CastMember::TYPE_ACTOR,
        ]);

        $data = ['name' => 'test_name_updated', 'type' => CastMember::TYPE_DIRECTOR];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDestroy()
    {
        $this->assertDestroy();
    }

    protected function routeStore(): string
    {
        return route('cast-members.store');
    }

    protected function routeUpdate(): string
    {
        return route('cast-members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function routeDestroy(Model $model): string
    {
        return route('cast-members.destroy', ['cast_member' => $model->id]);
    }

    protected function model(): string
    {
        return CastMember::class;
    }
}
