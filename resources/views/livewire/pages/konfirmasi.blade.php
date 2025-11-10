<?php

use App\Models\Registration;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

new #[Layout('components.layouts.auth.simple', ['title' => 'Konfirmasi Pembayaran'])] class extends Component {
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $lookup_value = ''; // Bisa ID Invoice, Nama, atau No HP

    #[Validate('nullable|string|max:15')]
    public string $nomor_hp_lookup = ''; // Khusus untuk lookup nama

    public ?Registration $registration = null;
    public string $error_message = '';
    public string $success_message = '';

    #[Validate('required|image|max:5120')] // 5MB Max
    public $payment_proof;

    /**
     * Cari pendaftaran berdasarkan ID Invoice atau Nama & No HP
     */
    public function lookup(): void
    {
        $this->reset(['registration', 'error_message', 'success_message']);

        $this->registration = Registration::where('invoice_id', $this->lookup_value)
            ->orWhere(function ($query) {
                $query->where('nama_pendaftar', $this->lookup_value)
                      ->where('nomor_hp', $this->nomor_hp_lookup);
            })
            ->first();

        if (!$this->registration) {
            $this->error_message = 'Pendaftaran tidak ditemukan. Pastikan ID Invoice atau kombinasi Nama & No HP benar.';
        } elseif ($this->registration->status === 'paid') {
            $this->error_message = 'Pendaftaran ini sudah lunas.';
            $this->registration = null;
        } elseif ($this->registration->status === 'pending_verification') {
            $this->error_message = 'Bukti bayar sudah diterima dan sedang diverifikasi.';
            $this->registration = null;
        }
    }

    /**
     * Unggah bukti pembayaran
     */
    public function uploadProof(): void
    {
        $this->validate(['payment_proof' => 'required|image|max:5120']);

        if (!$this->registration) {
            $this->error_message = 'Sesi habis, silakan cari ulang pendaftaran Anda.';
            return;
        }

        try {
            $path = $this->payment_proof->store('payment_proofs', 'public');

            $this->registration->update([
                'payment_proof_path' => $path,
                'status' => 'pending_verification',
            ]);

            $this->success_message = 'Bukti pembayaran berhasil diunggah. Pendaftaran Anda akan diverifikasi oleh panitia dalam 2x24 jam.';
            $this->reset(['registration', 'lookup_value', 'nomor_hp_lookup', 'payment_proof']);
            
        } catch (\Exception $e) {
            $this->error_message = 'Gagal mengunggah file: ' . $e->getMessage();
        }
    }

}; ?>

<div class="flex flex-col gap-6 w-full">
    <x-auth-header :title="__('Konfirmasi Pembayaran')" :description="__('Unggah bukti bayar Anda di sini.')" />

    <!-- Navigasi Halaman -->
    <div class="flex justify-center gap-4 text-sm">
        <a href="{{ route('home') }}" wire:navigate class="font-medium text-primary-600 hover:text-primary-500">Daftar</a>
        <a href="{{ route('konfirmasi') }}" wire:navigate class="font-medium text-primary-600 hover:text-primary-500">Konfirmasi Bayar</a>
        <a href="{{ route('tiket') }}" wire:navigate class="font-medium text-primary-600 hover:text-primary-500">Cek Tiket</a>
    </div>

    @if ($success_message)
        <div class="p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
            {{ $success_message }}
        </div>
    @endif

    @if ($error_message)
        <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
            {{ $error_message }}
        </div>
    @endif

    @if (!$registration)
        <!-- Formulir Pencarian -->
        <form wire:submit="lookup" class="flex flex-col gap-6">
            <p class="text-sm text-center dark:text-zinc-400">Masukkan ID Invoice ATAU Nama & No. HP Anda.</p>
            
            <flux:input
                wire:model="lookup_value"
                :label="__('ID Invoice / Nama Pendaftar')"
                type="text"
                required
                placeholder="AJ6-XXXXXX-X atau Nama Anda"
            />

            <flux:input
                wire:model="nomor_hp_lookup"
                :label="__('No. HP (Isi jika mencari pakai nama)')"
                type="tel"
                placeholder="08123456789"
            />
            
            <flux:button variant="primary" type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ __('Cari Pendaftaran') }}</span>
                <span wire:loading>{{ __('Mencari...') }}</span>
            </flux:button>
        </form>
    @else
        <!-- Formulir Unggah -->
        <form wire:submit="uploadProof" class="flex flex-col gap-6 p-4 border rounded-lg dark:border-zinc-700">
            <h3 class="text-lg font-semibold dark:text-white">Data Ditemukan</h3>
            <div class="space-y-1 text-sm dark:text-zinc-300">
                <p>ID Invoice: <strong>{{ $registration->invoice_id }}</strong></p>
                <p>Nama: <strong>{{ $registration->nama_pendaftar }}</strong></p>
                <p>Total Bayar: <strong>Rp {{ number_format($registration->total_amount, 0, ',', '.') }}</strong></p>
            </div>

            <flux:input
                wire:model="payment_proof"
                :label="__('Unggah Bukti Pembayaran (Max 5MB)')"
                type="file"
                required
                accept="image/png, image/jpeg, image/jpg"
            />
            @error('payment_proof') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            <div wire:loading wire:target="payment_proof" class="text-sm dark:text-zinc-400">Mengunggah file...</div>

            <flux:button variant="primary" type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ __('Konfirmasi Pembayaran') }}</span>
                <span wire:loading>{{ __('Mengunggah...') }}</span>
            </flux:button>
        </form>
    @endif
</div>