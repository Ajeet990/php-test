<?php

namespace App\Repositories;

use App\Models\Contact;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use App\Models\ContactCustomFieldValue;
use App\Models\MergeHistory;
use Illuminate\Support\Facades\DB;

class MergeRepository
{
    /**
     * Merge two contacts
     */
    public function mergeContacts($masterContactId, $mergeContactId, $mergedBy = null)
    {
        return DB::transaction(function () use ($masterContactId, $mergeContactId, $mergedBy) {
            $masterContact = Contact::with(['emails', 'phones', 'customFieldValues'])->findOrFail($masterContactId);
            $mergeContact = Contact::with(['emails', 'phones', 'customFieldValues'])->findOrFail($mergeContactId);

            // Collect merge data for history
            $mergeData = [
                'merge_contact_data' => [
                    'name' => $mergeContact->name,
                    'email' => $mergeContact->email,
                    'phone' => $mergeContact->phone,
                    'gender' => $mergeContact->gender,
                ],
                'emails_merged' => [],
                'phones_merged' => [],
                'custom_fields_merged' => [],
            ];

            // Merge emails (add non-duplicate emails)
            $masterEmails = $masterContact->emails->pluck('email')->toArray();
            foreach ($mergeContact->emails as $email) {
                if (!in_array($email->email, $masterEmails)) {
                    ContactEmail::create([
                        'contact_id' => $masterContact->id,
                        'email' => $email->email,
                        'is_primary' => false,
                    ]);
                    $mergeData['emails_merged'][] = $email->email;
                }
            }

            // Merge phones (add non-duplicate phones)
            $masterPhones = $masterContact->phones->pluck('phone')->toArray();
            foreach ($mergeContact->phones as $phone) {
                if (!in_array($phone->phone, $masterPhones)) {
                    ContactPhone::create([
                        'contact_id' => $masterContact->id,
                        'phone' => $phone->phone,
                        'is_primary' => false,
                    ]);
                    $mergeData['phones_merged'][] = $phone->phone;
                }
            }

            // Merge custom fields
            $masterCustomFields = $masterContact->customFieldValues->pluck('custom_field_id')->toArray();
            foreach ($mergeContact->customFieldValues as $customFieldValue) {
                if (!in_array($customFieldValue->custom_field_id, $masterCustomFields)) {
                    // Master doesn't have this custom field, add it
                    ContactCustomFieldValue::create([
                        'contact_id' => $masterContact->id,
                        'custom_field_id' => $customFieldValue->custom_field_id,
                        'field_value' => $customFieldValue->field_value,
                    ]);
                    $mergeData['custom_fields_merged'][] = [
                        'field_id' => $customFieldValue->custom_field_id,
                        'field_name' => $customFieldValue->customField->field_label,
                        'value' => $customFieldValue->field_value,
                        'action' => 'added',
                    ];
                } else {
                    // Master already has this field, keep master's value (policy: master wins)
                    $mergeData['custom_fields_merged'][] = [
                        'field_id' => $customFieldValue->custom_field_id,
                        'field_name' => $customFieldValue->customField->field_label,
                        'master_value' => $masterContact->customFieldValues->where('custom_field_id', $customFieldValue->custom_field_id)->first()->field_value,
                        'merge_value' => $customFieldValue->field_value,
                        'action' => 'kept_master',
                    ];
                }
            }

            // Mark merge contact as merged
            $mergeContact->update([
                'is_merged' => true,
                'merged_into' => $masterContact->id,
            ]);

            // Create merge history record
            MergeHistory::create([
                'master_contact_id' => $masterContact->id,
                'merged_contact_id' => $mergeContact->id,
                'merge_data' => $mergeData,
                'merged_by' => $mergedBy,
            ]);

            return [
                'success' => true,
                'master_contact' => $masterContact->fresh()->load(['emails', 'phones', 'customFieldValues.customField']),
                'merge_data' => $mergeData,
            ];
        });
    }

    /**
     * Get merge history for a contact
     */
    public function getMergeHistory($contactId)
    {
        return MergeHistory::with(['masterContact', 'mergedContact'])
            ->where('master_contact_id', $contactId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}