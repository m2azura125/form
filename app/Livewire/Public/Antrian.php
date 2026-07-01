<?php

namespace App\Livewire\Public;

use App\Models\Submission;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class Antrian extends Component
{
    use WithPagination;

    private const SORTABLE = ['nama', 'tanggal_pengajuan', 'deadline'];

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $status = '';

    #[Url(as: 'tipe', history: true)]
    public string $tipe = '';

    #[Url(as: 'sort', history: true)]
    public string $sortField = 'tanggal_pengajuan';

    #[Url(as: 'dir', history: true)]
    public string $sortDirection = 'desc';

    #[Url(as: 'per_page', history: true)]
    public int $perPage = 15;

    public function updated(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'tipe']);
        $this->resetPage();
    }

    protected function filteredQuery(): Builder
    {
        $query = Submission::query();

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('nama', 'like', "%{$this->search}%")
                    ->orWhere('judul_alat', 'like', "%{$this->search}%");
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->tipe !== '') {
            $query->where('tipe', $this->tipe);
        }

        $sortField = in_array($this->sortField, self::SORTABLE, true) ? $this->sortField : 'tanggal_pengajuan';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortField, $sortDirection);
    }

    public function render()
    {
        return view('livewire.public.antrian', [
            'submissions' => $this->filteredQuery()->paginate($this->perPage),
        ]);
    }
}
