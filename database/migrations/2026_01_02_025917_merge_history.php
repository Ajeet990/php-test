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
        Schema::create('merge_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_contact_id');
            $table->unsignedBigInteger('merged_contact_id');
            $table->json('merge_data')->nullable();
            $table->unsignedBigInteger('merged_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('master_contact_id', 'idx_master_contact_id');
            $table->index('merged_contact_id', 'idx_merged_contact_id');
            
            // Foreign keys
            $table->foreign('master_contact_id', 'fk_mh_master_contact_id')
                  ->references('id')
                  ->on('contacts')
                  ->onDelete('cascade');
                  
            $table->foreign('merged_contact_id', 'fk_mh_merged_contact_id')
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
        Schema::dropIfExists('merge_history');
    }
};