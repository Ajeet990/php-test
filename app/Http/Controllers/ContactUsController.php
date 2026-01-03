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
            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'gender' => 'required|in:male,female,other',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'additional_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $validated = $validator->validate();
            // dd($request->all());

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
            // dd($data);

            // Create contact
            $contact = $this->contactService->createContact($data);

            return response()->json([
                'success' => true,
                'message' => 'Contact form submitted successfully! Thank you for reaching out.',
                'data' => $contact,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting the form. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}