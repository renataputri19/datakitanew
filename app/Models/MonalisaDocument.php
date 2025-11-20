<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaDocument extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'assessment_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the assessment that owns this document.
     */
    public function assessment()
    {
        return $this->belongsTo(MonalisaAssessment::class, 'assessment_id');
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the comments for this document.
     */
    public function comments()
    {
        return $this->hasMany(MonalisaDocumentComment::class, 'document_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the file size in human-readable format.
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

