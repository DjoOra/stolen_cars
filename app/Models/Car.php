<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = ['name', 'license_plate', 'color', 'vin', 'make', 'model', 'year'];

    public function scopeApplyFilters($query, $make, $model, $year)
    {
        if ($make) {
            $query->where('make', 'LIKE', '%' . $make . '%');
        }

        if ($model) {
            $query->where('model', 'LIKE', '%' . $model . '%');
        }

        if ($year) {
            $query->where('year', 'LIKE', '%' . $year . '%');
        }
    }

    public function scopeApplySearch($query, $searchTerm)
    {
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('license_plate', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('vin', 'LIKE', '%' . $searchTerm . '%');
            });
        }
    }
}
