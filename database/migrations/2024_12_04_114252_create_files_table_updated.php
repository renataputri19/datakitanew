<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Changed to UUID
            $table->foreignUuid('domain_id')->constrained('domains')->cascadeOnDelete(); // Changed to UUID
            $table->string('file_path'); // File path
            $table->boolean('hasil')->nullable(); // Approval status
            $table->text('reasons')->nullable(); // Reasons for approval/rejection
            $table->string('original_name')->nullable();
            $table->string('context')->default('pembinaan');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Added user_id
            $table->timestamps(); // For 'Last Updated'
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
