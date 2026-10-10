import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import CompanyCard from '@/Components/Tradeon/CompanyCard';
import EmptyState from '@/Components/Tradeon/EmptyState';
import { router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function BusinessesIndex({ companies, filters }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const handleSearch = (e) => {
        e.preventDefault();
        const form = e.target;
        router.get('/businesses', {
            q: form.q.value,
            country: form.country.value,
            industry: form.industry.value,
        }, { preserveState: true });
    };

    return (
        <TradeonLayout>
            <div className="space-y-6">

                {/* Search & Filters Bar */}
                <div className="page-hero">
                    <h1 className="page-title">🏢 {t('directories.businesses_title')}</h1>
                    <p className="page-subtitle">{t('directories.businesses_sub')}</p>

                    <form onSubmit={handleSearch} className="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                        <input
                            type="text"
                            name="q"
                            placeholder={t('directories.search_ph')}
                            aria-label={t('directories.search_ph')}
                            defaultValue={filters.search}
                            className="tradeon-input px-4 py-2.5 text-xs"
                        />
                        <select name="country" defaultValue={filters.country || ''} aria-label={t('search.country')} className="tradeon-input px-3 py-2.5 text-xs">
                            <option value="">{t('directories.all_countries')}</option>
                            <option value="Palestine">{t('filters.countries.palestine')} 🇵🇸</option>
                            <option value="Turkey">{t('filters.countries.turkey')} 🇹🇷</option>
                            <option value="China">{t('filters.countries.china')} 🇨🇳</option>
                            <option value="Germany">{t('filters.countries.germany')} 🇩🇪</option>
                            <option value="United Arab Emirates">{t('filters.countries.uae')} 🇦🇪</option>
                        </select>
                        <select name="industry" defaultValue={filters.industry || ''} aria-label={t('search.industry')} className="tradeon-input px-3 py-2.5 text-xs">
                            <option value="">{t('directories.all_industries')}</option>
                            <option value="Fashion & Apparel">{t('filters.industries.fashion')}</option>
                            <option value="Footwear & Leather">{t('filters.industries.footwear')}</option>
                            <option value="Electronics & Tech">{t('filters.industries.electronics')}</option>
                            <option value="Food & Agriculture">{t('filters.industries.food')}</option>
                        </select>
                        <button type="submit" className="btn-primary">
                            🔍 {t('directories.filter')}
                        </button>
                    </form>
                </div>

                {/* Listing Grid */}
                {companies && companies.data && companies.data.length > 0 ? (
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {companies.data.map((comp) => (
                            <CompanyCard key={comp.id} company={comp} />
                        ))}
                    </div>
                ) : (
                    <EmptyState
                        icon="🏢"
                        title={t('directories.no_match')}
                        description={t('directories.no_match_desc')}
                    />
                )}

            </div>
        </TradeonLayout>
    );
}
