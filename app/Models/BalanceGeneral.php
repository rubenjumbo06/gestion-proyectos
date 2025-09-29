<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BalanceGeneral extends Model
{
    use SoftDeletes;

    protected $table = 'balance_general';
    protected $primaryKey = 'id_balance';
    protected $fillable = ['id_proyecto', 'total_servicios', 'egresos'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public $timestamps = true;

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'id_proyecto', 'id_proyecto');
    }
}
