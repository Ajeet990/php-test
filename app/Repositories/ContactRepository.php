<?php
namespace App\Repositories;

use App\Models\Contact;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use App\Models\ContactCustomFieldValue;
use Illuminate\Support\Facades\DB;

class ContactRepository
{
    public function getAll($filters = [], $perPage = 15)
    {
        $query = Contact::with(['emails', 'phones', 'customFieldValues.customField', 'mergedContacts'])
            ->notMerged();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function findById($id)
    {
        return Contact::with([
            'emails',
            'phones',
            'customFieldValues.customField',
            'mergedContacts'
        ])->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $contact = Contact::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'profile_image' => $data['profile_image'] ?? null,
                'additional_file' => $data['additional_file'] ?? null,
            ]);

            ContactEmail::create([
                'contact_id' => $contact->id,
                'email' => $data['email'],
                'is_primary' => true,
            ]);

            ContactPhone::create([
                'contact_id' => $contact->id,
                'phone' => $data['phone'],
                'is_primary' => true,
            ]);

            if (!empty($data['custom_fields'])) {
                foreach ($data['custom_fields'] as $fieldId => $value) {
                    if (!empty($value)) {
                        ContactCustomFieldValue::create([
                            'contact_id' => $contact->id,
                            'custom_field_id' => $fieldId,
                            'field_value' => $value,
                        ]);
                    }
                }
            }

            return $contact->load(['emails', 'phones', 'customFieldValues.customField']);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $contact = Contact::findOrFail($id);

            $contact->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'profile_image' => $data['profile_image'] ?? $contact->profile_image,
                'additional_file' => $data['additional_file'] ?? $contact->additional_file,
            ]);

            $primaryEmail = $contact->emails()->where('is_primary', true)->first();
            if ($primaryEmail) {
                $primaryEmail->update(['email' => $data['email']]);
            }

            $primaryPhone = $contact->phones()->where('is_primary', true)->first();
            if ($primaryPhone) {
                $primaryPhone->update(['phone' => $data['phone']]);
            }

            if (isset($data['custom_fields'])) {
                foreach ($data['custom_fields'] as $fieldId => $value) {
                    ContactCustomFieldValue::updateOrCreate(
                        [
                            'contact_id' => $contact->id,
                            'custom_field_id' => $fieldId,
                        ],
                        [
                            'field_value' => $value,
                        ]
                    );
                }
            }

            return $contact->load(['emails', 'phones', 'customFieldValues.customField']);
        });
    }

    public function delete($id)
    {
        $contact = Contact::findOrFail($id);
        return $contact->delete();
    }

    public function findForMerge($id)
    {
        return Contact::with([
            'emails',
            'phones',
            'customFieldValues.customField'
        ])->notMerged()->findOrFail($id);
    }

    public function getTwoForMerge($contactId1, $contactId2)
    {
        $contact1 = $this->findForMerge($contactId1);
        $contact2 = $this->findForMerge($contactId2);

        return [
            'contact1' => $contact1,
            'contact2' => $contact2,
        ];
    }

    public function exists($id)
    {
        return Contact::notMerged()->where('id', $id)->exists();
    }
}