<?php

namespace App\Services;

use App\Models\PathaoCity;
use App\Models\PathaoZone;
use App\Models\PathaoArea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoLocationService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.pathao.base_url', 'https://api-hermes.pathao.com');
        $this->clientId = config('services.pathao.client_id');
        $this->clientSecret = config('services.pathao.client_secret');
    }

    /**
     * Get access token for Pathao API
     */
    protected function getAccessToken()
    {
        try {
            $response = Http::post($this->baseUrl . '/v1/oauth/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                return $this->accessToken;
            }

            throw new \Exception('Failed to get access token: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Pathao token error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make authenticated request to Pathao API
     */
    protected function makeRequest($endpoint, $method = 'GET', $data = [])
    {
        try {
            $token = $this->getAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->$method($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API request failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Pathao API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get cities from Pathao API
     */
    public function getCitiesFromApi()
    {
        try {
            $response = $this->makeRequest('/v1/courier/cities');
            
            if (isset($response['data']['data'])) {
                return $response['data']['data'];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching cities: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get zones from Pathao API by city ID
     */
    public function getZonesFromApi($cityId)
    {
        try {
            $response = $this->makeRequest("/v1/courier/zones?city_id={$cityId}");
            
            if (isset($response['data']['data'])) {
                return $response['data']['data'];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error("Error fetching zones for city {$cityId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get areas from Pathao API by zone ID
     */
    public function getAreasFromApi($zoneId)
    {
        try {
            $response = $this->makeRequest("/v1/courier/areas?zone_id={$zoneId}");
            
            if (isset($response['data']['data'])) {
                return $response['data']['data'];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error("Error fetching areas for zone {$zoneId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store all locations to database
     */
    public function storeAllLocations()
    {
        try {
            DB::beginTransaction();

            $citiesStored = 0;
            $zonesStored = 0;
            $areasStored = 0;

            // 1. Get and store cities
            $cities = $this->getCitiesFromApi();
            
            foreach ($cities as $cityData) {
                $city = PathaoCity::updateOrCreate(
                    ['city_id' => $cityData['city_id']],
                    ['city_name' => $cityData['city_name']]
                );
                $citiesStored++;

                // 2. Get and store zones for this city
                $zones = $this->getZonesFromApi($cityData['city_id']);
                
                foreach ($zones as $zoneData) {
                    $zone = PathaoZone::updateOrCreate(
                        ['zone_id' => $zoneData['zone_id']],
                        [
                            'zone_name' => $zoneData['zone_name'],
                            'city_id' => $cityData['city_id']
                        ]
                    );
                    $zonesStored++;

                    // 3. Get and store areas for this zone
                    $areas = $this->getAreasFromApi($zoneData['zone_id']);
                    
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

            DB::commit();

            return [
                'success' => true,
                'message' => 'Locations stored successfully!',
                'data' => [
                    'cities' => $citiesStored,
                    'zones' => $zonesStored,
                    'areas' => $areasStored,
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store locations error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to store locations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get cities from database
     */
    public function getCities()
    {
        return PathaoCity::orderBy('city_name')->get();
    }

    /**
     * Get zones by city ID from database
     */
    public function getZones($cityId)
    {
        return PathaoZone::where('city_id', $cityId)->orderBy('zone_name')->get();
    }

    /**
     * Get areas by zone ID from database
     */
    public function getAreas($zoneId)
    {
        return PathaoArea::where('zone_id', $zoneId)->orderBy('area_name')->get();
    }

    /**
     * Search location by name
     */
    public function searchLocation($search)
    {
        $cities = PathaoCity::where('city_name', 'LIKE', "%{$search}%")->limit(10)->get();
        $zones = PathaoZone::where('zone_name', 'LIKE', "%{$search}%")->limit(10)->get();
        $areas = PathaoArea::where('area_name', 'LIKE', "%{$search}%")->limit(10)->get();

        return [
            'cities' => $cities,
            'zones' => $zones,
            'areas' => $areas,
        ];
    }

    /**
     * Get location hierarchy by area ID
     */
    public function getLocationHierarchy($areaId)
    {
        $area = PathaoArea::with(['zone.city'])->where('area_id', $areaId)->first();
        
        if (!$area) {
            return null;
        }

        return [
            'area' => $area,
            'zone' => $area->zone,
            'city' => $area->zone?->city,
        ];
    }
}