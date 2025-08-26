<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintBatch extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'file_name',
        'status',                // queued|processing|done|failed
        'source_excel_path',     // storage path relative (local disk)
        'mapping_json',          // JSON con mapping usado
        'normalized_excel_path', // storage path relativo
        'folder',                // ej: print_batches/abcd
        'artifact_path',         // zip/pdf final
        'user_id',
    ];

    protected $casts = [
        'mapping_json' => 'array',
    ];
}
