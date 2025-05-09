<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Domain;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\UserActionNotification;

class DomainTemplate extends Component
{
    use WithFileUploads;

    public $domainId;
    public $tingkat;
    public $uploadedFiles = [];
    public $updatedFiles = [];
    public $selectedCategory;
    public $criteria;
    public $isAdmin;
    public $reason = [];
    public $domainReasons = [];
    public $hasil;
    public $modalType; // To track whether the modal is for domain or file
    public $modalId;   // To track the specific domain or file being updated
    public $tingkat_tpb = [];

    public $indicators = []; // To store all indicators for the current aspek
    public $currentIndicator = null; // To store the currently selected indicator
    public $currentIndicatorIndex = 0; // To track the index of the selected indicator


    public $selectedFileId = null; // ID of the currently selected file
    public $selectedFile = null; // The selected file object
    public $selectedFileReason = null; // The reason for the selected file

    public $selectedContext = 'pembinaan'; // Default context


    // Update selected file when dropdown changes
    public function updatedSelectedFileId($fileId)
    {
        $this->selectedFile = File::find($fileId);

        if ($this->selectedFile) {
            $this->selectedFileReason = $this->selectedFile->reasons ?? '';
        } else {
            $this->selectedFileReason = '';
        }
    }



    // Save reason and status for the selected file
    public function saveFileReasonAndStatusInline($status)
    {
        if (!$this->selectedFile) {
            session()->flash('message', 'No file selected!');
            return;
        }
    
        if (trim($this->selectedFileReason) === '') {
            session()->flash('message', 'Reason cannot be empty!');
            return;
        }
    
        // Update the reason and status for the selected file
        $this->selectedFile->reasons = $this->selectedFileReason;
        $this->selectedFile->hasil = $status;
        $this->selectedFile->save();
    
        // Display a success message
        session()->flash('message', $status ? 'File approved successfully!' : 'File disapproved successfully!');
    
        // Reset to file selection view
        $this->selectedFileId = null;
        $this->selectedFile = null;
        $this->selectedFileReason = '';
    }
    





    public function saveDomainReasonAndStatus($domainId, $reason, $status)
    {
        $domain = Domain::find($domainId);

        if (!$domain) {
            session()->flash('message', 'Domain not found!');
            return;
        }

        $domain->reasons = $reason;
        $domain->disetujui = $status;
        $domain->save();

        // Reset modal properties
        $this->modalType = null;
        $this->modalId = null;

        session()->flash('message', $status ? 'Domain approved successfully!' : 'Domain disapproved successfully!');
    }



    public function saveFileReasonAndStatus($fileId, $status)
    {
        // Find the file by ID
        $file = File::find($fileId);

        if (!$file) {
            session()->flash('message', 'File not found!');
            return;
        }

        // Check if a reason is provided
        if (empty($this->selectedFileReason)) {
            session()->flash('message', 'Reason cannot be empty!');
            return;
        }

        // Update the file's reason and status
        $file->reasons = $this->selectedFileReason;
        $file->hasil = $status;
        $file->save();

        // Reset the reason field
        $this->selectedFileReason = null;

        // Notify the user of the successful action
        session()->flash('message', $status ? 'File approved successfully!' : 'File disapproved successfully!');
    }






    public function mount($selectedCategory)
    {
        $this->selectedCategory = $selectedCategory;
    
        // Fetch all domains in the selected category and load the user relationship
        $this->criteria = Domain::where('aspek', $this->selectedCategory)
            ->with('user') // Load the user relationship
            ->get();
    
        // Initialize domainId and tingkat only if there are domains
        if ($this->criteria->isNotEmpty()) {
            $this->domainId = $this->criteria->first()->id; // Initialize with the first domain
            $this->tingkat = $this->criteria->first()->tingkat; // Initialize tingkat
        }
    
        // Fetch all indicators for the selected aspek and explicitly convert to array
        $this->indicators = $this->criteria->map(function ($domain) {
            return $domain->toArray();
        })->toArray();
    
        // Restore the current indicator from the session or set the first indicator as default
        $savedIndex = session()->get('currentIndicatorIndex', 0); // Default to 0 if not set
        if (isset($this->indicators[$savedIndex])) {
            $this->currentIndicatorIndex = $savedIndex;
            $this->currentIndicator = $this->indicators[$savedIndex];
        } else {
            $this->currentIndicatorIndex = 0;
            $this->currentIndicator = $this->indicators[0] ?? null;
        }
    
        // Initialize file-related properties
        $this->selectedFileId = null;
        $this->selectedFile = null;
        $this->selectedFileReason = '';
    
        // Check if the logged-in user is an admin
        $this->isAdmin = auth()->user()->admin ?? false;
    }
    

