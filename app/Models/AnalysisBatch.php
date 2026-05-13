<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $status
 * @property int $total_documents
 */
class AnalysisBatch extends Model
{
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'status',
        'total_documents',
    ];

    /**
     * @return HasMany<Document>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * @return HasMany<Keyword>
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }
}
