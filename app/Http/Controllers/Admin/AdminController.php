<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\ContactRepository;

class AdminController extends Controller
{
    protected $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Show admin dashboard
     */
    public function index()
    {
        $totalContacts = \App\Models\Contact::notMerged()->count();
        $mergedContacts = \App\Models\Contact::merged()->count();
        $totalCustomFields = \App\Models\CustomField::count();
        
        return view('admin.dashboard', compact(
            'totalContacts',
            'mergedContacts',
            'totalCustomFields'
        ));
    }
}