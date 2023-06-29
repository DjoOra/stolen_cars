<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Make;
use App\Models\CarModel;

class UpdateCarData extends Command
{
    protected $signature = 'cars:update';

    protected $description = 'Update car makes and models data from external API';

    public function handle()
    {

        $makesResponse = Http::get('https://vpic.nhtsa.dot.gov/api/vehicles/getallmakes?format=json');

        if ($makesResponse->ok()) {
            $makesData = $makesResponse->json();

            if (isset($makesData['Results'])) {
                $makes = $makesData['Results'];

                foreach ($makes as $make) {
                    Make::updateOrCreate(
                        ['Make_ID' => $make['Make_ID']],
                        ['Make_Name' => $make['Make_Name']]
                    );
                }
            }
        }

        $makes = Make::all();

        foreach ($makes as $make) {

            $url = "https://vpic.nhtsa.dot.gov/api/vehicles/getmodelsformakeid/{$make->Make_ID}?format=json";

            try {
                $modelsResponse = Http::get($url);
            } catch (\Throwable $th) {
                //log
                continue;
            }

            if ($modelsResponse->ok()) {
                $modelsData = $modelsResponse->json();

                if (isset($modelsData['Results'])) {
                    $models = $modelsData['Results'];

                    foreach ($models as $model) {
                        CarModel::updateOrCreate(
                            ['Model_ID' => $model['Model_ID']],
                            ['Make_ID' => $model['Make_ID'], 'Model_Name' => $model['Model_Name']],
                        );
                    }
                }
            }
        }


        $this->info('Car makes and models data updated successfully.');
    }
}
