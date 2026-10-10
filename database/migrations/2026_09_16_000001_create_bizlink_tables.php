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
        // Companies Table
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->string('country');
            $table->string('city');
            $table->json('business_types'); // ['Manufacturer', 'Supplier', 'Wholesaler', etc.]
            $table->string('industry');
            $table->string('company_size')->nullable(); // 1-10, 11-50, 51-200, 201-500, 500+
            $table->integer('founded_year')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->json('products_summary')->nullable();
            $table->json('services_summary')->nullable();
            $table->json('markets_served')->nullable();
            $table->json('export_markets')->nullable();
            $table->json('import_markets')->nullable();
            $table->json('certifications')->nullable();
            $table->string('production_capacity')->nullable();
            $table->string('moq')->nullable(); // Minimum Order Quantity
            $table->string('business_hours')->nullable();
            $table->json('contact_options')->nullable();
            $table->json('team_members')->nullable();
            $table->string('verified_tier')->default('unverified'); // unverified, identity_verified, company_verified, factory_verified
            $table->integer('followers_count')->default(0);
            $table->integer('connections_count')->default(0);
            $table->integer('response_rate')->default(95); // %
            $table->string('response_time')->default('< 2 hours');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        // Factories Table
        Schema::create('factories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('factory_type')->nullable(); // OEM, ODM, Custom Manufacturing
            $table->string('location_address')->nullable();
            $table->integer('production_lines')->default(1);
            $table->string('annual_output')->nullable();
            $table->json('machinery')->nullable();
            $table->json('certifications')->nullable();
            $table->json('photos')->nullable();
            $table->json('videos')->nullable();
            $table->text('manufacturing_process')->nullable();
            $table->timestamps();
        });

        // Product Showcases Table (B2B Showcase, not store cart)
        Schema::create('product_showcases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->string('industry');
            $table->string('category')->nullable();
            $table->json('specifications')->nullable();
            $table->string('moq')->nullable();
            $table->json('available_markets')->nullable();
            $table->json('images')->nullable();
            $table->string('lead_time')->nullable();
            $table->timestamps();
        });

        // Posts Table (Business updates, product launches, RFQs, factory videos, etc.)
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('business_update');
            // business_update, product_launch, factory_update, manufacturing_process, company_news, business_opportunity, partnership_request, looking_for_supplier, looking_for_manufacturer, looking_for_distributor, looking_for_buyer, rfq_sourcing, trade_event, hiring
            $table->string('title')->nullable();
            $table->text('content');
            $table->json('media_urls')->nullable();
            $table->json('attachments')->nullable();
            $table->string('country')->nullable();
            $table->string('industry')->nullable();
            $table->json('tags')->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('reposts_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });

        // Post Interactions (Likes, Saved)
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'post_id']);
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();
        });

        // Business Requests Table
        Schema::create('business_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('request_type'); // supplier, manufacturer, distributor, buyer, partner
            $table->string('industry');
            $table->string('target_country')->nullable();
            $table->string('quantity')->nullable();
            $table->text('description');
            $table->string('budget_range')->nullable();
            $table->string('status')->default('open'); // open, fulfilled, closed
            $table->integer('responses_count')->default(0);
            $table->timestamps();
        });

        // RFQ System (Request for Quotation)
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->text('specifications');
            $table->string('quantity');
            $table->string('target_market')->nullable();
            $table->string('delivery_country');
            $table->date('required_date')->nullable();
            $table->text('additional_requirements')->nullable();
            $table->string('status')->default('active'); // active, closed, awarded
            $table->timestamps();
        });

        Schema::create('rfq_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->string('lead_time')->nullable();
            $table->text('quote_notes');
            $table->string('status')->default('submitted'); // submitted, accepted, rejected
            $table->timestamps();
        });

        // Social Connections & Follows
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('target_company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('type'); // connection, follow
            $table->string('status')->default('accepted'); // pending, accepted
            $table->timestamps();
        });

        // Chat & Messenger
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_two_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_one_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('company_two_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('body');
            $table->json('attachments')->nullable(); // PDF, Catalogue, images
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('chat_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('purpose'); // catalogue, partnership, sourcing, general
            $table->text('initial_message');
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->timestamps();
        });

        // Saved Collections (Companies, Products, Posts, Requests)
        Schema::create('saved_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_type'); // company, product, post, request
            $table->unsignedBigInteger('item_id');
            $table->string('collection_name')->default('Default'); // Potential Suppliers, Potential Manufacturers, etc.
            $table->timestamps();
        });

        // Business Events
        Schema::create('biz_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('event_type'); // trade_show, exhibition, webinar, meetup, conference
            $table->string('industry');
            $table->string('location');
            $table->string('country');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('website_link')->nullable();
            $table->text('description');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // Industry Communities
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('industry');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->string('cover')->nullable();
            $table->integer('members_count')->default(0);
            $table->timestamps();
        });

        // Company Verifications
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('verification_type'); // identity, company, factory
            $table->string('document_type');
            $table->string('document_url');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // Fraud Reports & Moderation
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->string('reportable_type'); // company, post, user, product
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, dismissed, actioned
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('verifications');
        Schema::dropIfExists('communities');
        Schema::dropIfExists('biz_events');
        Schema::dropIfExists('saved_items');
        Schema::dropIfExists('chat_requests');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('connections');
        Schema::dropIfExists('rfq_quotes');
        Schema::dropIfExists('rfqs');
        Schema::dropIfExists('business_requests');
        Schema::dropIfExists('post_comments');
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('product_showcases');
        Schema::dropIfExists('factories');
        Schema::dropIfExists('companies');
    }
};
