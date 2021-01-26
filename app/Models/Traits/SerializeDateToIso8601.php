<?php

namespace App\Models\Traits;

trait SerializeDateToIso8601
{
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format(\DateTime::ISO8601);
    }
}
