<?php

namespace App\Livewire\Forms;

use App\Models\Submission;
use Livewire\Form;

class SubmissionForm extends Form
{
    public string $nama = '';
    public string $asal_kampus = '';
    public string $judul_alat = '';
    public string $fitur = '';
    public string $tanggal_pengajuan = '';
    public string $deadline = '';
    public string $status = Submission::STATUS_BARU;
    public string $biaya_jasa = '';

    public bool $includeStatus = false;

    public function rules(): array
    {
        $rules = [
            'nama' => ['required', 'string', 'min:3', 'max:100'],
            'asal_kampus' => ['required', 'string', 'min:3', 'max:150'],
            'judul_alat' => ['required', 'string', 'min:3', 'max:150'],
            'fitur' => ['required', 'string', 'max:1000'],
            'tanggal_pengajuan' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after_or_equal:tanggal_pengajuan'],
        ];

        if ($this->includeStatus) {
            $rules['status'] = ['required', 'in:'.implode(',', Submission::STATUSES)];
            $rules['biaya_jasa'] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.min' => 'Nama minimal 3 karakter.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'asal_kampus.required' => 'Asal kampus/sekolah wajib diisi.',
            'asal_kampus.min' => 'Asal kampus/sekolah minimal 3 karakter.',
            'asal_kampus.max' => 'Asal kampus/sekolah maksimal 150 karakter.',
            'judul_alat.required' => 'Judul alat wajib diisi.',
            'judul_alat.min' => 'Judul alat minimal 3 karakter.',
            'judul_alat.max' => 'Judul alat maksimal 150 karakter.',
            'fitur.required' => 'Fitur wajib diisi.',
            'fitur.max' => 'Fitur maksimal 1000 karakter.',
            'tanggal_pengajuan.required' => 'Tanggal pengajuan wajib diisi.',
            'tanggal_pengajuan.date' => 'Tanggal pengajuan tidak valid.',
            'deadline.required' => 'Deadline wajib diisi.',
            'deadline.date' => 'Deadline tidak valid.',
            'deadline.after_or_equal' => 'Deadline harus sama atau setelah tanggal pengajuan.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'biaya_jasa.integer' => 'Biaya jasa harus berupa angka.',
            'biaya_jasa.min' => 'Biaya jasa tidak boleh negatif.',
        ];
    }

    public function fillFromSubmission(Submission $submission): void
    {
        $this->nama = $submission->nama;
        $this->asal_kampus = $submission->asal_kampus;
        $this->judul_alat = $submission->judul_alat;
        $this->fitur = $submission->fitur;
        $this->tanggal_pengajuan = $submission->tanggal_pengajuan->format('Y-m-d');
        $this->deadline = $submission->deadline->format('Y-m-d');
        $this->status = $submission->status;
        $this->biaya_jasa = $submission->biaya_jasa !== null ? (string) $submission->biaya_jasa : '';
    }

    public function payload(): array
    {
        $data = [
            'nama' => $this->nama,
            'asal_kampus' => $this->asal_kampus,
            'judul_alat' => $this->judul_alat,
            'fitur' => $this->fitur,
            'tanggal_pengajuan' => $this->tanggal_pengajuan,
            'deadline' => $this->deadline,
        ];

        if ($this->includeStatus) {
            $data['status'] = $this->status;
            $data['biaya_jasa'] = $this->biaya_jasa !== '' ? (int) $this->biaya_jasa : null;
        }

        return $data;
    }
}
