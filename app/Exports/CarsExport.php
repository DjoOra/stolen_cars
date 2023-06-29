<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Car;

class CarsExport implements FromCollection
{
    protected $cars;

    public function __construct($cars)
    {
        $this->cars = $cars;
    }

    public function collection()
    {
        return $this->cars;
    }
}
