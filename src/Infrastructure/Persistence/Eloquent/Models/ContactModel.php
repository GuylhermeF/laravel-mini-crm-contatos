<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Database\Factories\ContactModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property int $score
 * @property string $status
 * @property \Carbon\Carbon|null $processed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class ContactModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static string $factory = ContactModelFactory::class;

    protected $table = 'contacts';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'score',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * Normalize phone on saving (Observer pattern via model events).
     */
    protected static function booted(): void
    {
        static::saving(function (ContactModel $model) {
            // Normalize phone: strip non-numeric characters
            $model->phone = preg_replace('/\D/', '', $model->phone);
        });
    }
}
