<?php
namespace App\Services;

use App\Repositories\ContactRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactService
{
    protected $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function getAllContacts($filters = [], $perPage = 15)
    {
        return $this->contactRepository->getAll($filters, $perPage);
    }

    public function getContactById($id)
    {
        return $this->contactRepository->findById($id);
    }

    public function createContact($data)
    {
        if (isset($data['profile_image'])) {
            $data['profile_image'] = $this->uploadFile($data['profile_image'], 'profile_images');
        }

        if (isset($data['additional_file'])) {
            $data['additional_file'] = $this->uploadFile($data['additional_file'], 'additional_files');
        }

        return $this->contactRepository->create($data);
    }

    public function updateContact($id, $data)
    {
        $contact = $this->contactRepository->findById($id);

        if (isset($data['profile_image']) && $data['profile_image']) {
            if ($contact->profile_image) {
                $this->deleteFile($contact->profile_image);
            }
            $data['profile_image'] = $this->uploadFile($data['profile_image'], 'profile_images');
        }

        if (isset($data['additional_file']) && $data['additional_file']) {
            if ($contact->additional_file) {
                $this->deleteFile($contact->additional_file);
            }
            $data['additional_file'] = $this->uploadFile($data['additional_file'], 'additional_files');
        }

        return $this->contactRepository->update($id, $data);
    }

    public function deleteContact($id)
    {
        $contact = $this->contactRepository->findById($id);

        if ($contact->profile_image) {
            $this->deleteFile($contact->profile_image);
        }

        if ($contact->additional_file) {
            $this->deleteFile($contact->additional_file);
        }

        return $this->contactRepository->delete($id);
    }

    protected function uploadFile($file, $folder)
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');
        return $path;
    }

    protected function deleteFile($path)
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function getContactsForMerge($contactId1, $contactId2)
    {
        return $this->contactRepository->getTwoForMerge($contactId1, $contactId2);
    }

    public function validateMerge($contactId1, $contactId2)
    {
        if ($contactId1 == $contactId2) {
            return ['valid' => false, 'message' => 'Cannot merge a contact with itself'];
        }

        if (!$this->contactRepository->exists($contactId1)) {
            return ['valid' => false, 'message' => 'First contact not found or already merged'];
        }

        if (!$this->contactRepository->exists($contactId2)) {
            return ['valid' => false, 'message' => 'Second contact not found or already merged'];
        }

        return ['valid' => true];
    }
}
