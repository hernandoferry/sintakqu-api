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
         Schema::create('transaksi', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('nominal', 12, 2); // Nominal Transaksi (Menggunakan decimal agar akurat untuk keuangan)
            $table->string('kategori');
            $table->text('catatan')->nullable();
            $table->timestamps(); // Otomatis membuat kolom created_at dan updated_at
            Schema::dropIfExists('transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