    public function selectIndicator($index)
    {
        if (isset($this->indicators[$index])) {
            $this->currentIndicatorIndex = $index;

            // Fetch the latest data for the selected indicator
            $this->currentIndicator = Domain::find($this->indicators[$index]['id'])->toArray();

            // Update the local state for the current indicator
            $this->indicators[$index] = $this->currentIndicator;

            // Store the selected index in the session for persistence
            session()->put('currentIndicatorIndex', $index);

            // Reset file-related properties when the indicator changes
            $this->selectedFileId = null;
            $this->selectedFile = null;
            $this->selectedFileReason = '';
        }
    }


    public function saveReasonAndStatus($indicatorId, $status)
    {
        $indicator = Domain::with('user')->find($indicatorId);
    
        if (!$indicator) {
            session()->flash('message', 'Indicator not found!');
            return;
        }
    
        $indicator->reasons = $this->currentIndicator['reasons'];
        $indicator->disetujui = $status;
        $indicator->user_id = auth()->id();
        $indicator->save();
    
        // Fetch updated data
        $this->currentIndicator = Domain::with('user')->find($indicatorId)->toArray();
    
        session()->flash('message', 'Indicator updated successfully!');
    }
    
    







    // Save "tingkat" independently
    public function saveTingkat($level)
    {
        // Ensure a current indicator is selected
        if (!$this->currentIndicator || !isset($this->currentIndicator['id'])) {
            session()->flash('message', 'No indicator selected!');
            return;
        }

        // Fetch the domain by the current indicator's ID
        $domain = Domain::find($this->currentIndicator['id']);

        if (!$domain) {
            session()->flash('message', 'Domain not found!');
            return;
        }

        // Update the tingkat for the current indicator
        $domain->tingkat = $level;
        $domain->save();

        // Update the tingkat in the currentIndicator for UI purposes
        $this->currentIndicator['tingkat'] = $level;

        session()->flash('message', 'Tingkat updated successfully!');
    }



    public function updateTingkatTpb($domainId, $selectedTingkat)
    {
        // Find the domain by ID
        $domain = Domain::find($domainId);
    
        if (!$domain) {
            session()->flash('message', 'Domain not found!');
            return;
        }
    
        // Update the tingkat_tpb value and the user who performed the update
        $domain->tingkat_tpb = $selectedTingkat;
        $domain->user_id = auth()->id(); // Track the user
        $domain->save();
    
        // If the updated domain is the current indicator, update the local state
        if ($this->currentIndicator['id'] === $domainId) {
            $this->currentIndicator['tingkat_tpb'] = $selectedTingkat;
            $this->currentIndicator['user'] = $domain->user; // Update user information
        }
    
        // Provide feedback to the admin
        session()->flash('message', 'Tingkat TPB updated to ' . $selectedTingkat . ' successfully!');
    }
    





    // Save uploaded files
    public function saveFiles()
    {
        // Ensure a current indicator is selected
        if (!$this->currentIndicator) {
            session()->flash('message', 'No indikator selected!');
            return;
        }
    
        // Fetch the specific domain for the selected indikator
        $domain = Domain::find($this->currentIndicator['id']);
    
        if (!$domain) {
            session()->flash('message', 'Domain not found for the selected indikator!');
            return;
        }
    
        // Get context from the dropdown selection
        $context = $this->selectedContext ?? 'pembinaan';
    
        // Sanitize folder path by replacing spaces with underscores
        $folderPath = "uploads/" .
            $context . "/" . // Add context dynamically
            preg_replace('/\s+/', '_', $domain->domain) . "/" .
            preg_replace('/\s+/', '_', $domain->aspek) . "/" .
            preg_replace('/\s+/', '_', $domain->indikator);
    
        // Loop through the uploaded files
        foreach ($this->uploadedFiles as $file) {
            $originalName = $file->getClientOriginalName();
            $randomName = uniqid() . '.' . $file->getClientOriginalExtension(); // Generate a random filename
    
            // Store the file with a random name
            $filePath = $file->storeAs($folderPath, $randomName, 'public');
    
            // Save file information in the database
            File::create([
                'domain_id' => $domain->id, // Associate with the specific indikator
                'file_path' => $filePath,
                'original_name' => $originalName,
                'hasil' => null, // Default approval status
                'reasons' => null, // No reasons initially
                'context' => $context, // Add context
            ]);
        }
    
        // Clear the uploaded files and reset the view
        $this->uploadedFiles = [];
        session()->flash('message', 'Files uploaded successfully for the selected indikator!');
    }
    




