<?php

namespace App\Models;

use App\ModelFilters\GenreFilter;
use App\Models\Traits\SerializeDateToIso8601;
use App\Models\Traits\Uuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use SoftDeletes, Uuid, Filterable, SerializeDateToIso8601;

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = ['name', 'is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = ['id' => 'string', 'is_active' => 'boolean'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function modelFilter()
    {
        return $this->provideFilter(GenreFilter::class);
    }
}
