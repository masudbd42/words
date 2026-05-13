<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $document_id
 * @property int $keyword_id
 * @property int $frequency_count
 */
class DocumentKeywordResult extends Model
{
    protected $fillable = [
        'document_id',
        'keyword_id',
        'frequency_count',
    ];

    /**
     * @return BelongsTo<Document, DocumentKeywordResult>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return BelongsTo<Keyword, DocumentKeywordResult>
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
