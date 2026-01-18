<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Frame;

class FlowRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'status',
        'package_amount',
        'email',
        'transaction_id',
        'code_id',
        'frame_id',
        'layout_key',
        'layout_count',
        'frame_file',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

    public function frames()
    {
        // Logical relationship via layout_key (not a foreign key)
        return $this->hasMany(Frame::class, 'layout_key', 'layout_key');
    }

    public function frame()
    {
        return $this->belongsTo(Frame::class);
    }
}
