import React, { useState } from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import { Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function WorldMap({ companies }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [selectedCountry, setSelectedCountry] = useState('All');

    const countryOptions = [
        { value: 'All', label: t('common.all') },
        { value: 'Palestine', label: t('filters.countries.palestine') },
        { value: 'Turkey', label: t('filters.countries.turkey') },
        { value: 'China', label: t('filters.countries.china') },
        { value: 'Germany', label: t('filters.countries.germany') },
        { value: 'United Arab Emirates', label: t('filters.countries.uae') },
        { value: 'USA', label: t('filters.countries.usa') },
    ];

    const filteredCompanies = selectedCountry === 'All'
        ? companies
        : companies.filter(c => c.country.toLowerCase() === selectedCountry.toLowerCase());

    return (
        <TradeonLayout>
            <div className="space-y-6">

                {/* Header */}
                <div className="page-hero flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="page-title">🌍 {t('worldmap.title')}</h1>
                        <p className="page-subtitle">{t('worldmap.subtitle')}</p>
                    </div>

                    {/* Country Selector Pills */}
                    <div className="flex flex-wrap gap-2">
                        {countryOptions.map((cty) => (
                            <button
                                key={cty.value}
                                onClick={() => setSelectedCountry(cty.value)}
                                className={`rounded-xl border px-3 py-1.5 text-xs font-semibold transition ${selectedCountry === cty.value ? 'border-sky-500 bg-sky-500 font-bold text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'}`}
                            >
                                {cty.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Map Visualizer Box */}
                <div className="tradeon-card relative flex h-96 items-center justify-center overflow-hidden bg-gradient-to-br from-sky-50 via-white to-cyan-50 p-6">
                    <div className="absolute inset-0 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] opacity-10 [background-size:16px_16px]"></div>

                    <div className="relative z-10 max-w-lg text-center">
                        <div className="mb-3 text-5xl">🗺️</div>
                        <h3 className="text-lg font-extrabold text-slate-900">{t('worldmap.map_title')}</h3>
                        <p className="mt-1 text-xs text-slate-500">
                            {t('worldmap.showing')} <strong className="text-sky-600">{filteredCompanies.length}</strong> {t('worldmap.nodes')} {selectedCountry === 'All' ? t('worldmap.global') : selectedCountry}.
                        </p>
                    </div>
                </div>

                {/* Regional Companies Grid */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {filteredCompanies.map((comp) => (
                        <div key={comp.id} className="tradeon-card flex items-center justify-between p-4 transition hover:-translate-y-[1px]">
                            <div className="min-w-0">
                                <h4 className="truncate text-sm font-bold text-slate-900">{comp.name}</h4>
                                <div className="mt-0.5 text-xs text-slate-500">📍 {comp.city}, {comp.country}</div>
                                <div className="mt-1 text-[10px] font-semibold text-sky-600">{comp.industry}</div>
                            </div>
                            <Link href={`/company/${comp.slug}`} className="btn-secondary shrink-0 !py-1.5">
                                {t('worldmap.view')}
                            </Link>
                        </div>
                    ))}
                </div>

            </div>
        </TradeonLayout>
    );
}
