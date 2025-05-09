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
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Changed to UUID
            $table->string('task');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('category')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Added user_id
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
