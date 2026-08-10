<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * UuidPrimaryKey
 *
 * ⚠️ গুরুত্বপূর্ণ শিক্ষা: শুধু $incrementing=false সেট করলেই যথেষ্ট না।
 * Laravel non-incrementing key এর ক্ষেত্রে insert-এর পর DB থেকে generated
 * ID ফিরিয়ে আনে না (শুধু auto-increment integer এর জন্য এটা করে)। তাই
 * DB-লেভেল gen_random_uuid() default-এর ভরসায় থাকলে PHP অবজেক্টে id null
 * থেকে যায় create() এর পরও — এটাই বাগের কারণ ছিল।
 *
 * সমাধান: creating() ইভেন্টে PHP থেকেই UUID জেনারেট করে বসিয়ে দেওয়া,
 * DB default-এর ওপর নির্ভর না করে। DB default এখনো fallback হিসেবে
 * থেকে যাচ্ছে (কেউ যদি সরাসরি SQL দিয়ে insert করে), কিন্তু Eloquent দিয়ে
 * তৈরি প্রতিটা রেকর্ডে এখন থেকে PHP-ই আইডি নির্ধারণ করবে।
 */
trait UuidPrimaryKey
{
    public function initializeUuidPrimaryKey(): void
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    protected static function bootUuidPrimaryKey(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
