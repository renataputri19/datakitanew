<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaDocumentComment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'document_id',
        'user_id',
        'comment',
        'status',
    ];

    /**
     * Get the document that owns this comment.
     */
    public function document()
    {
        return $this->belongsTo(MonalisaDocument::class, 'document_id');
    }

    /**
     * Get the user who made this comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

