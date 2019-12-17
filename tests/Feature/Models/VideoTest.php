<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    public function testRollbackCreate()
    {
        $hasError = false;

        try {
            Video::create([
                'title' => 'test_title',
                'description' => 'test_description',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2],
            ]);
        } catch (QueryException $exception) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;

        $hasError = false;

        try {
            $video->update([
                'title' => 'test_title',
                'description' => 'test_description',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2],
            ]);
        } catch (QueryException $exception) {
            $this->assertDatabaseHas('videos', ['title' => $oldTitle]);
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testList()
    {
        factory(Video::class, 1)->create();
        $videos = Video::all();

        $this->assertCount(1, $videos);

        $videoKey = array_keys($videos->first()->getAttributes());

        $this->assertEqualsCanonicalizing(
            [
                'id',
                'title',
                'description',
                'year_launched',
                'opened',
                'rating',
                'duration',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            $videoKey
        );
    }

    public function testCreate()
    {
        $video = Video::create([
            'title' => 'test_title',
            'description' => 'test_description',
            'year_launched' => 2020,
            'rating' => 'L',
            'duration' => 120,
        ]);
        $video->refresh();

        $this->assertTrue(Uuid::isValid($video->id));
        $this->assertEquals('test_title', $video->title);
        $this->assertEquals('test_description', $video->description);
        $this->assertEquals(2020, $video->year_launched);
        $this->assertEquals('L', $video->rating);
        $this->assertEquals(120, $video->duration);
    }

    public function testUpdate()
    {
        /** @var Video $video */
        $video = factory(Video::class)->create([
            'title' => 'test_title',
            'description' => 'test_description',
            'year_launched' => 2020,
            'rating' => 'L',
            'duration' => 120,
        ]);

        $data = [
            'title' => 'test_title_updated',
            'description' => 'test_description_updated',
            'year_launched' => 2030,
            'rating' => '18',
            'duration' => 90,
        ];
        $video->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }
    }

    public function testDelete()
    {
        /** @var Video $video */
        $video = factory(Video::class)->create();

        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));

        $video->forceDelete();
        $this->assertNull(Video::find($video->id));
    }
}
