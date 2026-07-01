<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\SubmissionForm;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    use WithPagination;

    private const SORTABLE = ['nama', 'tanggal_pengajuan', 'deadline'];

    private const TRANSIENT_PROPERTIES = [
        'activeId', 'modalMode', 'confirmingDeleteId',
        'acceptingId', 'acceptBiayaJasa', 'acceptDeadline',
    ];

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $status = '';

    #[Url(as: 'tgl_dari', history: true)]
    public string $tanggalDari = '';

    #[Url(as: 'tgl_sampai', history: true)]
    public string $tanggalSampai = '';

    #[Url(as: 'dl_dari', history: true)]
    public string $deadlineDari = '';

    #[Url(as: 'dl_sampai', history: true)]
    public string $deadlineSampai = '';

    #[Url(as: 'sort', history: true)]
    public string $sortField = 'tanggal_pengajuan';

    #[Url(as: 'dir', history: true)]
    public string $sortDirection = 'desc';

    #[Url(as: 'per_page', history: true)]
    public int $perPage = 15;

    #[Url(as: 'trash', history: true)]
    public bool $trash = false;

    public SubmissionForm $form;

    public ?int $activeId = null;

    public string $modalMode = 'detail';

    public ?int $confirmingDeleteId = null;

    public ?int $acceptingId = null;

    public string $acceptBiayaJasa = '';

    public string $acceptDeadline = '';

    public function updated(string $name): void
    {
        if (in_array($name, self::TRANSIENT_PROPERTIES, true)) {
            return;
        }

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
        $this->reset([
            'search', 'status',
            'tanggalDari', 'tanggalSampai', 'deadlineDari', 'deadlineSampai',
        ]);
        $this->resetPage();
    }

    public function toggleTrash(): void
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $this->trash = ! $this->trash;
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->activeId = $id;
        $this->modalMode = 'detail';
    }

    public function openEdit(int $id): void
    {
        $submission = Submission::query()->findOrFail($id);

        $this->form->includeStatus = true;
        $this->form->fillFromSubmission($submission);
        $this->resetValidation();

        $this->activeId = $id;
        $this->modalMode = 'edit';
    }

    public function switchToEdit(): void
    {
        if ($this->activeId) {
            $this->openEdit($this->activeId);
        }
    }

    public function closeModal(): void
    {
        $this->activeId = null;
        $this->form->reset();
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->form->validate();

        $submission = Submission::query()->findOrFail($this->activeId);
        $submission->update($this->form->payload());

        $this->closeModal();
        session()->flash('success', 'Pengajuan berhasil diperbarui.');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function deleteSubmission(): void
    {
        Submission::query()->find($this->confirmingDeleteId)?->delete();
        $this->confirmingDeleteId = null;
        session()->flash('success', 'Pengajuan dipindahkan ke sampah.');
    }

    public function restoreSubmission(int $id): void
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        Submission::onlyTrashed()->find($id)?->restore();
        session()->flash('success', 'Pengajuan berhasil dipulihkan.');
    }

    public function openAccept(int $id): void
    {
        $submission = Submission::query()->findOrFail($id);

        $this->acceptingId = $id;
        $this->acceptBiayaJasa = '';
        $this->acceptDeadline = $submission->deadline->format('Y-m-d');
        $this->resetValidation();
    }

    public function closeAccept(): void
    {
        $this->acceptingId = null;
        $this->acceptBiayaJasa = '';
        $this->acceptDeadline = '';
        $this->resetValidation();
    }

    public function acceptSubmission(): void
    {
        $submission = Submission::query()->find($this->acceptingId);

        if (! $submission || $submission->status !== Submission::STATUS_BARU) {
            $this->closeAccept();

            return;
        }

        $this->validate([
            'acceptBiayaJasa' => ['required', 'integer', 'min:0'],
            'acceptDeadline' => ['required', 'date', 'after_or_equal:'.$submission->tanggal_pengajuan->format('Y-m-d')],
        ], [
            'acceptBiayaJasa.required' => 'Biaya jasa wajib diisi.',
            'acceptBiayaJasa.integer' => 'Biaya jasa harus berupa angka.',
            'acceptBiayaJasa.min' => 'Biaya jasa tidak boleh negatif.',
            'acceptDeadline.required' => 'Deadline wajib diisi.',
            'acceptDeadline.date' => 'Deadline tidak valid.',
            'acceptDeadline.after_or_equal' => 'Deadline harus sama atau setelah tanggal pengajuan.',
        ]);

        $submission->update([
            'status' => Submission::STATUS_DIPROSES,
            'biaya_jasa' => (int) $this->acceptBiayaJasa,
            'deadline' => $this->acceptDeadline,
        ]);

        $this->closeAccept();
        session()->flash('success', 'Pengajuan diterima dan sedang diproses.');
    }

    public function rejectSubmission(int $id): void
    {
        $submission = Submission::query()->find($id);

        if (! $submission || $submission->status !== Submission::STATUS_BARU) {
            return;
        }

        $submission->update(['status' => Submission::STATUS_DITOLAK]);
        session()->flash('success', 'Pengajuan ditolak.');
    }

    public function completeSubmission(int $id): void
    {
        $submission = Submission::query()->find($id);

        if (! $submission || $submission->status !== Submission::STATUS_DIPROSES) {
            return;
        }

        $submission->update(['status' => Submission::STATUS_SELESAI]);
        session()->flash('success', 'Pengajuan ditandai selesai.');
    }

    protected function filteredQuery(): Builder
    {
        $query = Submission::query();

        if ($this->trash) {
            $query->onlyTrashed();
        }

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('nama', 'like', "%{$this->search}%")
                    ->orWhere('judul_alat', 'like', "%{$this->search}%");
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->tanggalDari !== '') {
            $query->whereDate('tanggal_pengajuan', '>=', $this->tanggalDari);
        }

        if ($this->tanggalSampai !== '') {
            $query->whereDate('tanggal_pengajuan', '<=', $this->tanggalSampai);
        }

        if ($this->deadlineDari !== '') {
            $query->whereDate('deadline', '>=', $this->deadlineDari);
        }

        if ($this->deadlineSampai !== '') {
            $query->whereDate('deadline', '<=', $this->deadlineSampai);
        }

        $sortField = in_array($this->sortField, self::SORTABLE, true) ? $this->sortField : 'tanggal_pengajuan';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortField, $sortDirection);
    }

    public function exportQuery(): array
    {
        return [
            'q' => $this->search,
            'status' => $this->status,
            'tgl_dari' => $this->tanggalDari,
            'tgl_sampai' => $this->tanggalSampai,
            'dl_dari' => $this->deadlineDari,
            'dl_sampai' => $this->deadlineSampai,
        ];
    }

    public function render()
    {
        $summary = [
            'total' => Submission::query()->count(),
            'baru' => Submission::query()->where('status', Submission::STATUS_BARU)->count(),
            'diproses' => Submission::query()->where('status', Submission::STATUS_DIPROSES)->count(),
            'selesai' => Submission::query()->where('status', Submission::STATUS_SELESAI)->count(),
            'ditolak' => Submission::query()->where('status', Submission::STATUS_DITOLAK)->count(),
        ];

        return view('livewire.admin.dashboard', [
            'submissions' => $this->filteredQuery()->paginate($this->perPage),
            'summary' => $summary,
            'activeSubmission' => $this->activeId ? Submission::query()->withTrashed()->find($this->activeId) : null,
        ]);
    }
}
