<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'file_name', 'original_name', 'file_hash',
        'file_size', 'mime_type', 'storage_path', 'uploaded_at'
    ];
    
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
