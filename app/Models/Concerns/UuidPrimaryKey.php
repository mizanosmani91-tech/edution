<?php

namespace App\Models\Concerns;

/**
 * UuidPrimaryKey
 *
 * DB-লেভেলে `gen_random_uuid()` default থাকায় PHP থেকে UUID জেনারেট করা
 * লাগে না — এই trait শুধু Eloquent-কে বলে দেয় primary key auto-increment
 * integer না, বরং string(uuid)। এটা প্রতিটা মডেলে লাগবে যেটার টেবিলে
 * `$table->uuid('id')->primary()` আছে (students, teachers, classes, exams...)।
 */
trait UuidPrimaryKey
{
    public $incrementing = false;
    protected $keyType = 'string';
}
