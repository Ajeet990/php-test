<?php

namespace App\Services;

use App\Repositories\MergeRepository;
use App\Repositories\ContactRepository;

class MergeService
{
    protected $mergeRepository;
    protected $contactRepository;

    public function __construct(
        MergeRepository $mergeRepository,
        ContactRepository $contactRepository
    ) {
        $this->mergeRepository = $mergeRepository;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Merge two contacts
     */
    public function mergeContacts($masterContactId, $mergeContactId, $mergedBy = null)
    {
        // Validate both contacts exist and are not merged
        if (!$this->contactRepository->exists($masterContactId)) {
            throw new \Exception('Master contact not found or already merged');
        }

        if (!$this->contactRepository->exists($mergeContactId)) {
            throw new \Exception('Contact to merge not found or already merged');
        }

        if ($masterContactId == $mergeContactId) {
            throw new \Exception('Cannot merge a contact with itself');
        }

        return $this->mergeRepository->mergeContacts($masterContactId, $mergeContactId, $mergedBy);
    }

    /**
     * Get merge preview data
     */
    public function getMergePreview($contactId1, $contactId2)
    {
        $contact1 = $this->contactRepository->findForMerge($contactId1);
        $contact2 = $this->contactRepository->findForMerge($contactId2);

        return [
            'contact1' => $this->formatContactForPreview($contact1),
            'contact2' => $this->formatContactForPreview($contact2),
        ];
    }

    /**
     * Format contact data for preview
     */
    protected function formatContactForPreview($contact)
    {
        return [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'gender' => $contact->gender,
            'profile_image' => $contact->profile_image,
            'emails' => $contact->emails->pluck('email')->toArray(),
            'phones' => $contact->phones->pluck('phone')->toArray(),
            'custom_fields' => $contact->customFieldValues->map(function ($cfv) {
                return [
                    'field_id' => $cfv->custom_field_id,
                    'field_label' => $cfv->customField->field_label,
                    'field_type' => $cfv->customField->field_type,
                    'value' => $cfv->field_value,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get merge history for a contact
     */
    public function getMergeHistory($contactId)
    {
        return $this->mergeRepository->getMergeHistory($contactId);
    }
}