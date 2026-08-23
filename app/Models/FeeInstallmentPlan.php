<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallmentPlan extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'student_id', 'fee_type', 'total_amount',
        'installments_count', 'start_month', 'note', 'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function installments()
    {
        return $this->hasMany(FeeCollection::class, 'installment_plan_id')->orderBy('installment_number');
    }
}
