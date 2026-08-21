<?php

namespace App\Livewire\Admin;

use App\Models\SubmissionHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class HistoryIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'aksi', history: true)]
    public string $aksi = '';

    #[Url(as: 'user', history: true)]
    public string $userId = '';

    #[Url(as: 'tgl_dari', history: true)]
    public string $tanggalDari = '';

    #[Url(as: 'tgl_sampai', history: true)]
    public string $tanggalSampai = '';

    #[Url(as: 'per_page', history: true)]
    public int $perPage = 15;

    public function updated(string $name): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'aksi', 'userId', 'tanggalDari', 'tanggalSampai']);
        $this->resetPage();
    }

    public function render()
    {
        $query = SubmissionHistory::query()
            ->with(['submission', 'user'])
            ->latest();

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('keterangan', 'like', '%'.$this->search.'%')
                    ->orWhere('user_name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('submission', function (Builder $sq) {
                        $sq->where('nama', 'like', '%'.$this->search.'%')
                            ->orWhere('judul_alat', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->aksi !== '') {
            $query->where('aksi', $this->aksi);
        }

        if ($this->userId !== '') {
            $query->where('user_id', $this->userId);
        }

        if ($this->tanggalDari !== '') {
            $query->whereDate('created_at', '>=', $this->tanggalDari);
        }

        if ($this->tanggalSampai !== '') {
            $query->whereDate('created_at', '<=', $this->tanggalSampai);
        }

        $histories = $query->paginate($this->perPage);
        $users = User::query()->orderBy('name')->get();
        $aksiOptions = SubmissionHistory::AKSI_LABELS;

        return view('livewire.admin.history-index', [
            'histories' => $histories,
            'users' => $users,
            'aksiOptions' => $aksiOptions,
        ]);
    }
}
