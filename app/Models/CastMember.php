<?php

namespace App\Models;

use App\ModelFilters\CasMemberFilter;
use App\Models\Traits\SerializeDateToIso8601;
use App\Models\Traits\Uuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes, Uuid, Filterable, SerializeDateToIso8601;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    public static $types = [
        self::TYPE_DIRECTOR,
        self::TYPE_ACTOR,
    ];

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];
    protected $casts = ['id' => 'string'];

    public function modelFilter()
    {
        return $this->provideFilter(CasMemberFilter::class);
    }
}
