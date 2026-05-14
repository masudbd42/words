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
        'word_limit',
        'top_words',
    ];

    protected $casts = [
        'top_words' => 'array',
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

    /**
     * Aggregate top words across the whole batch.
     */
    public function aggregateIntelligence(): void
    {
        $allTopWords = [];
        foreach ($this->documents()->where('status', Document::STATUS_COMPLETED)->get() as $doc) {
            $docTopWords = $doc->top_words;
            if ($docTopWords && is_array($docTopWords)) {
                foreach ($docTopWords as $word => $count) {
                    $allTopWords[$word] = ($allTopWords[$word] ?? 0) + $count;
                }
            }
        }
        
        arsort($allTopWords);
        $batchTopWords = array_slice($allTopWords, 0, $this->word_limit ?: 20, true);

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'top_words' => $batchTopWords,
        ]);
    }
}
