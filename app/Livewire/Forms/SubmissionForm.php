<?php

namespace App\Livewire\Forms;

use App\Models\Submission;
use Livewire\Form;

class SubmissionForm extends Form
{
    public string $nama = '';
    public string $asal_kampus = '';
    public string $judul_alat = '';
    public string $tipe = '';
    public string $fitur = '';
    public string $tanggal_pengajuan = '';
    public string $deadline = '';
    public string $status = Submission::STATUS_BARU;
    public string $biaya_jasa = '';
    public string $pembagian_prioritas = '';
    public string $penerima = '';
    public string $metode_pembayaran = '';
    public string $jumlah_bayar = '';

    public bool $includeStatus = false;

    public function rules(): array
    {
        $rules = [
            'nama' => ['required', 'string', 'min:3', 'max:100'],
            'asal_kampus' => ['required', 'string', 'min:3', 'max:150'],
            'judul_alat' => ['required', 'string', 'min:3', 'max:150'],
            'tipe' => ['required', 'in:'.implode(',', Submission::TIPES)],
            'fitur' => ['required', 'string', 'max:1000'],
            'tanggal_pengajuan' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after_or_equal:tanggal_pengajuan'],
        ];

        if ($this->includeStatus) {
            $rules['status'] = ['required', 'in:'.implode(',', Submission::STATUSES)];
            $rules['biaya_jasa'] = ['nullable', 'integer', 'min:0'];
            $rules['pembagian_prioritas'] = ['nullable', 'integer', 'min:0'];

            if ($this->biaya_jasa !== '') {
                $rules['pembagian_prioritas'][] = 'lte:biaya_jasa';
            }

            if ($this->tipe === Submission::TIPE_LAIN_LAIN) {
                if ($this->status !== Submission::STATUS_BARU) {
                    $rules['penerima'] = ['required', 'in:'.implode(',', Submission::PENERIMAS)];
                } else {
                    $rules['penerima'] = ['nullable', 'in:'.implode(',', Submission::PENERIMAS)];
                }
            } else {
                $rules['penerima'] = ['nullable'];
            }

            if ($this->status === Submission::STATUS_SELESAI) {
                $rules['metode_pembayaran'] = ['required', 'in:'.implode(',', Submission::METODE_PEMBAYARANS)];
                $rules['jumlah_bayar'] = ['required', 'integer', 'min:0'];
                if ($this->biaya_jasa !== '') {
                    $rules['jumlah_bayar'][] = 'gte:biaya_jasa';
                }
            } else {
                $rules['metode_pembayaran'] = ['nullable', 'in:'.implode(',', Submission::METODE_PEMBAYARANS)];
                $rules['jumlah_bayar'] = ['nullable', 'integer', 'min:0'];
            }
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
            'tipe.required' => 'Tipe wajib dipilih.',
            'tipe.in' => 'Tipe tidak valid.',
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
            'pembagian_prioritas.integer' => 'Pembagian prioritas harus berupa angka.',
            'pembagian_prioritas.min' => 'Pembagian prioritas tidak boleh negatif.',
            'pembagian_prioritas.lte' => 'Pembagian prioritas tidak boleh lebih besar dari biaya jasa.',
            'penerima.required' => 'Penerima wajib dipilih.',
            'penerima.in' => 'Penerima tidak valid.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid.',
            'jumlah_bayar.required' => 'Jumlah bayar wajib diisi.',
            'jumlah_bayar.integer' => 'Jumlah bayar harus berupa angka.',
            'jumlah_bayar.min' => 'Jumlah bayar tidak boleh negatif.',
            'jumlah_bayar.gte' => 'Jumlah bayar tidak boleh kurang dari biaya jasa.',
        ];
    }

    public function fillFromSubmission(Submission $submission): void
    {
        $this->nama = $submission->nama;
        $this->asal_kampus = $submission->asal_kampus;
        $this->judul_alat = $submission->judul_alat;
        $this->tipe = $submission->tipe;
        $this->fitur = $submission->fitur;
        $this->tanggal_pengajuan = $submission->tanggal_pengajuan->format('Y-m-d');
        $this->deadline = $submission->deadline->format('Y-m-d');
        $this->status = $submission->status;
        $this->biaya_jasa = $submission->biaya_jasa !== null ? (string) $submission->biaya_jasa : '';
        $this->pembagian_prioritas = $submission->pembagian_prioritas !== null ? (string) $submission->pembagian_prioritas : '';
        $this->penerima = $submission->penerima ?? '';
        $this->metode_pembayaran = $submission->metode_pembayaran ?? '';
        $this->jumlah_bayar = $submission->jumlah_bayar !== null ? (string) $submission->jumlah_bayar : '';
    }

    public function payload(): array
    {
        $data = [
            'nama' => $this->nama,
            'asal_kampus' => $this->asal_kampus,
            'judul_alat' => $this->judul_alat,
            'tipe' => $this->tipe,
            'fitur' => $this->fitur,
            'tanggal_pengajuan' => $this->tanggal_pengajuan,
            'deadline' => $this->deadline,
        ];

        if ($this->includeStatus) {
            $data['status'] = $this->status;
            $data['biaya_jasa'] = $this->biaya_jasa !== '' ? (int) $this->biaya_jasa : null;
            $data['pembagian_prioritas'] = $this->pembagian_prioritas !== '' ? (int) $this->pembagian_prioritas : 0;
            $data['penerima'] = ($this->tipe === Submission::TIPE_LAIN_LAIN && $this->penerima !== '') ? $this->penerima : null;
            $data['metode_pembayaran'] = $this->metode_pembayaran !== '' ? $this->metode_pembayaran : null;
            $data['jumlah_bayar'] = $this->jumlah_bayar !== '' ? (int) $this->jumlah_bayar : null;
        }

        return $data;
    }
}
