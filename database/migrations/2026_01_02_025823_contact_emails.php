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
        Schema::create('contact_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->string('email', 255);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            // Index
            $table->index('contact_id', 'idx_contact_id');
            
            // Foreign key
            $table->foreign('contact_id', 'fk_ce_contact_id')
                  ->references('id')
                  ->on('contacts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_emails');
    }
};