    // Update file details
    // Automatically trigger file update when a new file is selected
    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'updatedFiles.')) {
            $fileId = explode('.', $propertyName)[1];
            $this->updateFile($fileId);
        }
    }

    public function updateFile($fileId)
    {
        $file = File::find($fileId);

        if (!$file) {
            session()->flash('message', 'File not found!');
            return;
        }

        if (!isset($this->updatedFiles[$fileId])) {
            session()->flash('message', 'No file selected for update!');
            return;
        }

        // Fetch the associated domain for folder structure
        $domain = Domain::find($file->domain_id);

        if (!$domain) {
            session()->flash('message', 'Domain not found!');
            return;
        }

        // Use the context from the file's existing record
        $context = $file->context ?? 'pembinaan';

        // Delete the old file from storage
        Storage::disk('public')->delete($file->file_path);

        // Sanitize folder path
        $folderPath = "uploads/" .
            $context . "/" . // Add context to the path
            preg_replace('/\s+/', '_', $domain->domain) . "/" .
            preg_replace('/\s+/', '_', $domain->aspek) . "/" .
            preg_replace('/\s+/', '_', $domain->indikator);

        // Generate a new random file name for storage
        $randomName = uniqid() . '.' . $this->updatedFiles[$fileId]->getClientOriginalExtension();

        // Store the new file in the correct folder
        $newFilePath = $this->updatedFiles[$fileId]->storeAs($folderPath, $randomName, 'public');

        // Update the file path, original name, and keep the existing context in the database
        $file->file_path = $newFilePath;
        $file->original_name = $this->updatedFiles[$fileId]->getClientOriginalName(); // Store the original name
        $file->context = $context; // Preserve the context
        $file->save();

        // Clear the temporary file input
        unset($this->updatedFiles[$fileId]);

        session()->flash('message', 'File updated successfully!');
    }



    // Approve or disapprove a file
    public function updateHasil($fileId, $status)
    {
        $file = File::find($fileId);

        if (!$file) {
            session()->flash('message', 'File not found!');
            return;
        }

        $file->hasil = $status;
        $file->save();

        session()->flash('message', $status ? 'File approved successfully!' : 'File disapproved successfully!');
    }

    // Save reasons for files
    public function saveReason($fileId, $reason)
    {
        $file = File::find($fileId);

        if (!$file) {
            session()->flash('message', 'File not found!');
            return;
        }

        if (trim($reason) === '') {
            session()->flash('message', 'Reason cannot be empty!');
            return;
        }

        $file->reasons = $reason;
        $file->save();

        session()->flash('message', 'Reason updated successfully for the file!');
    }




    public $confirmingDelete = null; // Track the ID of the file being deleted

    // Delete a file
    public function deleteFile($fileId)
    {
        $file = File::find($fileId);

        if (!$file) {
            session()->flash('message', 'File not found!');
            return;
        }

        // Delete the file from storage
        Storage::disk('public')->delete($file->file_path);

        // Delete the file record from the database
        $file->delete();

        // Reset the confirmation state
        $this->confirmingDelete = null;

        // Notify the user
        session()->flash('message', 'File deleted successfully!');
    }



    // Approve or disapprove a domain
    public function updateDomainApproval($domainId, $status)
    {
        $domain = Domain::find($domainId);

        if (!$domain) {
            session()->flash('message', 'Domain not found!');
            return;
        }

        $domain->disetujui = $status;
        $domain->save();

        session()->flash('message', $status ? 'Domain approved successfully!' : 'Domain disapproved successfully!');
    }

    // Save reasons for domains
    public function saveDomainReason($domainId, $reason)
    {
        $domain = Domain::find($domainId);

        if (!$domain) {
            session()->flash('message', 'Domain not found!');
            return;
        }

        $domain->reasons = $reason;
        $domain->save();

        session()->flash('message', 'Reason updated successfully!');
    }

    // Delete a domain
    public function deleteDomain($domainId)
    {
        $domain = Domain::find($domainId);

        if (!$domain) {
            session()->flash('message', 'Domain not found!');
            return;
        }

        $domain->delete();
        session()->flash('message', 'Domain deleted successfully!');
    }

    public function render()
    {
        return view('livewire.monalisa.domain-template', ['criteria' => $this->criteria]);
    }
}
