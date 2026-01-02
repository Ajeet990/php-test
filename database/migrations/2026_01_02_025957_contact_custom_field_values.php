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
        Schema::create('contact_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('custom_field_id');
            $table->text('field_value')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('contact_id', 'idx_contact_id');
            $table->index('custom_field_id', 'idx_custom_field_id');
            $table->unique(['contact_id', 'custom_field_id'], 'unique_contact_custom_field');
            
            // Foreign keys
            $table->foreign('contact_id', 'fk_ccfv_contact_id')
                  ->references('id')
                  ->on('contacts')
                  ->onDelete('cascade');
                  
            $table->foreign('custom_field_id', 'fk_ccfv_custom_field_id')
                  ->references('id')
                  ->on('custom_fields')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_custom_field_values');
    }
};