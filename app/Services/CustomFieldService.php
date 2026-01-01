<?php

namespace App\Services;

use App\Repositories\CustomFieldRepository;
use Illuminate\Support\Str;

class CustomFieldService
{
    protected $customFieldRepository;

    public function __construct(CustomFieldRepository $customFieldRepository)
    {
        $this->customFieldRepository = $customFieldRepository;
    }

    /**
     * Get all custom fields
     */
    public function getAllCustomFields()
    {
        return $this->customFieldRepository->getAll();
    }

    /**
     * Get all active custom fields
     */
    public function getActiveCustomFields()
    {
        return $this->customFieldRepository->getActive();
    }

    /**
     * Get custom field by ID
     */
    public function getCustomFieldById($id)
    {
        return $this->customFieldRepository->findById($id);
    }

    /**
     * Create new custom field
     */
    public function createCustomField($data)
    {
        // Generate field_name from field_label if not provided
        if (empty($data['field_name'])) {
            $data['field_name'] = Str::slug($data['field_label'], '_');
        }

        // Check if field name already exists
        if ($this->customFieldRepository->fieldNameExists($data['field_name'])) {
            throw new \Exception('Field name already exists. Please use a different name.');
        }

        // Parse field_options if it's a JSON string
        if (isset($data['field_options']) && is_string($data['field_options'])) {
            $data['field_options'] = json_decode($data['field_options'], true);
        }

        return $this->customFieldRepository->create($data);
    }

    /**
     * Update custom field
     */
    public function updateCustomField($id, $data)
    {
        // Generate field_name from field_label if not provided
        if (empty($data['field_name'])) {
            $data['field_name'] = Str::slug($data['field_label'], '_');
        }

        // Check if field name already exists (excluding current field)
        if ($this->customFieldRepository->fieldNameExists($data['field_name'], $id)) {
            throw new \Exception('Field name already exists. Please use a different name.');
        }

        // Parse field_options if it's a JSON string
        if (isset($data['field_options']) && is_string($data['field_options'])) {
            $data['field_options'] = json_decode($data['field_options'], true);
        }

        return $this->customFieldRepository->update($id, $data);
    }

    /**
     * Delete custom field
     */
    public function deleteCustomField($id)
    {
        return $this->customFieldRepository->delete($id);
    }

    /**
     * Validate field data
     */
    public function validateFieldData($data)
    {
        $errors = [];

        if (empty($data['field_label'])) {
            $errors[] = 'Field label is required';
        }

        if (empty($data['field_type'])) {
            $errors[] = 'Field type is required';
        }

        $validTypes = ['text', 'textarea', 'date', 'number', 'select'];
        if (!empty($data['field_type']) && !in_array($data['field_type'], $validTypes)) {
            $errors[] = 'Invalid field type';
        }

        if ($data['field_type'] === 'select' && empty($data['field_options'])) {
            $errors[] = 'Field options are required for select type';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}