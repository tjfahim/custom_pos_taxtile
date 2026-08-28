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
    public function autoSubmitLocationold(Request $request)
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
    private function calculateMatchPercentageOld($search, $text)
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
 
        $search = $this->transliterateBanglaToEnglish($search);
 
        $words = preg_split('/[\s,|&\-\/.]+/', $search);
        $words = array_values(array_filter($words, function ($w) {
            return mb_strlen($w) >= 2 && !ctype_digit($w);
        }));
 
        if (empty($words)) {
            return response()->json([
                'success' => false,
                'message' => 'Search term is too short'
            ]);
        }
 
        $cities = PathaoCity::all();
        $zones = PathaoZone::all();
        $areas = PathaoArea::all();
 
        // Fuzzy thresholds. Zone/area names get more tolerance for a typo
        // (85%) since a single wrong/missing letter is common and the
        // match is scoped enough to be safe. City names get a stricter
        // floor (95%) since they're short/generic and a wrong city is a
        // much worse mistake than a wrong zone guess.
        $zoneAreaTop = 99;
        $zoneAreaBottom = 85;
        $cityTop = 99;
        $cityBottom = 95;
 
        $matchedCity = null;
        $matchedZone = null;
        $cityMatchPercent = 0;
        $zoneMatchPercent = 0;
 
        // ---------- STEP 1: exact city match ----------
        $cityHit = $this->findExactWordMatch($words, $cities, 'city_name');
        if ($cityHit) {
            $matchedCity = $cityHit['item'];
            $cityMatchPercent = 100;
        }
 
        // ---------- STEP 2: exact zone match, ACROSS ALL CITIES ----------
        // Tried before any city guessing — most real addresses name a
        // neighbourhood/zone, not a city, so this is usually the strongest
        // signal available.
        if (!$matchedCity) {
            $zoneHit = $this->findExactWordMatch($words, $zones, 'zone_name');
        if ($zoneHit) {
    $city = PathaoCity::where(
        'city_id',
        $zoneHit['item']->city_id
    )->first();

    if ($city) {
        $matchedCity = $city;

        // City was inferred from the zone.
        // Do NOT falsely report 100%.
        $cityMatchPercent = $zoneHit['percent'];

        $matchedZone = $zoneHit['item'];
        $zoneMatchPercent = $zoneHit['percent'];
    }
}
        }
 
        // ---------- STEP 3: exact area match, ACROSS ALL CITIES ----------
        if (!$matchedCity) {
            $areaHit = $this->findExactWordMatch($words, $areas, 'area_name');
            if ($areaHit) {
                $zone = $zones->firstWhere('zone_id', $areaHit['item']->zone_id);
                if ($zone) {
                    $city = PathaoCity::where('city_id', $zone->city_id)->first();
                    if ($city) {
                        $matchedCity = $city;
                        $cityMatchPercent = 100;
                        $matchedZone = $zone;
                        $zoneMatchPercent = 100;
                    }
                }
            }
        }
 
        // ---------- STEP 4: fuzzy zone match, ACROSS ALL CITIES ----------
        // This is what catches "kalyanpur" -> "Kallyanpur" (~90% similar).
        if (!$matchedCity) {
            $zoneHit = $this->fuzzyMatchWithLadder($words, $zones, 'zone_name', $zoneAreaTop, $zoneAreaBottom);
            if ($zoneHit) {
                $city = PathaoCity::where('city_id', $zoneHit['item']->city_id)->first();
                if ($city) {
                    $matchedCity = $city;
                    $cityMatchPercent = 100; // city is deterministic once the zone is known
                    $matchedZone = $zoneHit['item'];
                    $zoneMatchPercent = $zoneHit['percent'];
                }
            }
        }
 
        // ---------- STEP 5: fuzzy area match, ACROSS ALL CITIES ----------
        if (!$matchedCity) {
            $areaHit = $this->fuzzyMatchWithLadder($words, $areas, 'area_name', $zoneAreaTop, $zoneAreaBottom);
            if ($areaHit) {
                $zone = $zones->firstWhere('zone_id', $areaHit['item']->zone_id);
                if ($zone) {
                    $city = PathaoCity::where('city_id', $zone->city_id)->first();
                    if ($city) {
                        $matchedCity = $city;
                        $cityMatchPercent = 100;
                        $matchedZone = $zone;
                        $zoneMatchPercent = $areaHit['percent'];
                    }
                }
            }
        }
 
        // ---------- STEP 6 (last resort): fuzzy CITY match ----------
        // Only reached if the search term didn't resemble any real
        // zone/area at all. Stricter threshold (95%) on purpose.
        if (!$matchedCity) {
            $cityHit = $this->fuzzyMatchWithLadder($words, $cities, 'city_name', $cityTop, $cityBottom);
            if ($cityHit) {
                $matchedCity = $cityHit['item'];
                $cityMatchPercent = $cityHit['percent'];
            }
        }
 
        if (!$matchedCity) {
            return response()->json([
                'success' => false,
                'message' => 'No confident city match'
            ]);
        }
 
        // ---------- ZONE RESOLUTION FOR A CITY FOUND VIA STEP 1 OR 6 ----------
        // (Steps 2-5 already resolved the zone directly, so this is
        // skipped for those.)
        if (!$matchedZone) {
            $zonesInCity = $zones->where('city_id', $matchedCity->city_id)->values();
 
            $zoneHit = $this->findExactWordMatch($words, $zonesInCity, 'zone_name');
 
            if (!$zoneHit) {
                $areasInCity = $areas->whereIn('zone_id', $zonesInCity->pluck('zone_id'));
                $areaHit = $this->findExactWordMatch($words, $areasInCity, 'area_name');
                if ($areaHit) {
                    $zone = $zonesInCity->firstWhere('zone_id', $areaHit['item']->zone_id);
                    if ($zone) {
                        $zoneHit = ['item' => $zone, 'word' => $areaHit['word'], 'percent' => 100];
                    }
                }
            }
 
            if ($zoneHit) {
                $matchedZone = $zoneHit['item'];
                $zoneMatchPercent = $zoneHit['percent'] ?? 100;
            } else {
                $zoneHit = $this->fuzzyMatchWithLadder($words, $zonesInCity, 'zone_name', $zoneAreaTop, $zoneAreaBottom);
                if ($zoneHit) {
                    $matchedZone = $zoneHit['item'];
                    $zoneMatchPercent = $zoneHit['percent'];
                }
            }
        }
 
        $result = [
            'success' => true,
            'city' => [
                'id' => $matchedCity->city_id,
                'name' => $matchedCity->city_name,
                'match_percent' => $cityMatchPercent,
            ],
            'zone' => null,
        ];
 
        if ($matchedZone) {
            $result['zone'] = [
                'id' => $matchedZone->zone_id,
                'name' => $matchedZone->zone_name,
                'match_percent' => $zoneMatchPercent,
            ];
        }
 
        return response()->json($result);
 
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Search failed: ' . $e->getMessage()
        ], 500);
    }
}
 
