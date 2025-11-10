<?php

use App\Models\Registration;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

new #[Layout('components.layouts.auth.simple', ['title' => 'Cek Tiket'])] class extends Component {
    
    #[Validate('required|string|max:255')]
    public string $lookup_value = ''; // Bisa ID Invoice, Nama, atau No HP

    #[Validate('nullable|string|max:15')]
    public string $nomor_hp_lookup = ''; // Khusus untuk lookup nama

    public ?Registration $registration = null;
    public string $error_message = '';

    /**
     * Cari pendaftaran berdasarkan ID Invoice atau Nama & No HP
     */
    public function lookup(): void
    {
        $this->reset(['registration', 'error_message']);

        $this->registration = Registration::with('participants') // Eager load peserta
            ->where('invoice_id', $this->lookup_value)
            ->orWhere(function ($query) {
                $query->where('nama_pendaftar', $this->lookup_value)
                      ->where('nomor_hp', $this->nomor_hp_lookup);
            })
            ->first();

        if (!$this->registration) {
            $this->error_message = 'Pendaftaran tidak ditemukan. Pastikan ID Invoice atau kombinasi Nama & No HP benar.';
        }
    }

    /**
     * Buat QR Code sebagai string SVG
     */
    public function getQrCodeSvg(string $ticketCode): string
    {
        $renderer = new SvgImageBackEnd();
        $rendererStyle = new RendererStyle(200, 0); // Ukuran 200px, margin 0
        $writer = new Writer($renderer, $rendererStyle);
        
        // Mengembalikan string SVG
        return $writer->writeString($ticketCode);
    }
}; ?>

<div class="flex flex-col gap-6 w-full">
    <x-auth-header :title="__('Cek E-Tiket Anda')" :description="__('Lihat status pembayaran dan e-tiket Anda.')" />

    <!-- Navigasi Halaman -->
    <div class="flex justify-center gap-4 text-sm">
        <a href="{{ route('home') }}" wire:navigate class="font-medium text-primary-600 hover:text-primary-500">Daftar</a>
        <a href="{{ route('konfirmasi') }}" wire:navigate class="font-medium text-primary-600 hover:text-primary-500">Konfirmasi Bayar</a>
        <a href="{{ route('tiket') }}" wire:navigate class="font-medium text-primary-600 hover:text-primary-500">Cek Tiket</a>
    </div>

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
                <span wire:loading.remove>{{ __('Cari Tiket') }}</span>
                <span wire:loading>{{ __('Mencari...') }}</span>
            </flux:button>
        </form>
    @else
        <!-- Tampilan Tiket -->
        <div class="flex flex-col gap-6">
            <div class="p-4 border rounded-lg dark:border-zinc-700 text-center">
                <h3 class="text-lg font-semibold dark:text-white">Data Pendaftaran</h3>
                <p class="text-sm dark:text-zinc-300">ID Invoice: <strong>{{ $registration->invoice_id }}</strong></p>
                <p class="text-sm dark:text-zinc-300">Nama Pendaftar: <strong>{{ $registration->nama_pendaftar }}</strong></p>
                
                @if ($registration->status === 'paid')
                    <span class="inline-block mt-2 px-3 py-1 text-sm font-semibold text-green-800 bg-green-200 rounded-full">LUNAS</span>
                @elseif ($registration->status === 'pending_verification')
                    <span class="inline-block mt-2 px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-200 rounded-full">SEDANG VERIFIKASI</span>
                @else
                    <span class="inline-block mt-2 px-3 py-1 text-sm font-semibold text-red-800 bg-red-200 rounded-full">BELUM LUNAS</span>
                @endif
            </div>

            <h4 class="text-md font-semibold dark:text-white text-center">Daftar Tiket Peserta</h4>
            
            @foreach ($registration->participants as $participant)
                <div class="p-4 border rounded-lg dark:border-zinc-700" wire:key="participant-ticket-{{ $participant->id }}">
                    <p class="font-bold text-lg dark:text-white">{{ $participant->nama_anak }} ({{ $participant->usia }} thn)</p>
                    <p class="text-sm dark:text-zinc-400">{{ $participant->nama_panggilan }} | {{ $participant->jenis_kelamin }}</p>
                    <hr class="my-4 dark:border-zinc-700">

                    @if ($registration->status === 'paid')
                        <div class="flex flex-col items-center gap-4">
                            <p class="font-semibold dark:text-white">E-Tiket Telah Terbit</p>
                            <div class="p-4 bg-white rounded-lg">
                                <!-- Tampilkan SVG QR Code -->
                                {!! $this->getQrCodeSvg($participant->ticket_code) !!}
                            </div>
                            <p class="text-sm font-mono dark:text-zinc-300">{{ $participant->ticket_code }}</p>
                            <p class="text-xs text-center dark:text-zinc-400">Tunjukkan QR Code ini di pintu masuk.</p>
                        </div>
                    @else
                        <p class="text-center text-sm dark:text-zinc-400">Tiket akan terbit setelah pembayaran Anda diverifikasi oleh panitia.</p>
                        @if ($registration->status === 'pending')
                            <flux:button as="a" href="{{ route('konfirmasi') }}" wire:navigate variant="outline" class="w-full text-center mt-4">
                                {{ __('Konfirmasi Pembayaran Anda') }}
                            </flux:button>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>