<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Traits\TestDeletes;
use Tests\Feature\Traits\TestSaves;
use Tests\Feature\Traits\TestValidations;
use Tests\TestCase;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestDeletes;

    /**
     * @var Video
     */
    private $video;

    /**
     * @var array
     */
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = factory(Video::class)->create();

        $this->sendData = [
            'title' => 'test_title',
            'description' => 'test_description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => '',
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = ['title' => str_repeat('a', 256)];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = ['duration' => 'a'];

        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = ['year_launched' => 'a'];

        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = ['opened' => 'a'];

        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = ['rating' => 'a'];

        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testInvalidationCategoriesField()
    {
        $data = ['categories_id' => 'a'];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['categories_id' => [100]];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();

        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationGenresField()
    {
        $data = ['genres_id' => 'a'];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['genres_id' => [100]];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = factory(Genre::class)->create();
        $genre->delete();

        $data = ['genres_id' => [$genre->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationVideoFileField()
    {
        \Storage::fake();

        $data = ['video_file' => UploadedFile::fake()->create('video.mkv')->size(10)];
        $this->assertInvalidationInStoreAction($data, 'mimetypes', ['values' => 'video/mp4']);
        $this->assertInvalidationInUpdateAction($data, 'mimetypes', ['values' => 'video/mp4']);

        $data = ['video_file' => UploadedFile::fake()->create('video.mp4')->size(11)];
        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => '10']);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => '10']);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categoriesId);
        $genreId = $genre->id;

        $response = $this->postJson(
            $this->routeStore(),
            $this->sendData + ['genres_id' => [$genreId], 'categories_id' => [$categoriesId[0]]]
        );
        $this->assertDatabaseHas(
            'category_video',
            ['category_id' => $categoriesId[0], 'video_id' => $response->json('id')]
        );

        $response = $this->putJson(
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + ['genres_id' => [$genreId], 'categories_id' => [$categoriesId[1], $categoriesId[2]]]
        );
        $this->assertDatabaseMissing(
            'category_video',
            ['category_id' => $categoriesId[0], 'video_id' => $response->json('id')]
        );
        $this->assertDatabaseHas(
            'category_video',
            ['category_id' => $categoriesId[1], 'video_id' => $response->json('id')]
        );
        $this->assertDatabaseHas(
            'category_video',
            ['category_id' => $categoriesId[2], 'video_id' => $response->json('id')]
        );
    }

    public function testSyncGenres()
    {
        $genres = factory(Genre::class, 3)->create();
        $genresId = $genres->pluck('id')->toArray();
        $categoryId = factory(Category::class)->create()->id;

        $genres->each(function ($genre) use ($categoryId) {
            $genre->categories()->sync($categoryId);
        });

        $response = $this->postJson(
            $this->routeStore(),
            $this->sendData + ['genres_id' => [$genresId[0]], 'categories_id' => [$categoryId]]
        );
        $this->assertDatabaseHas(
            'genre_video',
            ['genre_id' => $genresId[0], 'video_id' => $response->json('id')]
        );

        $response = $this->putJson(
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + ['genres_id' => [$genresId[1], $genresId[2]], 'categories_id' => [$categoryId]]
        );
        $this->assertDatabaseMissing(
            'genre_video',
            ['genre_id' => $genresId[0], 'video_id' => $response->json('id')]
        );
        $this->assertDatabaseHas(
            'genre_video',
            ['genre_id' => $genresId[1], 'video_id' => $response->json('id')]
        );
        $this->assertDatabaseHas(
            'genre_video',
            ['genre_id' => $genresId[2], 'video_id' => $response->json('id')]
        );
    }

    public function testSaveWithoutFiles()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $data = [
            [
                'send_data' => $this->sendData + [
                        'opened' => false,
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['opened' => false],
            ],
            [
                'send_data' => $this->sendData + [
                        'opened' => true,
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['opened' => true],
            ],
            [
                'send_data' => $this->sendData + [
                        'rating' => Video::RATING_LIST[1],
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
            ],
        ];

        foreach ($data as $key => $value) {
            // store
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);

            // relations
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);

            // update
            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);

            // relations
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);
        }
    }

    public function testSaveWithFiles()
    {
        \Storage::fake();

        $videoFile = UploadedFile::fake()->create('video.mp4');
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->postJson(
            $this->routeStore(),
            $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id],
                'video_file' => $videoFile,
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($this->sendData + ['video_file' => $videoFile->hashName()]);

        $videoId = $response->json('id');

        \Storage::assertExists("{$videoId}/{$videoFile->hashName()}");
    }

    public function testDestroy()
    {
        $this->assertDestroy();
    }

    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', ['video_id' => $videoId, 'category_id' => $categoryId]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', ['video_id' => $videoId, 'genre_id' => $genreId]);
    }

    protected function routeStore(): string
    {
        return route('videos.store');
    }

    protected function routeUpdate(): string
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function routeDestroy(Model $model): string
    {
        return route('videos.destroy', ['video' => $model->id]);
    }

    protected function model(): string
    {
        return Video::class;
    }
}
