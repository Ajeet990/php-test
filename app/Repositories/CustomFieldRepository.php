<?php

namespace App\Repositories;

use App\Models\CustomField;

class CustomFieldRepository
{
    /**
     * Get all custom fields
     */
    public function getAll()
    {
        return CustomField::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all active custom fields
     */
    public function getActive()
    {
        return CustomField::active()->orderBy('field_label', 'asc')->get();
    }

    /**
     * Find custom field by ID
     */
    public function findById($id)
    {
        return CustomField::findOrFail($id);
    }

    /**
     * Create new custom field
     */
    public function create(array $data)
    {
        return CustomField::create([
            'field_name' => $data['field_name'],
            'field_label' => $data['field_label'],
            'field_type' => $data['field_type'],
            'field_options' => $data['field_options'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update custom field
     */
    public function update($id, array $data)
    {
        $customField = CustomField::findOrFail($id);
        
        $customField->update([
            'field_name' => $data['field_name'],
            'field_label' => $data['field_label'],
            'field_type' => $data['field_type'],
            'field_options' => $data['field_options'] ?? null,
            'is_active' => $data['is_active'] ?? $customField->is_active,
        ]);

        return $customField;
    }

    /**
     * Delete custom field
     */
    public function delete($id)
    {
        $customField = CustomField::findOrFail($id);
        return $customField->delete();
    }

    /**
     * Check if field name already exists
     */
    public function fieldNameExists($fieldName, $excludeId = null)
    {
        $query = CustomField::where('field_name', $fieldName);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}