<?php

namespace App\Jobs;

use App\Models\PathaoCity;
use App\Models\PathaoZone;
use App\Models\PathaoArea;
use App\Services\PathaoCourier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPathaoLocations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 1;

    public function handle()
    {
        try {
            DB::beginTransaction();

            $citiesStored = 0;
            $zonesStored = 0;
            $areasStored = 0;

            // 1. Get cities from API
            $citiesResponse = PathaoCourier::GET_CITIES();
            
            if (!isset($citiesResponse['data']['data'])) {
                throw new \Exception('Invalid response from Pathao API for cities');
            }

            $cities = $citiesResponse['data']['data'];
            $totalCities = count($cities);

            foreach ($cities as $index => $cityData) {
                // Store or update city
                $city = PathaoCity::updateOrCreate(
                    ['city_id' => $cityData['city_id']],
                    ['city_name' => $cityData['city_name']]
                );
                $citiesStored++;

                // Log progress every 5 cities
                if ($index % 5 == 0) {
                    Log::info("Syncing Pathao locations: {$index}/{$totalCities} cities processed");
                }

                // 2. Get zones for this city from API
                $zonesResponse = PathaoCourier::GET_ZONES($cityData['city_id']);
                
                if (isset($zonesResponse['data']['data'])) {
                    $zones = $zonesResponse['data']['data'];

                    foreach ($zones as $zoneData) {
                        // Store or update zone
                        PathaoZone::updateOrCreate(
                            ['zone_id' => $zoneData['zone_id']],
                            [
                                'zone_name' => $zoneData['zone_name'],
                                'city_id' => $cityData['city_id']
                            ]
                        );
                        $zonesStored++;

                        // 3. Get areas for this zone from API
                        $areasResponse = PathaoCourier::GET_AREAS($zoneData['zone_id']);
                        
                        if (isset($areasResponse['data']['data'])) {
                            $areas = $areasResponse['data']['data'];

                            foreach ($areas as $areaData) {
                                PathaoArea::updateOrCreate(
                                    ['area_id' => $areaData['area_id']],
                                    [
                                        'area_name' => $areaData['area_name'],
                                        'zone_id' => $zoneData['zone_id'],
                                        'home_delivery_available' => $areaData['home_delivery_available'] ?? true,
                                        'pickup_available' => $areaData['pickup_available'] ?? true,
                                    ]
                                );
                                $areasStored++;
                            }
                        }
                    }
                }
            }

            DB::commit();

            Log::info('Pathao sync completed', [
                'cities' => $citiesStored,
                'zones' => $zonesStored,
                'areas' => $areasStored
            ]);

            return [
                'success' => true,
                'data' => [
                    'cities_stored' => $citiesStored,
                    'zones_stored' => $zonesStored,
                    'areas_stored' => $areasStored,
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SyncPathaoLocations job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}