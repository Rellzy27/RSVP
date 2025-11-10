<?php

use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new #[Layout('components.layouts.public', ['title' => 'Pendaftaran Amazing Journey 6'])] class extends Component {
    
    // Properti untuk data Pendaftar (Orang Tua)
    #[Validate('required|string|min:3|max:255')]
    public string $nama_pendaftar = '';
    
    #[Validate('required|string|min:10|max:15')]
    public string $nomor_hp = '';

    // Properti untuk data Peserta (Anak)
    #[Validate('required|array|min:1')]
    public array $participants = [];

    // Properti untuk info acara
    public int $ticket_price = 35000;
    public int $step = 1;

    // Untuk menyimpan hasil registrasi
    public ?Registration $registration = null;

    /**
     * Inisialisasi validasi untuk peserta
     */
    protected function participantValidationRules(): array
    {
        return [
            'participants.*.nama_anak' => 'required|string|max:255',
            'participants.*.nama_panggilan' => 'required|string|max:255',
            'participants.*.jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'participants.*.usia' => 'required|integer|min:6|max:12',
            'participants.*.paroki' => 'nullable|string|max:255',
            'participants.*.sekolah' => 'nullable|string|max:255',
            'participants.*.sudah_komuni' => 'required|boolean',
        ];
    }
    
    /**
     * Pindah ke langkah 2 (Data Anak)
     */
    public function nextStep(): void
    {
        $this->validate([
            'nama_pendaftar' => 'required|string|min:3|max:255',
            'nomor_hp' => 'required|string|min:10|max:15',
        ]);
        
        // Tambah 1 peserta default
        if (empty($this->participants)) {
            $this->addParticipant();
        }
        
        $this->step = 2;
    }

    /**
     * Tambah form peserta baru
     */
    public function addParticipant(): void
    {
        $this->participants[] = [
            'nama_anak' => '',
            'nama_panggilan' => '',
            'jenis_kelamin' => 'Laki-laki',
            'usia' => 6,
            'paroki' => '',
            'sekolah' => '',
            'sudah_komuni' => false,
        ];
    }

    /**
     * Hapus form peserta
     */
    public function removeParticipant(int $index): void
    {
        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
    }

    /**
     * Simpan pendaftaran (Bagian A)
     */
    public function submitRegistration(): void
    {
        $this->validate($this->participantValidationRules());

        try {
            DB::beginTransaction();

            // 1. Buat data Pendaftar (Orang Tua)
            $reg = Registration::create([
                'nama_pendaftar' => $this->nama_pendaftar,
                'nomor_hp' => $this->nomor_hp,
                'status' => 'pending',
                'unique_code' => 0, // sementara
                'total_amount' => 0, // sementara
            ]);

            // 2. Buat data Peserta (Anak)
            foreach ($this->participants as $data) {
                $participant = $reg->participants()->create($data);
                
                // Buat QR Code Unik (YYMMDD + 10000 + ID Peserta)
                $ticket_code = now()->format('ymd') . (10000 + $participant->id);
                $participant->update(['ticket_code' => $ticket_code]);
            }

            // 3. Hitung total bayar
            $unique_code = $reg->id; // Kode unik adalah ID pendaftaran
            $total_amount = (count($this->participants) * $this->ticket_price) + $unique_code;
            $invoice_id = 'AJ6-' . now()->format('ymd') . '-' . $reg->id;

            // 4. Update data pendaftaran
            $reg->update([
                'invoice_id' => $invoice_id,
                'unique_code' => $unique_code,
                'total_amount' => $total_amount,
            ]);

            DB::commit();

            $this->registration = $reg->fresh(); // Ambil data terbaru
            $this->step = 3; // Pindah ke langkah 3

        } catch (\Exception $e) {
            DB::rollBack();
            // Tampilkan error
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}; ?>

<div class="flex flex-col gap-6 w-full">

    <div class="flex flex-col items-center text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pendaftaran Amazing Journey 6</h1>
        <p class="text-gray-600 dark:text-gray-400">Magical World of Hope (6-12 Tahun)</p>
    </div>

    @if (session('error'))
        <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- LANGKAH 1: Data Pendaftar (Orang Tua) -->
    @if ($step === 1)
        <form wire:submit="nextStep" class="flex flex-col gap-6">
            <x-auth-header :title="__('Langkah 1: Data Pendaftar (Orang Tua)')" :description="__('Masukkan nama dan nomor WhatsApp Anda.')" />
            
            <flux:input
                wire:model="nama_pendaftar"
                :label="__('Nama Lengkap Pendaftar (Orang Tua)')"
                type="text"
                required
                autocomplete="name"
                placeholder="Nama Orang Tua"
            />
            
            <flux:input
                wire:model="nomor_hp"
                :label="__('Nomor WhatsApp (No WA)')"
                type="tel"
                required
                autocomplete="tel"
                placeholder="08123456789"
            />
            
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Lanjut ke Data Anak') }}
            </flux:button>
        </form>
    @endif

    <!-- LANGKAH 2: Data Peserta (Anak) -->
    @if ($step === 2)
        <form wire:submit="submitRegistration" class="flex flex-col gap-6">
            <x-auth-header :title="__('Langkah 2: Data Peserta (Anak)')" :description="__('Masukkan data anak yang akan mendaftar.')" />

            @foreach ($participants as $index => $participant)
                <div class="p-4 border rounded-lg dark:border-zinc-700" wire:key="participant-{{ $index }}">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold dark:text-white">Peserta {{ $index + 1 }}</h3>
                        
                        @if (count($participants) > 1)
                            <flux:button type="button" variant="danger" size="sm" 
                                         wire:click.prevent="removeParticipant({{ $index }})" 
                                         class="!p-2">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                </svg>
                            </flux:button>
                        @endif
                    </div>
                    <!-- Akhir Perbaikan -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input
                            wire:model="participants.{{ $index }}.nama_anak"
                            :label="__('Nama Lengkap Anak')"
                            type="text" required
                        />
                        <flux:input
                            wire:model="participants.{{ $index }}.nama_panggilan"
                            :label="__('Nama Panggilan')"
                            type="text" required
                        />
                        <flux:select wire:model="participants.{{ $index }}.jenis_kelamin" :label="__('Jenis Kelamin')" required>
                            <option value="Laki-laki">Laki-laki</a:option>
                            <option value="Perempuan">Perempuan</option>
                        </flux:select>
                        <flux:input
                            wire:model="participants.{{ $index }}.usia"
                            :label="__('Usia (6-12 tahun)')"
                            type="number" min="6" max="12" required
                        />
                        <flux:input
                            wire:model="participants.{{ $index }}.paroki"
                            :label="__('Paroki (Opsional)')"
                            type="text"
                        />
                        <flux:input
                            wire:model="participants.{{ $index }}.sekolah"
                            :label="__('Sekolah (Opsional)')"
                            type="text"
                        />
                        <flux:select wire:model="participants.{{ $index }}.sudah_komuni" :label="__('Sudah Komuni Pertama?')" required>
                            <option value="0">Belum</option>
                            <option value="1">Sudah</option>
                        </flux:select>
                    </div>
                </div>
            @endforeach

            <flux:button type="button" variant="outline" wire:click.prevent="addParticipant" class="w-full">
                + {{ __('Tambah Peserta Lain') }}
            </flux:button>

            <div class="flex gap-4">
                <flux:button type="button" variant="filled" wire:click="$set('step', 1)" class="w-full">
                    {{ __('Kembali') }}
                </flux:button>
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Daftar Sekarang') }}
                </flux:button>
            </div>
        </form>
    @endif

    <!-- LANGKAH 3: Ringkasan Pembayaran -->
    @if ($step === 3 && $registration)
        <div class="flex flex-col gap-4 p-6 bg-zinc-50 dark:bg-zinc-800 rounded-lg shadow">
            <x-auth-header :title="__('Pendaftaran Berhasil!')" :description="__('Silakan selesaikan pembayaran Anda.')" />

            <div class="space-y-2 text-sm dark:text-zinc-300">
                <p class="flex justify-between"><span>ID Invoice:</span> <strong class="dark:text-white">{{ $registration->invoice_id }}</strong></p>
                <p class="flex justify-between"><span>Nama Pendaftar:</span> <strong class="dark:text-white">{{ $registration->nama_pendaftar }}</strong></p>
                <p class="flex justify-between"><span>Nomor HP:</span> <strong class="dark:text-white">{{ $registration->nomor_hp }}</strong></p>
            </div>
            <hr class="dark:border-zinc-700">
            <div class="space-y-2 text-sm dark:text-zinc-300">
                <p class="flex justify-between"><span>Harga Tiket:</span> <span>Rp {{ number_format($ticket_price, 0, ',', '.') }} x {{ $registration->participants->count() }} Peserta</span></p>
                <p class="flex justify-between"><span>Kode Unik:</span> <span>Rp {{ number_format($registration->unique_code, 0, ',', '.') }}</span></p>
                <p class="flex justify-between text-lg font-bold dark:text-white">
                    <span>Total Pembayaran:</span> 
                    <span>Rp {{ number_format($registration->total_amount, 0, ',', '.') }}</span>
                </p>
                <p class="text-xs text-zinc-500">(*Pastikan Anda mentransfer hingga digit terakhir)</p>
            </div>
            <hr class="dark:border-zinc-700">
            <div class="space-y-2 text-sm dark:text-zinc-300">
                <p class="font-semibold dark:text-white">Silakan transfer ke rekening:</p>
                <p>Bank: <strong>BCA</strong></p>
                <p>No. Rek: <strong>4400398114</strong></p>
                <p>Atas Nama: <strong>Novia M.N / Veronica O.S</strong></p>
            </div>
            
            <flux:button as="a" href="{{ route('konfirmasi') }}" wire:navigate variant="primary" class="w-full text-center">
                {{ __('Saya Sudah Bayar, Lakukan Konfirmasi') }}
            </flux:button>
        </div>
    @endif

</div>