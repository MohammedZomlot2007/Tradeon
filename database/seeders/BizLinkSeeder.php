<?php

namespace Database\Seeders;

use App\Models\BizEvent;
use App\Models\BusinessRequest;
use App\Models\Community;
use App\Models\Company;
use App\Models\Factory;
use App\Models\Post;
use App\Models\ProductShowcase;
use App\Models\Rfq;
use App\Models\User;
use Illuminate\Database\Seeder;

class BizLinkSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Users & Companies
        $user1 = User::create([
            'name' => 'Tariq Al-Natsheh',
            'email' => 'tariq@paltextile.com',
            'password' => bcrypt('password'),
        ]);

        $company1 = Company::create([
            'user_id' => $user1->id,
            'name' => 'Palestine Textile & Garment Corp',
            'slug' => 'palestine-textile-garment-corp',
            'logo' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=200&auto=format&fit=crop',
            'cover' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200&auto=format&fit=crop',
            'country' => 'Palestine',
            'city' => 'Hebron',
            'business_types' => ['Manufacturer', 'Wholesaler'],
            'industry' => 'Fashion & Apparel',
            'company_size' => '51-200',
            'founded_year' => 2008,
            'website' => 'https://paltextile.example.com',
            'description' => 'Leading Palestinian manufacturer of high-quality denim, traditional embroidery garments, and workplace uniforms exporting to MENA and Europe.',
            'products_summary' => ['Denim Apparel', 'Custom Uniforms', 'Embroidered Textiles'],
            'services_summary' => ['OEM Manufacturing', 'Private Label Production'],
            'markets_served' => ['Middle East', 'Europe'],
            'export_markets' => ['Jordan', 'UAE', 'Turkey', 'Germany'],
            'import_markets' => ['Egypt', 'India'],
            'certifications' => ['ISO 9001', 'OEKO-TEX Standard 100', 'GOTS Certified'],
            'production_capacity' => '50,000 units / month',
            'moq' => '500 units',
            'verified_tier' => 'factory_verified',
            'followers_count' => 1240,
            'connections_count' => 380,
            'response_rate' => 98,
            'response_time' => '< 1 hour',
            'is_featured' => true,
        ]);

        Factory::create([
            'company_id' => $company1->id,
            'name' => 'Al-Natsheh Main Weaving & Sewing Plant',
            'factory_type' => 'OEM & Private Label',
            'location_address' => 'Industrial Zone, Hebron, Palestine',
            'production_lines' => 8,
            'annual_output' => '600,000 Garments',
            'machinery' => ['Automatic Cutting Systems', 'Juki High-Speed Sewing Lines', 'Embroidery Multi-Head Units'],
            'certifications' => ['ISO 9001:2015', 'SEDEX Audited'],
            'photos' => [
                'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800&auto=format&fit=crop',
            ],
            'manufacturing_process' => 'Yarn inspection -> Fabric cutting -> Automated stitching -> Quality control inspection -> Eco-friendly pressing & packaging.',
        ]);

        ProductShowcase::create([
            'company_id' => $company1->id,
            'title' => 'Premium Heavy Duty Denim Jeans (Raw & Washed)',
            'slug' => 'premium-heavy-duty-denim-jeans',
            'description' => 'Organic 14oz raw denim pants featuring reinforced stitching and custom metal hardware. Ideal for commercial fashion brands.',
            'industry' => 'Fashion & Apparel',
            'category' => 'Men Fashion',
            'specifications' => ['Fabric Weight' => '14 oz', 'Material' => '100% Cotton', 'Fit' => 'Slim & Straight'],
            'moq' => '300 pcs',
            'available_markets' => ['Europe', 'North America', 'Middle East'],
            'images' => ['https://images.unsplash.com/photo-1542272604-780c36856d66?w=800&auto=format&fit=crop'],
            'lead_time' => '25 Days',
        ]);

        // User 2: Istanbul Footwear Factory (Turkey)
        $user2 = User::create([
            'name' => 'Emre Yilmaz',
            'email' => 'emre@istanbulshoe.com',
            'password' => bcrypt('password'),
        ]);

        $company2 = Company::create([
            'user_id' => $user2->id,
            'name' => 'Anatolia Footwear & Leather Works',
            'slug' => 'anatolia-footwear-leather-works',
            'logo' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=200&auto=format&fit=crop',
            'cover' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=1200&auto=format&fit=crop',
            'country' => 'Turkey',
            'city' => 'Istanbul',
            'business_types' => ['Manufacturer', 'Brand', 'Exporter'],
            'industry' => 'Footwear & Leather',
            'company_size' => '201-500',
            'founded_year' => 1999,
            'website' => 'https://anatoliashoe.example.com',
            'description' => 'Turkish footwear manufacturer specializing in genuine leather boots, sneakers, and orthomorphic formal shoes.',
            'products_summary' => ['Leather Boots', 'Casual Sneakers', 'Dress Shoes'],
            'services_summary' => ['OEM Shoe Molding', 'Sole Prototyping'],
            'markets_served' => ['Global'],
            'export_markets' => ['USA', 'Germany', 'Palestine', 'Saudi Arabia', 'UK'],
            'certifications' => ['ISO 14001', 'LWG Gold Rated Leather'],
            'production_capacity' => '80,000 pairs / month',
            'moq' => '200 pairs',
            'verified_tier' => 'factory_verified',
            'followers_count' => 3400,
            'connections_count' => 890,
            'response_rate' => 96,
            'response_time' => '< 2 hours',
            'is_featured' => true,
        ]);

        // User 3: Shenzhen Tech & Electronics (China)
        $user3 = User::create([
            'name' => 'Chen Wei',
            'email' => 'chen@shenzhentech.cn',
            'password' => bcrypt('password'),
        ]);

        $company3 = Company::create([
            'user_id' => $user3->id,
            'name' => 'Shenzhen HyperLink Electronics Co.',
            'slug' => 'shenzhen-hyperlink-electronics',
            'logo' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=200&auto=format&fit=crop',
            'cover' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&auto=format&fit=crop',
            'country' => 'China',
            'city' => 'Shenzhen',
            'business_types' => ['Manufacturer', 'Supplier'],
            'industry' => 'Electronics & Tech',
            'company_size' => '500+',
            'founded_year' => 2012,
            'website' => 'https://hyperlinktech.example.com',
            'description' => 'High-tech manufacturer of smart wireless chargers, IoT sensors, high-capacity power banks, and USB-C multiport hubs.',
            'products_summary' => ['GaN Chargers', 'USB-C Hubs', 'Wireless Earbuds'],
            'markets_served' => ['North America', 'Europe', 'Asia Pacific', 'Latin America'],
            'export_markets' => ['USA', 'UAE', 'Germany', 'Brazil', 'Japan'],
            'certifications' => ['CE', 'FCC', 'RoHS', 'UL Listed'],
            'production_capacity' => '300,000 units / month',
            'moq' => '500 units',
            'verified_tier' => 'company_verified',
            'followers_count' => 5200,
            'connections_count' => 1400,
            'response_rate' => 99,
            'response_time' => '< 30 mins',
            'is_featured' => true,
        ]);

        // 2. Business Communities
        $communities = [
            ['name' => 'Fashion & Textiles Network', 'slug' => 'fashion-textiles', 'industry' => 'Fashion & Apparel', 'description' => 'Global community of apparel manufacturers, fabric suppliers, retailers, and fashion brand founders.'],
            ['name' => 'Electronics & Hardware Manufacturers', 'slug' => 'electronics-hardware', 'industry' => 'Electronics & Tech', 'description' => 'Connecting PCB fabricators, OEM tech factories, chip distributors, and hardware buyers worldwide.'],
            ['name' => 'Footwear & Leather Goods B2B', 'slug' => 'footwear-leather', 'industry' => 'Footwear & Leather', 'description' => 'Specialized forum for shoe manufacturers, tanneries, and bulk footwear importers.'],
            ['name' => 'Food & Agriculture Global Sourcing', 'slug' => 'food-agriculture', 'industry' => 'Food & Agriculture', 'description' => 'B2B hub for olive oil pressers, grain suppliers, processed food packagers, and cold-chain distributors.'],
        ];

        foreach ($communities as $comm) {
            Community::create($comm);
        }

        // 3. Create Posts
        Post::create([
            'company_id' => $company1->id,
            'user_id' => $user1->id,
            'type' => 'factory_update',
            'title' => 'New Automated Stitching Line Installed in Hebron Factory',
            'content' => 'We are excited to share that Palestine Textile Corp has officially added 2 new automated Juki stitching lines to increase denim manufacturing capacity by 20,000 units/month!',
            'country' => 'Palestine',
            'industry' => 'Fashion & Apparel',
            'media_urls' => ['https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&auto=format&fit=crop'],
            'likes_count' => 45,
            'comments_count' => 12,
        ]);

        Post::create([
            'company_id' => $company2->id,
            'user_id' => $user2->id,
            'type' => 'product_launch',
            'title' => 'Unveiling 2026 Genuine Italian Leather Boot Collection for Importers',
            'content' => 'Anatolia Footwear is launching our autumn/winter genuine leather boot lineup. Available for private labeling with low MOQs starting at 200 pairs per colorway.',
            'country' => 'Turkey',
            'industry' => 'Footwear & Leather',
            'media_urls' => ['https://images.unsplash.com/photo-1549298916-b41d501d3772?w=800&auto=format&fit=crop'],
            'likes_count' => 88,
            'comments_count' => 19,
        ]);

        Post::create([
            'company_id' => null,
            'user_id' => $user1->id,
            'type' => 'looking_for_supplier',
            'title' => 'RFQ: Seeking Organic Cotton Yarn Supplier (20 Metric Tons)',
            'content' => 'We are sourcing 20 metric tons of OEKO-TEX certified combed organic cotton yarn (30s/1 & 40s/1 count) for Q4 production delivery to Ashdod / Haifa ports.',
            'country' => 'Palestine',
            'industry' => 'Fashion & Apparel',
            'likes_count' => 31,
            'comments_count' => 8,
        ]);

        // 4. Create Business Requests
        BusinessRequest::create([
            'user_id' => $user1->id,
            'company_id' => $company1->id,
            'title' => 'Looking for Footwear Distributor in the UAE & Gulf Region',
            'request_type' => 'distributor',
            'industry' => 'Footwear & Leather',
            'target_country' => 'United Arab Emirates',
            'quantity' => '10,000 pairs / quarter',
            'description' => 'Seeking established regional distributors with retail channel access in Dubai and Abu Dhabi to represent our genuine leather shoes.',
            'budget_range' => '$50,000 - $200,000',
            'status' => 'open',
            'responses_count' => 6,
        ]);

        // 5. Create RFQs
        Rfq::create([
            'user_id' => $user1->id,
            'company_id' => $company1->id,
            'product_name' => 'Custom Printed Matte Black Packaging Boxes with Foil Logo',
            'specifications' => '350gsm rigid cardboard boxes, matte lamination, gold foil embossing, magnetic closure. Size: 30cm x 20cm x 10cm.',
            'quantity' => '5,000 units',
            'target_market' => 'Middle East',
            'delivery_country' => 'Palestine',
            'required_date' => now()->addDays(30),
            'additional_requirements' => 'Samples required prior to mass production run.',
            'status' => 'active',
        ]);

        // 6. Create Events
        BizEvent::create([
            'company_id' => $company2->id,
            'title' => 'Istanbul International Leather & Footwear Fair 2026',
            'event_type' => 'trade_show',
            'industry' => 'Footwear & Leather',
            'location' => 'Tuyap Exhibition Center',
            'country' => 'Turkey',
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(18),
            'website_link' => 'https://istanbul-leather-fair.example.com',
            'description' => 'The largest B2B leather and footwear trade exhibition in Eurasia, hosting 600+ manufacturers and 20,000 international buyers.',
            'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&auto=format&fit=crop',
        ]);
    }
}
