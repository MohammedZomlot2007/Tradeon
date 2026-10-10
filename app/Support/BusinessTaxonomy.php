<?php

namespace App\Support;

/**
 * Single source of truth for the business taxonomy used during
 * registration and company onboarding. Server-side validation must
 * always reference these lists so session state cannot be manipulated
 * into storing arbitrary values.
 */
class BusinessTaxonomy
{
    public const BUSINESS_TYPES = [
        'Business Owner',
        'Manufacturer',
        'Supplier',
        'Wholesaler',
        'Distributor',
        'Importer',
        'Retailer',
        'Brand',
        'Buyer',
        'Service Provider',
        'Entrepreneur',
        'Business Professional',
    ];

    public const INDUSTRIES = [
        'Technology',
        'Manufacturing',
        'Textiles & Apparel',
        'Fashion & Apparel',
        'Footwear & Leather',
        'Food & Beverage',
        'Food & Agriculture',
        'Construction',
        'Furniture',
        'Automotive',
        'Electronics',
        'Electronics & Tech',
        'Agriculture',
        'Beauty & Personal Care',
        'Pharmaceuticals',
        'Logistics & Shipping',
        'Wholesale & Trading',
        'Professional Services',
        'Other',
    ];

    public const COMPANY_SIZES = [
        '1-10',
        '11-50',
        '51-200',
        '201-500',
        '500+',
    ];

    /**
     * @return array<int, string>
     */
    public static function businessTypes(): array
    {
        return self::BUSINESS_TYPES;
    }

    /**
     * @return array<int, string>
     */
    public static function industries(): array
    {
        return self::INDUSTRIES;
    }
}
