<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unique('user_id', 'companies_user_id_unique');
            $table->index(['country', 'industry'], 'companies_country_industry_index');
            $table->index('verified_tier', 'companies_verified_tier_index');
        });

        Schema::table('connections', function (Blueprint $table) {
            $table->unique(['user_id', 'target_user_id', 'type'], 'connections_user_target_type_unique');
            $table->unique(['user_id', 'target_company_id', 'type'], 'connections_user_company_type_unique');
            $table->index(['target_company_id', 'type'], 'connections_company_type_index');
        });

        Schema::table('saved_items', function (Blueprint $table) {
            $table->unique(['user_id', 'item_type', 'item_id'], 'saved_items_unique');
            $table->index(['item_type', 'item_id'], 'saved_items_item_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['company_id', 'created_at'], 'posts_company_created_index');
            $table->index(['type', 'created_at'], 'posts_type_created_index');
            $table->index(['user_id', 'created_at'], 'posts_user_created_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at'], 'messages_conversation_created_index');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->index(['user_one_id', 'user_two_id'], 'conversations_participants_index');
        });

        Schema::table('chat_requests', function (Blueprint $table) {
            $table->index(['receiver_id', 'status'], 'chat_requests_receiver_status_index');
            $table->index(['sender_id', 'receiver_id'], 'chat_requests_parties_index');
        });

        Schema::table('business_requests', function (Blueprint $table) {
            $table->index(['status', 'request_type'], 'business_requests_status_type_index');
            $table->index('industry', 'business_requests_industry_index');
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'rfqs_status_created_index');
        });

        Schema::table('rfq_quotes', function (Blueprint $table) {
            $table->unique(['rfq_id', 'company_id'], 'rfq_quotes_rfq_company_unique');
        });

        Schema::table('verifications', function (Blueprint $table) {
            $table->index(['status', 'company_id'], 'verifications_status_company_index');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->index('status', 'reports_status_index');
        });

        Schema::table('biz_events', function (Blueprint $table) {
            $table->index(['country', 'industry'], 'biz_events_country_industry_index');
        });

        Schema::table('product_showcases', function (Blueprint $table) {
            $table->index('company_id', 'product_showcases_company_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_showcases', function (Blueprint $table) {
            $table->dropIndex('product_showcases_company_index');
        });

        Schema::table('biz_events', function (Blueprint $table) {
            $table->dropIndex('biz_events_country_industry_index');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_status_index');
        });

        Schema::table('verifications', function (Blueprint $table) {
            $table->dropIndex('verifications_status_company_index');
        });

        Schema::table('rfq_quotes', function (Blueprint $table) {
            $table->dropUnique('rfq_quotes_rfq_company_unique');
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropIndex('rfqs_status_created_index');
        });

        Schema::table('business_requests', function (Blueprint $table) {
            $table->dropIndex('business_requests_status_type_index');
            $table->dropIndex('business_requests_industry_index');
        });

        Schema::table('chat_requests', function (Blueprint $table) {
            $table->dropIndex('chat_requests_receiver_status_index');
            $table->dropIndex('chat_requests_parties_index');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_participants_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_created_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_company_created_index');
            $table->dropIndex('posts_type_created_index');
            $table->dropIndex('posts_user_created_index');
        });

        Schema::table('saved_items', function (Blueprint $table) {
            $table->dropUnique('saved_items_unique');
            $table->dropIndex('saved_items_item_index');
        });

        Schema::table('connections', function (Blueprint $table) {
            $table->dropUnique('connections_user_target_type_unique');
            $table->dropUnique('connections_user_company_type_unique');
            $table->dropIndex('connections_company_type_index');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique('companies_user_id_unique');
            $table->dropIndex('companies_country_industry_index');
            $table->dropIndex('companies_verified_tier_index');
        });
    }
};
