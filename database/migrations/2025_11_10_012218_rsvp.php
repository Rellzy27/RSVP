<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique()->nullable();
            $table->string('nama_pendaftar');
            $table->string('nomor_hp');
            $table->unsignedInteger('total_amount')->default(0);
            $table->unsignedInteger('unique_code')->default(0);
            $table->string('payment_proof_path')->nullable();
            $table->string('status')->default('pending'); // pending, pending_verification, paid
            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->string('ticket_code')->unique()->nullable(); // Kode unik QR (misal: 25110710001)
            
            $table->string('nama_anak');
            $table->string('nama_panggilan');
            $table->string('jenis_kelamin');
            $table->integer('usia');
            $table->string('paroki')->nullable();
            $table->string('sekolah')->nullable();
            $table->boolean('sudah_komuni')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
        Schema::dropIfExists('registrations');
    }
};