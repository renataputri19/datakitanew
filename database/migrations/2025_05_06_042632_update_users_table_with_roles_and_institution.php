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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('institution_id')->nullable()->after('email');
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('set null');
            $table->boolean('is_bps')->default(false)->after('institution_id');
            $table->boolean('is_admin')->default(false)->after('is_bps');
            $table->boolean('is_superadmin')->default(false)->after('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn(['institution_id', 'is_bps', 'is_admin', 'is_superadmin']);
        });
    }
};
