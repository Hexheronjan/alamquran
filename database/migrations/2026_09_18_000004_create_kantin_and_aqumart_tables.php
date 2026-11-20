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
        // Tabel Produk Kantin & Minimarket Aqumart
        Schema::create('kantin_produks', function (Blueprint $table) {
            $table->id();
            $table->enum('toko', ['kantin_1', 'kantin_2', 'aqumart'])->default('aqumart');
            $table->string('nama_produk');
            $table->string('kode_produk', 50)->nullable()->unique();
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_modal', 12, 2)->default(0); // HPP untuk kalkulasi laba/rugi
            $table->decimal('harga_jual', 12, 2);
            $table->integer('stok')->default(0);
            $table->string('foto_produk')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['toko', 'is_active']);
        });

        // Tabel Transaksi Kasir Kantin / Aqumart
        Schema::create('kantin_transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 40)->unique();
            $table->enum('toko', ['kantin_1', 'kantin_2', 'aqumart'])->default('aqumart');
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kasir_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('total_modal', 12, 2)->default(0);
            $table->decimal('total_harga', 12, 2);
            $table->decimal('laba_bersih', 12, 2)->default(0);
            $table->enum('metode_pembayaran', ['uang_saku', 'tunai'])->default('uang_saku');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['toko', 'created_at']);
            $table->index(['santri_id', 'created_at']);
        });

        // Tabel Rincian Item Transaksi
        Schema::create('kantin_transaksi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('kantin_transaksis')->cascadeOnDelete();
            $table->foreignId('produk_id')->nullable()->constrained('kantin_produks')->nullOnDelete();
            $table->string('nama_produk');
            $table->decimal('harga_modal', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2);
            $table->integer('qty')->default(1);
            $table->decimal('subtotal_modal', 12, 2)->default(0);
            $table->decimal('subtotal_harga', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantin_transaksi_items');
        Schema::dropIfExists('kantin_transaksis');
        Schema::dropIfExists('kantin_produks');
    }
};
