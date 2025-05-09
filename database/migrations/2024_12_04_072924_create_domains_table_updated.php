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
        Schema::create('domains', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Changed to UUID
            $table->string('domain'); // Domain name
            $table->string('aspek'); // Aspek name
            $table->string('indikator'); // Indikator name
            $table->integer('tingkat')->nullable(); // Tingkat (level/score)
            $table->boolean('disetujui')->nullable(); // Approval status
            $table->text('reasons')->nullable(); // Reasons for approval/rejection
            $table->timestamps(); // created_at and updated_at
            $table->integer('tingkat_tpb')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Using regular integer foreign key
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
