<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Enan\PathaoCourier\Facades\PathaoCourier;
use Enan\PathaoCourier\Requests\PathaoUserSuccessRateRequest;
use Illuminate\Http\Request;

class PathaoController extends Controller
{
    /**
     * Display the Pathao management page
     */
    public function index()
    {
        return view('pathao.index');
    }

    /**
     * Get all cities
     */
    public function getCities()
    {
        try {
            $cities = PathaoCourier::GET_CITIES();
            return response()->json([
                'success' => true,
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
    public function getZones($cityId)
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
    public function getAreas($zoneId)
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
}