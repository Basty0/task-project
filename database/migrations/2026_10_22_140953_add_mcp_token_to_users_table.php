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
            $table->string('mcp_token_hash', 64)->nullable()->unique()->after('is_admin');
            $table->string('mcp_token_prefix', 12)->nullable()->after('mcp_token_hash');
            $table->timestamp('mcp_token_created_at')->nullable()->after('mcp_token_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mcp_token_hash', 'mcp_token_prefix', 'mcp_token_created_at']);
        });
    }
};