/**
 * Replaces your existing calculateMatchPercentage(). Bounded 0-100,
 * standard normalized Levenshtein similarity. Exact matches short-circuit
 * to 100 without running Levenshtein at all.
 */
private function calculateMatchPercentage($str1, $str2)
{
    $str1 = mb_strtolower(trim((string) $str1));
    $str2 = mb_strtolower(trim((string) $str2));
 
    if ($str1 === $str2) {
        return 100;
    }
 
    $len1 = mb_strlen($str1);
    $len2 = mb_strlen($str2);
 
    if ($len1 === 0 || $len2 === 0) {
        return 0;
    }
 
    // levenshtein() works on bytes, not multibyte-safe — fine here since
    // by this point everything has already been transliterated to Latin
    // script (see transliterateBanglaToEnglish()).
    $distance = levenshtein($str1, $str2);
    $maxLen = max($len1, $len2);
 
    $percent = (1 - ($distance / $maxLen)) * 100;
 
    return max(0, round($percent, 2));
}
 
/**
 * Lowercase + strip everything except letters/digits, for strict
 * equality comparisons (step 1 / step 2 / 3a / 3b above).
 */
private function normalizeForMatch($str)
{
    $str = mb_strtolower(trim((string) $str));
    return preg_replace('/[^\p{L}\p{N}]+/u', '', $str);
}
 
