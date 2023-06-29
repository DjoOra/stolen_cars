<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CarsExport;
use App\Models\Make;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Car::query();

        $query->applyFilters($request->input('make'), $request->input('model'), $request->input('year'));
        $query->applySearch($request->input('search'));

        // Применение сортировки
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order');
            $query->orderBy($sortBy, $sortOrder);
        }

        // Получение результата с пагинацией
        $cars = $query->paginate(10);

        return response()->json($cars);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'vin' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Выполнение запроса к API декодера VIN
        $vinData = $this->decodeVin($request->vin);
        if (!$vinData) {
            return response()->json(['error' => 'Failed to decode VIN'], 400);
        }

        // Создание записи авто
        $car = new Car;
        $car->name = $request->name;
        $car->license_plate = $request->license_plate;
        $car->color = $request->color;
        $car->vin = $request->vin;
        $car->make = $vinData['make'];
        $car->model = $vinData['model'];
        $car->year = $vinData['year'];
        $car->save();

        return response()->json(['message' => 'Car added successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $car = Car::findOrFail($id);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'license_plate' => 'string|max:50',
            'color' => 'string|max:50',
            'vin' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        if ($request->filled('name')) {
            $car->name = $request->input('name');
        }

        if ($request->filled('license_plate')) {
            $car->license_plate = $request->input('license_plate');
        }

        if ($request->filled('color')) {
            $car->color = $request->input('color');
        }

        if ($request->filled('vin')) {
            $car->vin = $request->input('vin');

            $vinData = $this->decodeVin($request->vin);

            if (!$vinData) {
                return response()->json(['message' => 'Invalid VIN code'], 422);
            }

            $car->make = $vinData['make'];
            $car->model = $vinData['model'];
            $car->year = $vinData['year'];
        }

        $car->save();

        return response()->json(['message' => 'Car updated successfully', 'car' => $car]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $car = Car::findOrFail($id);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $car->delete();

        return response()->json(['message' => 'Car deleted successfully']);
    }

    public function export(Request $request)
    {
        $query = Car::query();

        $query->applyFilters($request->input('make'), $request->input('model'), $request->input('year'));
        $query->applySearch($request->input('search'));

        // Применение сортировки
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order');
            $query->orderBy($sortBy, $sortOrder);
        }

        // Получение данных
        $cars = $query->get();

        // Создание экспорта
        $export = new CarsExport($cars);

        // Генерация файла XLS
        return Excel::download($export, 'cars.xls');
    }

    public function autocomplete($make)
    {
        $make = Make::where('Make_Name', 'LIKE', '%' . $make . '%')->first();

        if (!$make) {
            return response()->json(['error' => 'Make not found'], 404);
        }

        $models = $make->models;

        return response()->json(['make' => $make, 'models' => $models]);
    }

    private function decodeVin($vin)
    {
        $url = "https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVin/{$vin}?format=json";

        $client = new \GuzzleHttp\Client();
        $response = $client->get($url);

        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody(), true);

            if (isset($data['Results'][0])) {
                $make = $data['Results'][7]['Value'];
                $model = $data['Results'][9]['Value'];
                $year = $data['Results'][10]['Value'];

                return [
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
                ];
            }
        }

        return null;
    }
}
