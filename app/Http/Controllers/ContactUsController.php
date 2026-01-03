<?php

namespace App\Http\Controllers;

use App\Services\ContactService;
use App\Services\CustomFieldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    protected $contactService;
    protected $customFieldService;

    public function __construct(
        ContactService $contactService,
        CustomFieldService $customFieldService
    ) {
        $this->contactService = $contactService;
        $this->customFieldService = $customFieldService;
    }

    /**
     * Show contact us form
     */
    public function index()
    {
        $customFields = $this->customFieldService->getActiveCustomFields();
        return view('contact-us', compact('customFields'));
    }

    /**
     * Store contact form submission
     */
    public function store(Request $request)
    {
        try {
            // Validation rules with unique constraints
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:contacts,email',
                'phone' => 'required|string|max:20|unique:contacts,phone',
                'gender' => 'required|in:male,female,other',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'additional_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
            ];

            // Custom error messages
            $messages = [
                'email.unique' => 'This email address is already registered. Please use a different email.',
                'phone.unique' => 'This phone number is already registered. Please use a different phone number.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'phone.required' => 'Phone number is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Prepare data
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
            ];

            // Handle file uploads
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image');
            }

            if ($request->hasFile('additional_file')) {
                $data['additional_file'] = $request->file('additional_file');
            }

            // Handle custom fields
            $customFieldsData = [];
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'custom_field_') === 0) {
                    $fieldId = str_replace('custom_field_', '', $key);
                    $customFieldsData[$fieldId] = $value;
                }
            }
            $data['custom_fields'] = $customFieldsData;

            // Create contact
            $contact = $this->contactService->createContact($data);

            return response()->json([
                'success' => true,
                'message' => 'Contact form submitted successfully! Thank you for reaching out.',
                'data' => $contact,
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database unique constraint errors
            if ($e->getCode() == 23000) {
                $message = 'A contact with this email or phone already exists.';
                
                if (str_contains($e->getMessage(), 'email')) {
                    $message = 'This email address is already registered.';
                } elseif (str_contains($e->getMessage(), 'phone')) {
                    $message = 'This phone number is already registered.';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [
                        'database' => [$message]
                    ],
                ], 422);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Database error',
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting the form. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }
}