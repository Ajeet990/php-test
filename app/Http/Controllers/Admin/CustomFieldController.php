<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomFieldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomFieldController extends Controller
{
    protected $customFieldService;

    public function __construct(CustomFieldService $customFieldService)
    {
        $this->customFieldService = $customFieldService;
    }

    /**
     * Display custom fields page
     */
    public function index()
    {
        return view('admin.custom-fields.index');
    }

    /**
     * Get custom fields list via AJAX
     */
    public function list()
    {
        try {
            $customFields = $this->customFieldService->getAllCustomFields();

            return response()->json([
                'success' => true,
                'data' => $customFields,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching custom fields',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active custom fields
     */
    public function getActiveFields()
    {
        try {
            $customFields = $this->customFieldService->getActiveCustomFields();

            return response()->json([
                'success' => true,
                'data' => $customFields,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching active custom fields',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store new custom field via AJAX
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'field_label' => 'required|string|max:100',
                'field_type' => 'required|in:text,textarea,date,number,select',
                'field_options' => 'required_if:field_type,select',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = [
                'field_label' => $request->field_label,
                'field_type' => $request->field_type,
                'is_active' => $request->is_active ?? true,
            ];

            // Handle field_options for select type
            if ($request->field_type === 'select' && $request->field_options) {
                if (is_string($request->field_options)) {
                    // Parse comma-separated string to array
                    $options = array_map('trim', explode(',', $request->field_options));
                    $data['field_options'] = $options;
                } else {
                    $data['field_options'] = $request->field_options;
                }
            }

            $customField = $this->customFieldService->createCustomField($data);

            return response()->json([
                'success' => true,
                'message' => 'Custom field created successfully!',
                'data' => $customField,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update custom field via AJAX
     */
    public function update(Request $request, $id)
    {
        try {
            $rules = [
                'field_label' => 'required|string|max:100',
                'field_type' => 'required|in:text,textarea,date,number,select',
                'field_options' => 'required_if:field_type,select',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = [
                'field_label' => $request->field_label,
                'field_type' => $request->field_type,
                'is_active' => $request->is_active ?? true,
            ];

            // Handle field_options for select type
            if ($request->field_type === 'select' && $request->field_options) {
                if (is_string($request->field_options)) {
                    // Parse comma-separated string to array
                    $options = array_map('trim', explode(',', $request->field_options));
                    $data['field_options'] = $options;
                } else {
                    $data['field_options'] = $request->field_options;
                }
            }

            $customField = $this->customFieldService->updateCustomField($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Custom field updated successfully!',
                'data' => $customField,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete custom field via AJAX
     */
    public function destroy($id)
    {
        try {
            $this->customFieldService->deleteCustomField($id);

            return response()->json([
                'success' => true,
                'message' => 'Custom field deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting custom field',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}