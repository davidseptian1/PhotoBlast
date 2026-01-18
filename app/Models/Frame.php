<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    use HasFactory;

    protected $fillable = [
        'layout_key',
        'file',
        'original_name',
        'photo_pad_ratio',
        'photo_scale',
        'photo_offset_x',
        'photo_offset_y',
        'grid_gap_ratio',
        'row_anchor_ratio',
    ];
}
