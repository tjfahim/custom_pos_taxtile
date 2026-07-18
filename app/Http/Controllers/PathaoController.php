<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InsideDhaka;
use App\Models\PathaoArea;
use App\Models\PathaoCity;
use App\Models\PathaoZone;
use Enan\PathaoCourier\Facades\PathaoCourier;
use Enan\PathaoCourier\Requests\PathaoUserSuccessRateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PathaoController extends Controller
{
    /**
     * Display the Pathao management page
     */
    public function index()
    {
        return view('pathao.index');
    }

    public function issueToken()
{
    try {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post('https://courier-api-sandbox.pathao.com/aladdin/api/v1/issue-token', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'client_id' => env('PATHAO_CLIENT_ID'),
                'client_secret' => env('PATHAO_CLIENT_SECRET'),
                'grant_type' => 'password', // Important: use 'password'
                'username' => env('PATHAO_USERNAME'),
                'password' => env('PATHAO_PASSWORD')
            ]
        ]);
        
        $tokenData = json_decode($response->getBody(), true);
        
        
        
        return $tokenData;
        
    } catch (\Exception $e) {
        throw new \Exception('Token issuance failed: ' . $e->getMessage());
    }
}

    /**
     * Get all cities
     */
    public function getCitiesold()
    {
        try {
            $cities = PathaoCourier::GET_CITIES();
            return response()->json([
                'success'   => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get zones by city ID
     */
    public function getZonesold($cityId)
    {
        try {
            $zones = PathaoCourier::GET_ZONES($cityId);
            return response()->json([
                'success' => true,
                'data' => $zones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get areas by zone ID
     */
    public function getAreasold($zoneId)
    {
        try {
            $areas = PathaoCourier::GET_AREAS($zoneId);
            return response()->json([
                'success' => true,
                'data' => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



        public function getCities()
    {
        try {
            $cities = PathaoCity::orderBy('city_name')->get();
            
            $formattedData = $cities->map(function($city) {
                return [
                    'city_id' => (int) $city->city_id,
                    'city_name' => $city->city_name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => (object) [
                    'data' => (object) [
                        'data' => $formattedData
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get zones by city ID from database (exact API response format)
     */
    public function getZones($cityId)
    {
        try {
            $zones = PathaoZone::where('city_id', $cityId)
                ->orderBy('zone_name')
                ->get();
            
            $formattedData = $zones->map(function($zone) {
                return [
                    'zone_id' => (int) $zone->zone_id,
                    'zone_name' => $zone->zone_name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => (object) [
                    'data' => (object) [
                        'data' => $formattedData
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get areas by zone ID from database (exact API response format)
     */
    public function getAreas($zoneId)
    {
        try {
            $areas = PathaoArea::where('zone_id', $zoneId)
                ->orderBy('area_name')
                ->get();
            
            $formattedData = $areas->map(function($area) {
                return [
                    'area_id' => (int) $area->area_id,
                    'area_name' => $area->area_name,
                    'home_delivery_available' => (bool) $area->home_delivery_available,
                    'pickup_available' => (bool) $area->pickup_available
                ];
            });

            return response()->json([
                'success' => true,
                'data' => (object) [
                    'data' => (object) [
                        'data' => $formattedData
                    ]
                ],
                'message' => 'Area list fetched.',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




public function checkPhoneOld(Request $request)
{
    // Try to get phone from different sources
    $phone = $request->input('phone') ?? 
             $request->route('phone') ?? 
             ($request->isMethod('post') ? $request->get('phone') : null);

    // If still no phone, check request body for JSON
    if (!$phone && $request->isMethod('post') && $request->isJson()) {
        $data = $request->json()->all();
        $phone = $data['phone'] ?? null;
    }

    if (!$phone) {
        return response()->json([
            'success' => false,
            'error' => 'Phone number is required',
            'method' => $request->method(),
            'input' => $request->all()
        ], 400);
    }

    // Clean phone number (remove spaces, +, etc.)
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Validate phone format
    if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
        return response()->json([
            'success' => false,
            'error' => 'Invalid phone number format. Must be 11 digits starting with 013-019.',
            'phone_received' => $phone
        ], 400);
    }

    $apiKey = '5f34b001ee23080bb11bf0ea5e3684d1';
    $apiUrl = 'https://fraudchecker.link/api/v1/qc/';

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['phone' => $phone]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return response()->json([
            'success' => false,
            'error' => 'CURL Error: ' . $curlError,
            'phone' => $phone
        ], 500);
    }

    if ($httpCode !== 200) {
        return response()->json([
            'success' => false,
            'error' => 'API error',
            'code' => $httpCode,
            'phone' => $phone,
            'response' => substr($response, 0, 200)
        ], $httpCode);
    }

    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return response()->json([
            'success' => false,
            'error' => 'Invalid JSON response from API',
            'phone' => $phone,
            'raw_response' => substr($response, 0, 200)
        ], 500);
    }

    return response()->json($data);
}


public function checkPhoneHoorin(Request $request)
{
    // Try to get phone from different sources
    $phone = $request->input('phone') ?? 
             $request->route('phone') ?? 
             ($request->isMethod('post') ? $request->get('phone') : null);

    // If still no phone, check request body for JSON
    if (!$phone && $request->isMethod('post') && $request->isJson()) {
        $data = $request->json()->all();
        $phone = $data['phone'] ?? null;
    }

    if (!$phone) {
        return response()->json([
            'success' => false,
            'error' => 'Phone number is required',
            'method' => $request->method(),
            'input' => $request->all()
        ], 400);
    }

    // Clean phone number (remove spaces, +, etc.)
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Validate phone format
    if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
        return response()->json([
            'success' => false,
            'error' => 'Invalid phone number format. Must be 11 digits starting with 013-019.',
            'phone_received' => $phone
        ], 400);
    }

    // New API endpoint
    $apiKey = 'f6a19a8525d97160b6e677';
    $apiUrl = "https://dash.hoorin.com/api/courier/api?apiKey={$apiKey}&searchTerm={$phone}";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return response()->json([
            'success' => false,
            'error' => 'CURL Error: ' . $curlError,
            'phone' => $phone
        ], 500);
    }

    if ($httpCode !== 200) {
        return response()->json([
            'success' => false,
            'error' => 'API error',
            'code' => $httpCode,
            'phone' => $phone,
            'response' => substr($response, 0, 200)
        ], $httpCode);
    }

    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return response()->json([
            'success' => false,
            'error' => 'Invalid JSON response from API',
            'phone' => $phone,
            'raw_response' => substr($response, 0, 200)
        ], 500);
    }

    // Convert the new response format to the old format
    $convertedData = $this->convertToOldFormat($data, $phone);

    return response()->json($convertedData);
}

/**
 * Convert new API response format to old format with Steadfast included
 */
private function convertToOldFormat($newData, $phone)
{
    // Calculate totals
    $totalParcels = 0;
    $totalDelivered = 0;
    $totalCancel = 0;

    // Initialize APIs array with default structure including Steadfast
    $apis = [
        'Pathao' => [
            'courier_name' => 'Pathao',
            'total_parcels' => 0,
            'total_delivered_parcels' => 0,
            'total_cancelled_parcels' => 0
        ],
        'CarryBee' => [
            'courier_name' => 'Carrybee',
            'total_parcels' => 0,
            'total_delivered_parcels' => 0,
            'total_cancelled_parcels' => 0
        ],
        'Redex' => [
            'courier_name' => 'Redx',
            'total_parcels' => 0,
            'total_delivered_parcels' => 0,
            'total_cancelled_parcels' => 0
        ],
        'Steadfast' => [
            'courier_name' => 'Steadfast',
            'total_parcels' => 0,
            'total_delivered_parcels' => 0,
            'total_cancelled_parcels' => 0,
            'details' => null
        ]
    ];

    // Map new API courier names to old format
    if (isset($newData['Summaries'])) {
        // Process RedX (maps to Redex in old format)
        if (isset($newData['Summaries']['RedX'])) {
            $redx = $newData['Summaries']['RedX'];
            $apis['Redex']['total_parcels'] = $redx['Total Parcels'] ?? 0;
            $apis['Redex']['total_delivered_parcels'] = $redx['Delivered Parcels'] ?? 0;
            $apis['Redex']['total_cancelled_parcels'] = $redx['Canceled Parcels'] ?? 0;
            
            $totalParcels += $apis['Redex']['total_parcels'];
            $totalDelivered += $apis['Redex']['total_delivered_parcels'];
            $totalCancel += $apis['Redex']['total_cancelled_parcels'];
        }

        // Process Pathao
        if (isset($newData['Summaries']['Pathao'])) {
            $pathao = $newData['Summaries']['Pathao'];
            $apis['Pathao']['total_parcels'] = $pathao['Total Delivery'] ?? 0;
            $apis['Pathao']['total_delivered_parcels'] = $pathao['Successful Delivery'] ?? 0;
            $apis['Pathao']['total_cancelled_parcels'] = $pathao['Canceled Delivery'] ?? 0;
            
            $totalParcels += $apis['Pathao']['total_parcels'];
            $totalDelivered += $apis['Pathao']['total_delivered_parcels'];
            $totalCancel += $apis['Pathao']['total_cancelled_parcels'];
        }

        // Process Carrybee
        if (isset($newData['Summaries']['Carrybee'])) {
            $carrybee = $newData['Summaries']['Carrybee'];
            $apis['CarryBee']['total_parcels'] = $carrybee['Total Parcels'] ?? 0;
            $apis['CarryBee']['total_delivered_parcels'] = $carrybee['Delivered Parcels'] ?? 0;
            $apis['CarryBee']['total_cancelled_parcels'] = $carrybee['Canceled Parcels'] ?? 0;
            $apis['CarryBee']['status'] = 'found';
            
            $totalParcels += $apis['CarryBee']['total_parcels'];
            $totalDelivered += $apis['CarryBee']['total_delivered_parcels'];
            $totalCancel += $apis['CarryBee']['total_cancelled_parcels'];
        } else {
            $apis['CarryBee']['status'] = 'notfound';
        }

        // Process Steadfast
        if (isset($newData['Summaries']['Steadfast'])) {
            $steadfast = $newData['Summaries']['Steadfast'];
            $apis['Steadfast']['total_parcels'] = $steadfast['Total Parcels'] ?? 0;
            $apis['Steadfast']['total_delivered_parcels'] = $steadfast['Delivered Parcels'] ?? 0;
            $apis['Steadfast']['total_cancelled_parcels'] = $steadfast['Canceled Parcels'] ?? 0;
            
            // Add details if they exist
            if (isset($steadfast['Details']) && is_array($steadfast['Details'])) {
                $apis['Steadfast']['details'] = $steadfast['Details'];
            }
            
            $totalParcels += $apis['Steadfast']['total_parcels'];
            $totalDelivered += $apis['Steadfast']['total_delivered_parcels'];
            $totalCancel += $apis['Steadfast']['total_cancelled_parcels'];
        }
    }

    // Build the response in old format
    return [
        'mobile_number' => $phone,
        'total_parcels' => $totalParcels,
        'total_delivered' => $totalDelivered,
        'total_cancel' => $totalCancel,
        'apis' => $apis
    ];
}

public function checkPhone(Request $request)
{
    // Try to get phone from different sources
    $phone = $request->input('phone') ?? 
             $request->route('phone') ?? 
             ($request->isMethod('post') ? $request->get('phone') : null);

    // If still no phone, check request body for JSON
    if (!$phone && $request->isMethod('post') && $request->isJson()) {
        $data = $request->json()->all();
        $phone = $data['phone'] ?? null;
    }

    if (!$phone) {
        return response()->json([
            'success' => false,
            'error' => 'Phone number is required',
            'method' => $request->method(),
            'input' => $request->all()
        ], 400);
    }

    // Clean phone number (remove spaces, +, etc.)
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Validate phone format
    if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
        return response()->json([
            'success' => false,
            'error' => 'Invalid phone number format. Must be 11 digits starting with 013-019.',
            'phone_received' => $phone
        ], 400);
    }

    // New API integration
    $apiKey = 'FwMdGn4NCK5da1ljQs8QbrsajDwhhawqh31Qh7tu2xg0nkVdcWteQVc063jR';
    $apiUrl = 'https://api.bdcourier.com/courier-check';
    
    $postData = json_encode(['phone' => $phone]);
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'Content-Length: ' . strlen($postData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return response()->json([
            'success' => false,
            'error' => 'CURL Error: ' . $curlError,
            'phone' => $phone
        ], 500);
    }

    if ($httpCode !== 200) {
        return response()->json([
            'success' => false,
            'error' => 'API error',
            'code' => $httpCode,
            'phone' => $phone,
            'response' => substr($response, 0, 200)
        ], $httpCode);
    }

    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return response()->json([
            'success' => false,
            'error' => 'Invalid JSON response from API',
            'phone' => $phone,
            'raw_response' => substr($response, 0, 200)
        ], 500);
    }

    // Check if phone number was found
    if (isset($data['status']) && $data['status'] === 'error') {
        return response()->json([
            'success' => false,
            'error' => $data['message'] ?? 'Phone number not found',
            'phone' => $phone
        ], 404);
    }

    // Convert the new response format to the old format
    $convertedData = $this->convertNewApiToOldFormat($data, $phone);

    return response()->json($convertedData);
}

private function convertNewApiToOldFormat($newApiData, $phone)
{
    // Initialize with default values
    $result = [
        'mobile_number' => $phone,
        'total_parcels' => 0,
        'total_delivered' => 0,
        'total_cancel' => 0,
        'apis' => []
    ];
    
    // Map courier names from new API to old format
    $courierMapping = [
        'pathao' => 'Pathao',
        'steadfast' => 'Steadfast',
        'parceldex' => 'ParcelDex',
        'redx' => 'Redex',
        'paperfly' => 'Paperfly',
        'carrybee' => 'CarryBee'
    ];
    
    // Check if we have data from the new API
    if (isset($newApiData['status']) && $newApiData['status'] === 'success' && isset($newApiData['data'])) {
        $data = $newApiData['data'];
        
        // Process summary data for totals
        if (isset($data['summary'])) {
            $result['total_parcels'] = $data['summary']['total_parcel'] ?? 0;
            $result['total_delivered'] = $data['summary']['success_parcel'] ?? 0;
            $result['total_cancel'] = $data['summary']['cancelled_parcel'] ?? 0;
        }
        
        // Process individual courier data
        foreach ($courierMapping as $apiKey => $courierName) {
            if (isset($data[$apiKey])) {
                $courierData = $data[$apiKey];
                $result['apis'][$courierName] = [
                    'courier_name' => $courierData['name'] ?? $courierName,
                    'total_parcels' => $courierData['total_parcel'] ?? 0,
                    'total_delivered_parcels' => $courierData['success_parcel'] ?? 0,
                    'total_cancelled_parcels' => $courierData['cancelled_parcel'] ?? 0,
                    'success_ratio' => $courierData['success_ratio'] ?? 0
                ];
            } else {
                // Add empty entry for couriers not in response
                $result['apis'][$courierName] = [
                    'courier_name' => $courierName,
                    'total_parcels' => 0,
                    'total_delivered_parcels' => 0,
                    'total_cancelled_parcels' => 0,
                    'status' => 'found'
                ];
            }
        }
    }
    
    // Add reports data if available
    if (isset($newApiData['reports']) && !empty($newApiData['reports'])) {
        $result['reports'] = $newApiData['reports'];
    }
    
    return $result;
}

public function checkCustomerByPhone($phone)
{
    try {
        // Clean phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Validate phone format
        if (!preg_match('/^01[3-9]\d{8}$/', $cleanPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number format. Must be 11 digits starting with 013-019.',
                'phone_received' => $phone
            ], 400);
        }
        
        // Check if customer exists
        $customer = Customer::where('phone_number_1', $cleanPhone)
            ->orWhere('phone_number_2', $cleanPhone)
            ->first(['id', 'name', 'phone_number_1', 'phone_number_2','merchant_order_id', 'full_address', 'delivery_area', 'created_at']);
        
        if ($customer) {
            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer found'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'customer' => null,
            'message' => 'No customer found with this phone number'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Customer check error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error checking customer: ' . $e->getMessage()
        ], 500);
    }
}


  

    public function storeAllLocationsPaginated()
    {
        try {
            // Increase execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '2048M');

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

            // Process cities in chunks
            $cityChunks = array_chunk($cities, 5);
            
            foreach ($cityChunks as $chunkIndex => $cityChunk) {
                foreach ($cityChunk as $cityData) {
                    // Store or update city
                    $city = PathaoCity::updateOrCreate(
                        ['city_id' => $cityData['city_id']],
                        ['city_name' => $cityData['city_name']]
                    );
                    $citiesStored++;

                    // 2. Get zones for this city from API
                    $zonesResponse = PathaoCourier::GET_ZONES($cityData['city_id']);
                    
                    if (isset($zonesResponse['data']['data'])) {
                        $zones = $zonesResponse['data']['data'];

                        foreach ($zones as $zoneData) {
                            // Store or update zone
                            $zone = PathaoZone::updateOrCreate(
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
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All locations stored successfully!',
                'data' => [
                    'cities_stored' => $citiesStored,
                    'zones_stored' => $zonesStored,
                    'areas_stored' => $areasStored,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store locations error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to store locations: ' . $e->getMessage()
            ], 500);
        }
    }
       public function syncCitiesOnly()
    {
        try {
            $citiesResponse = PathaoCourier::GET_CITIES();
            
            if (!isset($citiesResponse['data']['data'])) {
                throw new \Exception('Invalid response from Pathao API');
            }

            $cities = $citiesResponse['data']['data'];
            $stored = 0;

            foreach ($cities as $cityData) {
                PathaoCity::updateOrCreate(
                    ['city_id' => $cityData['city_id']],
                    ['city_name' => $cityData['city_name']]
                );
                $stored++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$stored} cities synced successfully!",
                'data' => ['cities_synced' => $stored]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync cities: ' . $e->getMessage()
            ], 500);
        }
    }
      public function syncZonesForCity($cityId)
    {
        try {
            $city = PathaoCity::where('city_id', $cityId)->first();
            
            if (!$city) {
                // Create city if not exists
                $city = PathaoCity::create([
                    'city_id' => $cityId,
                    'city_name' => 'City ID: ' . $cityId
                ]);
            }

            $zonesResponse = PathaoCourier::GET_ZONES($cityId);
            
            if (!isset($zonesResponse['data']['data'])) {
                throw new \Exception('No zones found for city ID: ' . $cityId);
            }

            $zones = $zonesResponse['data']['data'];
            $stored = 0;

            foreach ($zones as $zoneData) {
                PathaoZone::updateOrCreate(
                    ['zone_id' => $zoneData['zone_id']],
                    [
                        'zone_name' => $zoneData['zone_name'],
                        'city_id' => $cityId
                    ]
                );
                $stored++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$stored} zones synced for city!",
                'data' => ['zones_synced' => $stored]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync zones: ' . $e->getMessage()
            ], 500);
        }
    }
    public function syncAreasForZone($zoneId)
    {
        try {
            $areasResponse = PathaoCourier::GET_AREAS($zoneId);
            
            if (!isset($areasResponse['data']['data'])) {
                throw new \Exception('No areas found for zone ID: ' . $zoneId);
            }

            $areas = $areasResponse['data']['data'];
            $stored = 0;

            foreach ($areas as $areaData) {
                PathaoArea::updateOrCreate(
                    ['area_id' => $areaData['area_id']],
                    [
                        'area_name' => $areaData['area_name'],
                        'zone_id' => $zoneId,
                        'home_delivery_available' => $areaData['home_delivery_available'] ?? true,
                        'pickup_available' => $areaData['pickup_available'] ?? true,
                    ]
                );
                $stored++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$stored} areas synced for zone!",
                'data' => ['areas_synced' => $stored]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync areas: ' . $e->getMessage()
            ], 500);
        }
    }
      public function getSyncStatus()
    {
        // Check if queue is running
        $queueSize = DB::table('jobs')->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'queue_size' => $queueSize,
                'cities' => PathaoCity::count(),
                'zones' => PathaoZone::count(),
                'areas' => PathaoArea::count(),
            ]
        ]);
    }
    /**
     * Search locations by name
     */
    public function autoSubmitLocation(Request $request)
{
    try {
        $search = trim($request->search);
        
        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Search term is required'
            ], 400);
        }

        $words = preg_split('/[\s,|&\-\/.]+/', $search);
        $words = array_values(array_filter($words, fn($w) => mb_strlen($w) >= 2));

        if (empty($words)) {
            return response()->json([
                'success' => false, 
                'message' => 'Search term is too short'
            ]);
        }

        // 1. Find the best CITY match across all words
        $cities = PathaoCity::all();
        $bestCity = null;
        $bestCityPercent = 0;
        $bestCityWord = '';

        foreach ($words as $word) {
            foreach ($cities as $city) {
                $percent = $this->calculateMatchPercentage($word, $city->city_name);
                if ($percent > $bestCityPercent) {
                    $bestCityPercent = $percent;
                    $bestCity = $city;
                    $bestCityWord = $word;
                }
            }
        }

        // Threshold: below this we don't trust the city guess at all
        if (!$bestCity || $bestCityPercent < 60) {
            return response()->json([
                'success' => false, 
                'message' => 'No confident city match'
            ]);
        }

        // 2. Find the best ZONE match, scoped to that city only
        $remainingWords = array_values(array_diff($words, [$bestCityWord]));
        $wordsForZone = !empty($remainingWords) ? $remainingWords : $words;

        $zones = PathaoZone::where('city_id', $bestCity->city_id)->get();
        $bestZone = null;
        $bestZonePercent = 0;

        foreach ($zones as $zone) {
            foreach ($wordsForZone as $word) {
                $percent = $this->calculateMatchPercentage($word, $zone->zone_name);
                if ($percent > $bestZonePercent) {
                    $bestZonePercent = $percent;
                    $bestZone = $zone;
                }
            }
        }

        $result = [
            'success' => true,
            'city' => [
                'id' => $bestCity->city_id,
                'name' => $bestCity->city_name,
                'match_percent' => $bestCityPercent,
            ],
            'zone' => null,
        ];

        // Threshold for zone confidence
        if ($bestZone && $bestZonePercent >= 55) {
            $result['zone'] = [
                'id' => $bestZone->zone_id,
                'name' => $bestZone->zone_name,
                'match_percent' => $bestZonePercent,
            ];
        }

        return response()->json($result);

    } catch (\Exception $e) {
        Log::error('Search error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Search failed: ' . $e->getMessage()
        ], 500);
    }
}
    public function searchLocation(Request $request)
    {
        try {
            $search = trim($request->search);
            
            if (empty($search)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search term is required'
                ], 400);
            }

            $allResults = [];
            $searchLower = strtolower($search);
            
            // Split search into words
            $words = preg_split('/[\s,|&-]+/', $search);
            $words = array_filter($words, function($w) { return strlen($w) >= 2; });
            $words = array_values($words);
            
            if (empty($words)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search term is too short'
                ], 400);
            }

            // 1. FIRST: Try to find exact matches for the entire search phrase
            // Check exact matches in zones (priority)
            $exactZones = PathaoZone::whereRaw('LOWER(zone_name) = ?', [$searchLower])
                ->orWhere('zone_id', $search)
                ->get();
            
            foreach ($exactZones as $zone) {
                $allResults[] = $this->formatZoneResult($zone, 100);
            }
            
            // Check exact matches in areas
            $exactAreas = PathaoArea::whereRaw('LOWER(area_name) = ?', [$searchLower])
                ->orWhere('area_id', $search)
                ->get();
            
            foreach ($exactAreas as $area) {
                $allResults[] = $this->formatAreaResult($area, 100);
            }
            
            // Check exact matches in cities
            $exactCities = PathaoCity::whereRaw('LOWER(city_name) = ?', [$searchLower])
                ->orWhere('city_id', $search)
                ->get();
            
            foreach ($exactCities as $city) {
                $allResults[] = $this->formatCityResult($city, 100);
            }

            // 2. If exact matches found, return them first
            if (!empty($allResults)) {
                // Also search for related items with high match
                $this->addRelatedMatches($allResults, $words);
                
                // Sort and return
                $allResults = $this->uniqueResults($allResults);
                usort($allResults, function($a, $b) {
                    return $b['match_percent'] - $a['match_percent'];
                });
                $allResults = array_slice($allResults, 0, 50);
                
                return $this->formatSearchResponse($allResults);
            }

            // 3. SECOND: Try to find city + location combination
            $cityMatch = null;
            $locationTerms = [];
            
            // Find the best city match
            $allCities = PathaoCity::all();
            $bestCityMatch = null;
            $bestCityPercent = 0;
            $bestCityWord = '';
            
            foreach ($words as $word) {
                foreach ($allCities as $city) {
                    $percent = $this->calculateMatchPercentage($word, $city->city_name);
                    if ($percent > $bestCityPercent) {
                        $bestCityPercent = $percent;
                        $bestCityMatch = $city;
                        $bestCityWord = $word;
                    }
                }
            }
            
            // If we found a city with good match, search within that city
            if ($bestCityMatch && $bestCityPercent >= 70) {
                $cityMatch = $bestCityMatch;
                $locationTerms = array_diff($words, [$bestCityWord]);
                $locationTerms = array_values($locationTerms);
                
                // If no other terms, just return the city
                if (empty($locationTerms)) {
                    $allResults[] = $this->formatCityResult($cityMatch, $bestCityPercent);
                } else {
                    // Search for zones in this city that match the location terms
                    $zones = PathaoZone::where('city_id', $cityMatch->city_id)->get();
                    
                    foreach ($zones as $zone) {
                        $bestZonePercent = 0;
                        $zoneMatched = false;
                        
                        foreach ($locationTerms as $term) {
                            $percent = $this->calculateMatchPercentage($term, $zone->zone_name);
                            if ($percent >= 70) {
                                $zoneMatched = true;
                                $bestZonePercent = max($bestZonePercent, $percent);
                            }
                        }
                        
                        // If zone matches, add it
                        if ($zoneMatched) {
                            $allResults[] = $this->formatZoneResult($zone, $bestZonePercent);
                        }
                        
                        // Also search areas in this zone
                        $areas = PathaoArea::where('zone_id', $zone->zone_id)->get();
                        foreach ($areas as $area) {
                            $bestAreaPercent = 0;
                            $areaMatched = false;
                            
                            foreach ($locationTerms as $term) {
                                $percent = $this->calculateMatchPercentage($term, $area->area_name);
                                if ($percent >= 70) {
                                    $areaMatched = true;
                                    $bestAreaPercent = max($bestAreaPercent, $percent);
                                }
                            }
                            
                            if ($areaMatched) {
                                $allResults[] = $this->formatAreaResult($area, $bestAreaPercent);
                            }
                        }
                    }
                }
            }
            
            // 4. If no results with city context, do a general search
            if (empty($allResults)) {
                // Search each word individually with high priority
                foreach ($words as $word) {
                    if (strlen($word) < 2) continue;
                    
                    // Search zones with high priority
                    $wordZones = PathaoZone::where('zone_name', 'LIKE', "%{$word}%")
                        ->orWhere('zone_name', 'LIKE', "{$word}%")
                        ->limit(20)
                        ->get();
                    
                    foreach ($wordZones as $zone) {
                        $percent = $this->calculateMatchPercentage($word, $zone->zone_name);
                        if ($percent >= 70) {
                            $allResults[] = $this->formatZoneResult($zone, $percent);
                        }
                    }
                    
                    // Search areas
                    $wordAreas = PathaoArea::where('area_name', 'LIKE', "%{$word}%")
                        ->orWhere('area_name', 'LIKE', "{$word}%")
                        ->limit(20)
                        ->get();
                    
                    foreach ($wordAreas as $area) {
                        $percent = $this->calculateMatchPercentage($word, $area->area_name);
                        if ($percent >= 70) {
                            $allResults[] = $this->formatAreaResult($area, $percent);
                        }
                    }
                    
                    // Search cities
                    $wordCities = PathaoCity::where('city_name', 'LIKE', "%{$word}%")
                        ->orWhere('city_name', 'LIKE', "{$word}%")
                        ->limit(10)
                        ->get();
                    
                    foreach ($wordCities as $city) {
                        $percent = $this->calculateMatchPercentage($word, $city->city_name);
                        if ($percent >= 70) {
                            $allResults[] = $this->formatCityResult($city, $percent);
                        }
                    }
                }
            }

            // 5. If still no results, try fuzzy matching with all data
            if (empty($allResults)) {
                // Try with all zones
                $allZones = PathaoZone::all();
                foreach ($allZones as $zone) {
                    $bestPercent = 0;
                    foreach ($words as $word) {
                        $percent = $this->calculateMatchPercentage($word, $zone->zone_name);
                        $bestPercent = max($bestPercent, $percent);
                    }
                    if ($bestPercent >= 60) {
                        $allResults[] = $this->formatZoneResult($zone, $bestPercent);
                    }
                }
                
                // Try with all areas
                $allAreas = PathaoArea::all();
                foreach ($allAreas as $area) {
                    $bestPercent = 0;
                    foreach ($words as $word) {
                        $percent = $this->calculateMatchPercentage($word, $area->area_name);
                        $bestPercent = max($bestPercent, $percent);
                    }
                    if ($bestPercent >= 60) {
                        $allResults[] = $this->formatAreaResult($area, $bestPercent);
                    }
                }
            }

            // Remove duplicates and sort
            $allResults = $this->uniqueResults($allResults);
            usort($allResults, function($a, $b) {
                return $b['match_percent'] - $a['match_percent'];
            });
            $allResults = array_slice($allResults, 0, 50);
            
            return $this->formatSearchResponse($allResults);

        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate match percentage between search term and text
     * Uses Levenshtein distance and similar_text
     */
    private function calculateMatchPercentage($search, $text)
    {
        $search = trim(strtolower($search));
        $text = trim(strtolower($text));
        
        if (empty($search) || empty($text)) {
            return 0;
        }
        
        // Exact match
        if ($search === $text) {
            return 100;
        }
        
        // Starts with
        if (strpos($text, $search) === 0) {
            return 95;
        }
        
        // Contains with space
        if (strpos($text, ' ' . $search) !== false) {
            return 90;
        }
        
        // Contains
        if (strpos($text, $search) !== false) {
            return 85;
        }
        
        // Levenshtein distance
        $distance = levenshtein($search, $text);
        $maxLen = max(strlen($search), strlen($text));
        if ($maxLen > 0) {
            $similarity = (1 - $distance / $maxLen) * 100;
            if ($similarity >= 70) {
                return round($similarity);
            }
        }
        
        // similar_text
        similar_text($search, $text, $percent);
        
        // Bonus for similar length
        $lengthDiff = abs(strlen($search) - strlen($text));
        if ($lengthDiff <= 2 && $percent >= 60) {
            $percent += 10;
        }
        
        return round($percent);
    }

    /**
     * Add related matches to results
     */
    private function addRelatedMatches(&$results, $words)
    {
        $existingIds = [];
        foreach ($results as $result) {
            $existingIds[] = $result['type'] . '_' . $result['id'];
        }
        
        foreach ($words as $word) {
            if (strlen($word) < 2) continue;
            
            // Search zones
            $zones = PathaoZone::where('zone_name', 'LIKE', "%{$word}%")
                ->limit(10)
                ->get();
            
            foreach ($zones as $zone) {
                $key = 'zone_' . $zone->zone_id;
                if (!in_array($key, $existingIds)) {
                    $percent = $this->calculateMatchPercentage($word, $zone->zone_name);
                    if ($percent >= 85) {
                        $results[] = $this->formatZoneResult($zone, $percent);
                        $existingIds[] = $key;
                    }
                }
            }
            
            // Search areas
            $areas = PathaoArea::where('area_name', 'LIKE', "%{$word}%")
                ->limit(10)
                ->get();
            
            foreach ($areas as $area) {
                $key = 'area_' . $area->area_id;
                if (!in_array($key, $existingIds)) {
                    $percent = $this->calculateMatchPercentage($word, $area->area_name);
                    if ($percent >= 85) {
                        $results[] = $this->formatAreaResult($area, $percent);
                        $existingIds[] = $key;
                    }
                }
            }
        }
    }

    /**
     * Format search response
     */
    private function formatSearchResponse($results)
    {
        $exactCount = count(array_filter($results, function($r) { return $r['match_percent'] == 100; }));
        $highCount = count(array_filter($results, function($r) { return $r['match_percent'] >= 80 && $r['match_percent'] < 100; }));
        $mediumCount = count(array_filter($results, function($r) { return $r['match_percent'] < 80; }));

        return response()->json([
            'success' => true,
            'data' => [
                'results' => $results,
                'summary' => [
                    'total' => count($results),
                    'exact' => $exactCount,
                    'high' => $highCount,
                    'medium' => $mediumCount,
                ]
            ]
        ]);
    }

    /**
     * Format city result with hierarchy
     */
    private function formatCityResult($city, $matchPercent = 100)
    {
        return [
            'type' => 'city',
            'id' => $city->city_id,
            'name' => $city->city_name,
            'display_name' => $city->city_name,
            'full_address' => $city->city_name,
            'match_percent' => min(100, $matchPercent),
            'hierarchy' => [
                'city' => $city->city_name,
                'city_id' => $city->city_id,
                'zone' => null,
                'zone_id' => null,
                'area' => null,
                'area_id' => null,
            ]
        ];
    }

    /**
     * Format zone result with hierarchy
     */
    private function formatZoneResult($zone, $matchPercent = 100)
    {
        $city = $zone->city;
        $displayName = $city ? $city->city_name . ' → ' . $zone->zone_name : $zone->zone_name;
        
        return [
            'type' => 'zone',
            'id' => $zone->zone_id,
            'name' => $zone->zone_name,
            'display_name' => $displayName,
            'full_address' => $displayName,
            'match_percent' => min(100, $matchPercent),
            'hierarchy' => [
                'city' => $city ? $city->city_name : null,
                'city_id' => $city ? $city->city_id : null,
                'zone' => $zone->zone_name,
                'zone_id' => $zone->zone_id,
                'area' => null,
                'area_id' => null,
            ]
        ];
    }

    /**
     * Format area result with hierarchy
     */
    private function formatAreaResult($area, $matchPercent = 100)
    {
        $zone = $area->zone;
        $city = $zone ? $zone->city : null;
        
        $displayName = '';
        if ($city) $displayName .= $city->city_name . ' → ';
        if ($zone) $displayName .= $zone->zone_name . ' → ';
        $displayName .= $area->area_name;
        
        return [
            'type' => 'area',
            'id' => $area->area_id,
            'name' => $area->area_name,
            'display_name' => $displayName,
            'full_address' => $displayName,
            'match_percent' => min(100, $matchPercent),
            'home_delivery_available' => $area->home_delivery_available,
            'pickup_available' => $area->pickup_available,
            'hierarchy' => [
                'city' => $city ? $city->city_name : null,
                'city_id' => $city ? $city->city_id : null,
                'zone' => $zone ? $zone->zone_name : null,
                'zone_id' => $zone ? $zone->zone_id : null,
                'area' => $area->area_name,
                'area_id' => $area->area_id,
            ]
        ];
    }

    /**
     * Remove duplicate results
     */
    private function uniqueResults($results)
    {
        $seen = [];
        $unique = [];
        
        foreach ($results as $result) {
            $key = $result['type'] . '_' . $result['id'];
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $unique[] = $result;
            }
        }
        
        return $unique;
    }


    /**
     * Get full location hierarchy by area ID
     */
    public function getLocationHierarchy($areaId)
    {
        try {
            $area = PathaoArea::with(['zone.city'])
                             ->where('area_id', $areaId)
                             ->first();
            
            if (!$area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'area' => $area,
                    'zone' => $area->zone,
                    'city' => $area->zone?->city,
                    'full_address' => $area->zone?->city?->city_name . ', ' . 
                                     $area->zone?->zone_name . ', ' . 
                                     $area->area_name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync a single city with its zones and areas
     */
    public function syncCity($cityId)
    {
        try {
            DB::beginTransaction();

            // Get city details from API (optional - you might not have this endpoint)
            // For now, we'll just sync zones and areas for the given city ID
            
            $zonesResponse = PathaoCourier::GET_ZONES($cityId);
            
            if (!isset($zonesResponse['data']['data'])) {
                throw new \Exception('No zones found for city ID: ' . $cityId);
            }

            $zones = $zonesResponse['data']['data'];
            $zonesSynced = 0;
            $areasSynced = 0;

            foreach ($zones as $zoneData) {
                $zone = PathaoZone::updateOrCreate(
                    ['zone_id' => $zoneData['zone_id']],
                    [
                        'zone_name' => $zoneData['zone_name'],
                        'city_id' => $cityId
                    ]
                );
                $zonesSynced++;

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
                        $areasSynced++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'City synced successfully!',
                'data' => [
                    'city_id' => $cityId,
                    'zones_synced' => $zonesSynced,
                    'areas_synced' => $areasSynced,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync city: ' . $e->getMessage()
            ], 500);
        }
    }
    public function manage()
{
    return view('pathao.manage');
}

/**
 * Get statistics for dashboard
 */
public function getStatistics()
{
    try {
        $cities = PathaoCity::count();
        $zones = PathaoZone::count();
        $areas = PathaoArea::count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'cities' => $cities,
                'zones' => $zones,
                'areas' => $areas,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}



  public function getDeliveryChargeByZone($zoneId, $totalQuantity = 1)
    {
        try {
            // Validate total quantity
            if ($totalQuantity < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total quantity must be at least 1'
                ], 400);
            }

            // Get delivery charge
            $result = InsideDhaka::getDeliveryChargeByZone($zoneId, $totalQuantity);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get delivery charge: ' . $e->getMessage()
            ], 500);
        }
    }
}