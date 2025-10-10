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
        Schema::table('temporary_survei_sibstr', function (Blueprint $table) {
            // Add company_id reference (nullable for backward compatibility)
            $table->uuid('company_id')->nullable()->after('email');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            
            // Add alamat field
            $table->text('alamat')->nullable()->after('perusahaan');
            
            // Add index for better performance
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temporary_survei_sibstr', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropColumn(['company_id', 'alamat']);
        });
    }
};
