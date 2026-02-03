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
            ->first(['id', 'name', 'phone_number_1', 'phone_number_2', 'full_address', 'delivery_area', 'created_at']);
        
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