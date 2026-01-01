<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContactService;
use App\Services\CustomFieldService;
use App\Services\MergeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    protected $contactService;
    protected $customFieldService;
    protected $mergeService;

    public function __construct(
        ContactService $contactService,
        CustomFieldService $customFieldService,
        MergeService $mergeService
    ) {
        $this->contactService = $contactService;
        $this->customFieldService = $customFieldService;
        $this->mergeService = $mergeService;
    }

    /**
     * Display contacts list page
     */
    public function index()
    {
        $customFields = $this->customFieldService->getActiveCustomFields();
        return view('admin.contacts.index', compact('customFields'));
    }

    /**
     * Get contacts list via AJAX
     */
    public function list(Request $request)
    {
        try {
            // dd("hhhh");
            $filters = [
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
            ];
            

            $perPage = $request->per_page ?? 15;
            $contacts = $this->contactService->getAllContacts($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $contacts,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching contacts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single contact
     */
    public function show($id)
    {
        try {
            $contact = $this->contactService->getContactById($id);
            
            return response()->json([
                'success' => true,
                'data' => $contact,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store new contact via AJAX
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

            // Prepare data
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
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
                'message' => 'Contact created successfully!',
                'data' => $contact,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating contact',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update contact via AJAX
     */
    public function update(Request $request, $id)
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

            // Prepare data
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
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

            // Update contact
            $contact = $this->contactService->updateContact($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully!',
                'data' => $contact,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating contact',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete contact via AJAX
     */
    public function destroy($id)
    {
        try {
            $this->contactService->deleteContact($id);

            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting contact',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initiate merge - get preview data
     */
    public function initiateMerge(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'contact_id_1' => 'required|exists:contacts,id',
                'contact_id_2' => 'required|exists:contacts,id|different:contact_id_1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validate contacts can be merged
            $validation = $this->contactService->validateMerge(
                $request->contact_id_1,
                $request->contact_id_2
            );

            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                ], 400);
            }

            // Get merge preview
            $previewData = $this->mergeService->getMergePreview(
                $request->contact_id_1,
                $request->contact_id_2
            );

            return response()->json([
                'success' => true,
                'message' => 'Select master contact to continue',
                'data' => $previewData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error initiating merge',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm and execute merge
     */
    public function confirmMerge(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'master_contact_id' => 'required|exists:contacts,id',
                'merge_contact_id' => 'required|exists:contacts,id|different:master_contact_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Execute merge
            $result = $this->mergeService->mergeContacts(
                $request->master_contact_id,
                $request->merge_contact_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Contacts merged successfully!',
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error merging contacts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}