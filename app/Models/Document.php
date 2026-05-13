<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $analysis_batch_id
 * @property string $original_filename
 * @property string $stored_path
 * @property string $status
 * @property string|null $error_message
 */
class Document extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'analysis_batch_id',
        'original_filename',
        'stored_path',
        'status',
        'error_message',
    ];

    /**
     * @return BelongsTo<AnalysisBatch, Document>
     */
    public function analysisBatch(): BelongsTo
    {
        return $this->belongsTo(AnalysisBatch::class);
    }

    /**
     * @return HasMany<DocumentKeywordResult>
     */
    public function keywordResults(): HasMany
    {
        return $this->hasMany(DocumentKeywordResult::class);
    }

    /**
     * @return BelongsToMany<Keyword>
     */
    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'document_keyword_results')
            ->withPivot('frequency_count')
            ->withTimestamps();
    }
}
