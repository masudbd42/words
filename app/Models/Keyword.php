<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $analysis_batch_id
 * @property string $keyword
 */
class Keyword extends Model
{
    protected $fillable = [
        'analysis_batch_id',
        'keyword',
    ];

    /**
     * @return BelongsTo<AnalysisBatch, Keyword>
     */
    public function analysisBatch(): BelongsTo
    {
        return $this->belongsTo(AnalysisBatch::class);
    }

    /**
     * @return BelongsToMany<Document>
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_keyword_results')
            ->withPivot('frequency_count')
            ->withTimestamps();
    }
}
