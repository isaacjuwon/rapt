<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class Verify extends Component
{
    use WithFileUploads;

    #[Validate('required|in:bvn,nin')]
    public string $document_type = '';

    #[Validate('required|file|mimes:pdf,jpg,png|max:2048')]
    public $document_file;

    public function submit()
    {
        $this->validate();

        // Store the uploaded file
        $path = $this->document_file->store('verification_documents', 'public');

        // For now, we'll just store the path and type.
        // In a real application, you would save this to the user's record
        // or a separate verification request table, and then an admin
        // would review it.
        // For demonstration, let's just redirect to dashboard.
        session()->flash('message', 'Document submitted successfully for verification!');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.verify');
    }
}