/**
 * Scan $words IN ORDER against $collection's $nameField for an exact
 * (normalized) match. Returns the first hit — word order beats
 * collection order.
 */
private function findExactWordMatch(array $words, $collection, $nameField)
{
    foreach ($words as $word) {
        foreach ($collection as $item) {
            if ($this->normalizeForMatch($word) === $this->normalizeForMatch($item->{$nameField})) {
                return ['item' => $item, 'word' => $word];
            }
        }
    }
    return null;
}
 
/**
 * For a single confidence threshold: walk $words in order, and for the
 * first word whose best fuzzy match against $collection clears
 * $minThreshold, return that match. Does NOT keep searching for a
 * globally-better match once one word qualifies.
 */

private function findBestWordMatch(
    array $words,
    $collection,
    $nameField,
    $minPercent = 80,
    $minMargin = 5
) {
    $candidates = [];

    foreach ($words as $wordIndex => $word) {

        $normalizedWord = $this->normalizeForMatch($word);

        if ($normalizedWord === '') {
            continue;
        }

        foreach ($collection as $item) {

            $name = $item->{$nameField};

            $percent = $this->calculateMatchPercentage(
                $normalizedWord,
                $name
            );

            $distance = levenshtein(
                $normalizedWord,
                $this->normalizeForMatch($name)
            );

            $wordLength = mb_strlen($normalizedWord);

            /*
             * We only want 1-2 character errors.
             */
            if ($distance > 2) {
                continue;
            }

            /*
             * Short words need stronger confidence.
             *
             * Example:
             * "mir" -> "mirpur"
             * should NOT be accepted just because it has
             * some similarity.
             */
            if ($wordLength <= 4 && $percent < 90) {
                continue;
            }

            if ($wordLength <= 6 && $percent < 85) {
                continue;
            }

            if ($percent < $minPercent) {
                continue;
            }

            $candidates[] = [
                'item' => $item,
                'word' => $word,
                'percent' => $percent,
                'distance' => $distance,
                'word_index' => $wordIndex,
            ];
        }
    }

    if (empty($candidates)) {
        return null;
    }

    /*
     * Highest percentage first.
     * If percentage is equal, prefer fewer edits.
     * If still equal, prefer the earlier search word.
     */
    usort($candidates, function ($a, $b) {

        if ($a['percent'] != $b['percent']) {
            return $b['percent'] <=> $a['percent'];
        }

        if ($a['distance'] != $b['distance']) {
            return $a['distance'] <=> $b['distance'];
        }

        return $a['word_index'] <=> $b['word_index'];
    });

    $best = $candidates[0];
    $second = $candidates[1] ?? null;

    /*
     * If two candidates are almost equally good,
     * don't guess.
     */
    if ($second) {

        $margin = $best['percent'] - $second['percent'];

        if ($margin < $minMargin) {
            return null;
        }
    }

    return $best;
}
private function findBestWordMatchAtThreshold(array $words, $collection, $nameField, $minThreshold)
{
    foreach ($words as $word) {
        $bestItem = null;
        $bestPercent = 0;
 
        foreach ($collection as $item) {
            $percent = $this->calculateMatchPercentage($word, $item->{$nameField});
            if ($percent > $bestPercent) {
                $bestPercent = $percent;
                $bestItem = $item;
            }
        }
 
        if ($bestItem && $bestPercent >= $minThreshold) {
            return ['item' => $bestItem, 'word' => $word, 'percent' => $bestPercent];
        }
    }
    return null;
}
 
/**
 * Descend thresholds from $topThreshold to $bottomThreshold (inclusive),
 * returning the first match found at the highest threshold that
 * produces one at all.
 */
private function fuzzyMatchWithLadder(
    array $words,
    $collection,
    $nameField,
    $topThreshold = 99,
    $bottomThreshold = 80
) {
    return $this->findBestWordMatch(
        $words,
        $collection,
        $nameField,
        $bottomThreshold,
        5
    );
}
 
