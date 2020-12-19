<?php

namespace Tests\Unit\Observers;

use App\Observers\SyncModelObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Tests\TestCase;

class Category extends Model
{
    //
}

class CastMember extends Model
{
    //
}

class SyncModelObserverTest extends TestCase
{
    /**
     * @var Category
     */
    private $category;
    /**
     * @var SyncModelObserver
     */
    private $observer;
    /**
     * @var CastMember
     */
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = new Category();
        $this->castMember = new CastMember();
        $this->observer = new SyncModelObserver();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }

    public function testCreated()
    {
        $this->assertMethod('created');
    }

    public function testUpdated()
    {
        $this->assertMethod('updated');
    }

    public function testDeleted()
    {
        $this->assertMethod('deleted');
    }

    public function testRestored()
    {
        $this->assertMethod('restored');
    }

    public function testForceDeleted()
    {
        $this->assertMethod('force_deleted');
    }

    public function testGetModelName()
    {
        $reflectionClass = new \ReflectionClass(SyncModelObserver::class);
        $reflectionMethod = $reflectionClass->getMethod('getModelName');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('category', $reflectionMethod->invokeArgs($this->observer, [$this->category]));
        $this->assertEquals('cast_member', $reflectionMethod->invokeArgs($this->observer, [$this->castMember]));
    }

    public function testPublish()
    {
        $this->markTestSkipped();
    }

    protected function assertMethod(string $method)
    {
        $models = [
            'category' => \Mockery::mock(Category::class)->makePartial(),
            'cast_member' => \Mockery::mock(Category::class)->makePartial(),
        ];

        foreach ($models as $key => $model) {
            $model->shouldReceive('toArray')
                ->withNoArgs()
                ->andReturn(['id' => 'uuid']);

            $observer = \Mockery::mock(SyncModelObserver::class)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $observer->shouldReceive('getModelName')
                ->withArgs([$model])
                ->andReturn($key);

            $observer->shouldReceive('publish')
                ->once()
                ->withArgs(["model.{$key}.{$method}", ['id' => 'uuid']]);

            $hasError = false;

            try {
                $observer->{Str::camel($method)}($model);
            } catch (\Exception $exception) {
                dump($exception);
                $hasError = true;
            }

            $this->assertFalse($hasError);
        }
    }
}
