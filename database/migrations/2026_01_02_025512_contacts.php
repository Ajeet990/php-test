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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phone', 20);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('profile_image', 255)->nullable();
            $table->string('additional_file', 255)->nullable();
            $table->boolean('is_merged')->default(false);
            $table->unsignedBigInteger('merged_into')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('email', 'idx_email');
            $table->index('is_merged', 'idx_is_merged');
            $table->index('merged_into', 'idx_merged_into');
        });
        
        // Add foreign key after table creation
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreign('merged_into', 'fk_contacts_merged_into')
                  ->references('id')
                  ->on('contacts')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('fk_contacts_merged_into');
        });
        
        Schema::dropIfExists('contacts');
    }
};