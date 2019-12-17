<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'id' => 'string',
        'year_launched' => 'integer',
        'opened' => 'boolean',
        'rating' => 'string',
        'duration' => 'integer',
    ];

    public static function create(array $attributes = [])
    {
        try {
            \DB::beginTransaction();

            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);

            // TODO: uploads here

            \DB::commit();
        } catch (\Exception $e) {
            if (isset($obj)) {
                // TODO: delete files from uploads
            }

            \DB::rollBack();

            throw $e;
        }

        return $obj->refresh();
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            \DB::beginTransaction();

            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);

            if ($saved) {
                // TODO: uploads here
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

    protected static function handleRelations(self $video, array $attributes)
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
}
