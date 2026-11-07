<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_platform_admin')->default(false)->after('password');
            $table->string('locale', 10)->default('en')->after('is_platform_admin');
            $table->string('timezone')->default('UTC')->after('locale');
            $table->string('phone')->nullable()->after('timezone');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            $table->json('preferences')->nullable()->after('last_login_at');
            $table->foreignId('current_organization_id')->nullable()->after('preferences')->constrained('organizations')->nullOnDelete();
            $table->foreignId('current_tenant_id')->nullable()->after('current_organization_id')->constrained('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_organization_id']);
            $table->dropForeign(['current_tenant_id']);
            $table->dropColumn([
                'is_platform_admin',
                'locale',
                'timezone',
                'phone',
                'avatar_path',
                'last_login_at',
                'preferences',
                'current_organization_id',
                'current_tenant_id',
            ]);
        });
    }
};
