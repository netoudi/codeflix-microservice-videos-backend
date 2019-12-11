<?php

namespace Tests\Unit\Models;

use App\Models\Traits\Uuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class VideoUnitTest extends TestCase
{
    /**
     * @var Video
     */
    private $video;

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = new Video();
    }

    public function testIfExtendsModelFromEloquent()
    {
        $this->assertInstanceOf(Model::class, $this->video);
    }

    public function testIfUseTraits()
    {
        $traits = [SoftDeletes::class, Uuid::class];
        $videoTraits = array_keys(class_uses(Video::class));

        $this->assertEquals($traits, $videoTraits);
    }

    public function testFillableAttribute()
    {
        $fillable = ['title', 'description', 'year_launched', 'opened', 'rating', 'duration'];

        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testDatesAttribute()
    {
        $dates = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($dates as $date) {
            $this->assertContains($date, $this->video->getDates());
        }

        $this->assertCount(count($dates), $this->video->getDates());
    }

    public function testCastsAttribute()
    {
        $casts = [
            'id' => 'string',
            'year_launched' => 'integer',
            'opened' => 'boolean',
            'rating' => 'string',
            'duration' => 'integer',
        ];

        $this->assertEquals($casts, $this->video->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->video->incrementing);
    }
}