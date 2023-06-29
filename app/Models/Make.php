<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Make extends Model
{
    protected $fillable = ['Make_ID', 'Make_Name'];
    protected $primaryKey = 'Make_ID';
    public $timestamps = false;

    public function models()
    {
        return $this->hasMany(CarModel::class, 'Make_ID');
    }
}
