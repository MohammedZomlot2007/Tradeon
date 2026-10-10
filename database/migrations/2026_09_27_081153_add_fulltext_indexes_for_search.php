<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fulltext indexes apply to MySQL production only; SQLite (local/tests) skips them.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->fulltext(['name', 'description', 'industry'], 'companies_search_fulltext');
        });
        Schema::table('product_showcases', function (Blueprint $table) {
            $table->fulltext(['title', 'description', 'category'], 'showcases_search_fulltext');
        });
        Schema::table('business_requests', function (Blueprint $table) {
            $table->fulltext(['title', 'description', 'industry'], 'requests_search_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('business_requests', function (Blueprint $table) {
            $table->dropFullText('requests_search_fulltext');
        });
        Schema::table('product_showcases', function (Blueprint $table) {
            $table->dropFullText('showcases_search_fulltext');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropFullText('companies_search_fulltext');
        });
    }
};
