<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $fillable = ['Model_ID', 'Make_ID', 'Model_Name'];
    protected $primaryKey = 'Model_ID';
    public $timestamps = false;

    public function make()
    {
        return $this->belongsTo(Make::class, 'Make_ID');
    }
}
