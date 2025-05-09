<?php

namespace App\Livewire\Monalisa;

use App\Models\Monalisa\Domain;
use Livewire\Component;
use Livewire\WithPagination;

class DomainList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $domains = Domain::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.monalisa.domain-list', [
            'domains' => $domains,
        ]);
    }
}