/**
 * Approximate phonetic Bangla -> English (Latin) transliteration.
 * Good enough for fuzzy-matching purposes, not meant to be a linguistically
 * perfect romanization. Handles:
 *  - independent vowels (অ, আ, ই, ...)
 *  - consonants + dependent vowel signs (matras)
 *  - hasant/virama (্) for consonant conjuncts, with a special case for
 *    the "ya-phala" (্য) which sounds like "y" rather than "j"
 *  - Bangla digits
 *  - chandrabindu (ঁ), anusvara (ং), visarga (ঃ)
 *
 * Non-Bangla characters (including plain English, spaces, punctuation,
 * digits already in Latin script) pass through completely unchanged.
 */
private function transliterateBanglaToEnglish($text)
{
    // Skip entirely if there's no Bangla in the string at all.
    if (!preg_match('/\p{Bengali}/u', $text)) {
        return $text;
    }
 
    $independentVowels = [
        'অ' => 'o', 'আ' => 'a', 'ই' => 'i', 'ঈ' => 'i', 'উ' => 'u',
        'ঊ' => 'u', 'ঋ' => 'ri', 'এ' => 'e', 'ঐ' => 'oi', 'ও' => 'o', 'ঔ' => 'ou',
    ];
 
    $consonants = [
        'ক' => 'k', 'খ' => 'kh', 'গ' => 'g', 'ঘ' => 'gh', 'ঙ' => 'ng',
        'চ' => 'ch', 'ছ' => 'chh', 'জ' => 'j', 'ঝ' => 'jh', 'ঞ' => 'n',
        'ট' => 't', 'ঠ' => 'th', 'ড' => 'd', 'ঢ' => 'dh', 'ণ' => 'n',
        'ত' => 't', 'থ' => 'th', 'দ' => 'd', 'ধ' => 'dh', 'ন' => 'n',
        'প' => 'p', 'ফ' => 'ph', 'ব' => 'b', 'ভ' => 'bh', 'ম' => 'm',
        'য' => 'j', 'র' => 'r', 'ল' => 'l', 'শ' => 'sh', 'ষ' => 'sh',
        'স' => 's', 'হ' => 'h', 'ড়' => 'r', 'ঢ়' => 'rh', 'য়' => 'y',
    ];
 
    $vowelSigns = [
        'া' => 'a', 'ি' => 'i', 'ী' => 'i', 'ু' => 'u', 'ূ' => 'u',
        'ৃ' => 'ri', 'ে' => 'e', 'ৈ' => 'oi', 'ো' => 'o', 'ৌ' => 'ou',
    ];
 
    $digits = [
        '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
        '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
    ];
 
    $chars = mb_str_split($text);
    $count = count($chars);
    $out = '';
 
    for ($i = 0; $i < $count; $i++) {
        $char = $chars[$i];
        $next = $chars[$i + 1] ?? null;
 
        if (isset($digits[$char])) {
            $out .= $digits[$char];
            continue;
        }
 
        if (isset($consonants[$char])) {
            $latin = $consonants[$char];
 
            // ya-phala: য directly after a hasant (্) sounds like "y",
            // e.g. শ্যামলী -> "shyamoli", not "shjamoli".
            if ($char === 'য' && $i > 0 && $chars[$i - 1] === '্') {
                $latin = 'y';
            }
 
            $out .= $latin;
 
            if ($next === '্') {
                // Hasant/virama — this consonant carries no vowel sound
                // and joins directly with the next consonant.
                $i++; // consume the hasant, add nothing for it
            } elseif ($next !== null && isset($vowelSigns[$next])) {
                $out .= $vowelSigns[$next];
                $i++; // consume the vowel sign
            } else {
                // No vowel sign, no hasant -> the consonant's inherent
                // vowel sound ("o") applies.
                $out .= 'o';
            }
            continue;
        }
 
        if (isset($independentVowels[$char])) {
            $out .= $independentVowels[$char];
            continue;
        }
 
        if ($char === 'ং') { $out .= 'ng'; continue; }
        if ($char === 'ঃ') { $out .= 'h'; continue; }
        if ($char === 'ৎ') { $out .= 't'; continue; }
        if ($char === 'ঁ') { continue; } // chandrabindu — nasalization, drop
 
        // Already-Latin characters, spaces, punctuation, etc.
        $out .= $char;
    }
 
    return $out;
}
}