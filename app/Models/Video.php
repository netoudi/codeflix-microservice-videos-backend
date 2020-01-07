<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid, UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'id' => 'string',
        'year_launched' => 'integer',
        'opened' => 'boolean',
        'rating' => 'string',
        'duration' => 'integer',
    ];

    public static $fileFields = ['video_file', 'banner', 'trailer'];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);

        try {
            \DB::beginTransaction();

            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);

            $obj->uploadFiles($files);

            \DB::commit();
        } catch (\Exception $e) {
            if (isset($obj)) {
                $obj->deleteFiles($files);
            }

            \DB::rollBack();

            throw $e;
        }

        return $obj->refresh();
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);

        try {
            \DB::beginTransaction();

            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);

            if ($saved) {
                $this->uploadFiles($files);
                // TODO: delete old files
            }

            \DB::commit();
        } catch (\Exception $e) {
            // TODO: delete files from uploads

            \DB::rollBack();

            throw $e;
        }

        return $this->refresh();
    }

    public static function handleRelations(self $video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    protected function uploadDir()
    {
        return $this->id;
    }
}
