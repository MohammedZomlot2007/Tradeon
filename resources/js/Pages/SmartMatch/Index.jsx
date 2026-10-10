import React, { useState } from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import CompanyCard from '@/Components/Tradeon/CompanyCard';
import axios from 'axios';

import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function SmartMatchIndex() {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [needType, setNeedType] = useState('Manufacturer');
    const [industry, setIndustry] = useState('Fashion & Apparel');
    const [targetCountry, setTargetCountry] = useState('Turkey');
    const [prompt, setPrompt] = useState('');
    const [loading, setLoading] = useState(false);
    const [matches, setMatches] = useState(null);

    const handleMatch = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const res = await axios.post('/api/smart-match', {
                need_type: needType,
                industry: industry,
                target_country: targetCountry,
                prompt: prompt,
            });
            setMatches(res.data.matches);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    return (
        <TradeonLayout>
            <div className="mx-auto max-w-4xl space-y-8">

                {/* Header */}
                <div className="overflow-hidden rounded-2xl border border-slate-200/80 bg-sky-600 p-8 text-center">
                    <span className="text-2xl" aria-hidden="true">✨</span>
                    <h1 className="mt-2 text-2xl font-extrabold text-white sm:text-3xl">{t('smartmatch.title')}</h1>
                    <p className="mx-auto mt-2 max-w-xl text-xs leading-relaxed text-sky-100 sm:text-sm">
                        {t('smartmatch.subtitle')}
                    </p>
                </div>

                {/* Match Wizard Form */}
                <div className="tradeon-card p-6">
                    <form onSubmit={handleMatch} className="space-y-5">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label className="tradeon-label">{t('smartmatch.looking_for')}</label>
                                <select
                                    value={needType}
                                    onChange={(e) => setNeedType(e.target.value)}
                                    className="tradeon-input px-3 py-2.5 text-xs"
                                >
                                    <option value="Manufacturer">{t('smartmatch.need_manufacturer')}</option>
                                    <option value="Supplier">{t('smartmatch.need_supplier')}</option>
                                    <option value="Distributor">{t('smartmatch.need_distributor')}</option>
                                    <option value="Buyer">{t('smartmatch.need_buyer')}</option>
                                    <option value="Partner">{t('smartmatch.need_partner')}</option>
                                </select>
                            </div>
                            <div>
                                <label className="tradeon-label">{t('smartmatch.sector')}</label>
                                <select
                                    value={industry}
                                    onChange={(e) => setIndustry(e.target.value)}
                                    className="tradeon-input px-3 py-2.5 text-xs"
                                >
                                    <option value="Fashion & Apparel">{t('filters.industries.fashion')}</option>
                                    <option value="Footwear & Leather">{t('filters.industries.footwear')}</option>
                                    <option value="Electronics & Tech">{t('filters.industries.electronics')}</option>
                                    <option value="Food & Agriculture">{t('filters.industries.food')}</option>
                                </select>
                            </div>
                            <div>
                                <label className="tradeon-label">{t('smartmatch.target')}</label>
                                <select
                                    value={targetCountry}
                                    onChange={(e) => setTargetCountry(e.target.value)}
                                    className="tradeon-input px-3 py-2.5 text-xs"
                                >
                                    <option value="Palestine">{t('filters.countries.palestine')} 🇵🇸</option>
                                    <option value="Turkey">{t('filters.countries.turkey')} 🇹🇷</option>
                                    <option value="China">{t('filters.countries.china')} 🇨🇳</option>
                                    <option value="Germany">{t('filters.countries.germany')} 🇩🇪</option>
                                    <option value="United Arab Emirates">{t('filters.countries.uae')} 🇦🇪</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label className="tradeon-label">{t('smartmatch.prompt_label')}</label>
                            <input
                                type="text"
                                placeholder={t('smartmatch.prompt_ph')}
                                className="tradeon-input px-4 py-3 text-xs"
                                value={prompt}
                                onChange={(e) => setPrompt(e.target.value)}
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={loading}
                            className="btn-primary w-full !py-3.5 !text-sm"
                        >
                            {loading ? `🔍 ${t('smartmatch.analyzing')}` : `✨ ${t('smartmatch.find')}`}
                        </button>
                    </form>
                </div>

                {/* Results Section */}
                {matches && (
                    <div className="space-y-4">
                        <h2 className="flex items-center gap-2 text-lg font-extrabold text-slate-900">
                            <span aria-hidden="true">🎯</span>
                            <span>{t('smartmatch.results')} ({matches.length})</span>
                        </h2>

                        <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                            {matches.map((item) => (
                                <div key={item.company.id} className="relative">
                                    <div className="absolute top-3 right-3 z-10 rounded-full bg-sky-600 px-2.5 py-1 text-xs font-extrabold text-white shadow">
                                        {item.match_score}% {t('smartmatch.match')}
                                    </div>
                                    <CompanyCard company={item.company} />
                                </div>
                            ))}
                        </div>
                    </div>
                )}

            </div>
        </TradeonLayout>
    );
}
