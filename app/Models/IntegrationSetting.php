<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * IntegrationSetting — InstitutionSetting এর মতোই institution_id primary key
 * প্যাটার্ন ব্যবহার করে (এক institution = এক row), BelongsToTenant লাগেনি।
 */
class IntegrationSetting extends Model
{
    protected $primaryKey = 'institution_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'institution_id',
        'bkash_enabled', 'bkash_merchant_number', 'bkash_api_key', 'bkash_api_secret',
        'nagad_enabled', 'nagad_merchant_number', 'nagad_api_key',
        'sms_enabled', 'sms_provider', 'sms_api_key', 'sms_sender_id',
        'email_enabled', 'email_smtp_host', 'email_smtp_port', 'email_smtp_username',
        'email_smtp_password', 'email_smtp_encryption', 'email_from_address', 'email_from_name',
    ];

    protected $casts = [
        'bkash_enabled' => 'boolean',
        'nagad_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'bkash_api_key' => 'encrypted',
        'bkash_api_secret' => 'encrypted',
        'nagad_api_key' => 'encrypted',
        'sms_api_key' => 'encrypted',
        'email_smtp_password' => 'encrypted',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
