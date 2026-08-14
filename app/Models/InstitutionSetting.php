<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * InstitutionSetting — BelongsToTenant ব্যবহার করা হয়নি ইচ্ছাকৃতভাবে।
 * কারণ: এই টেবিলের primary key-ই institution_id (এক institution = এক row),
 * global scope এখানে অপ্রয়োজনীয় জটিলতা তৈরি করবে। এটা সবসময় Institution
 * এর মাধ্যমে (relation দিয়ে) অ্যাক্সেস হওয়া উচিত, সরাসরি নয়।
 */
class InstitutionSetting extends Model
{
    protected $primaryKey = 'institution_id';
    public $incrementing = false;
    protected $keyType = 'string'; // uuid

    protected $fillable = [
        'institution_id',
        'has_departments',
        'consecutive_period_blocking',
        'qawmi_grading',
        'theme_primary_color',
        'theme_accent_color',
    ];

    protected $casts = [
        'has_departments' => 'boolean',
        'consecutive_period_blocking' => 'boolean',
        'qawmi_grading' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
