import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';

import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function IntelligenceIndex({ countryStats, industryStats }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-6">

                <div className="page-hero">
                    <h1 className="page-title">📊 {t('intelligence.title')}</h1>
                    <p className="page-subtitle">{t('intelligence.subtitle')}</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Active Countries */}
                    <div className="tradeon-card p-5">
                        <h3 className="font-bold text-slate-900 text-sm mb-4">{t('intelligence.hubs')}</h3>
                        <div className="space-y-3">
                            {countryStats && countryStats.map((st) => (
                                <div key={st.country} className="flex items-center justify-between text-xs bg-slate-50 border border-slate-100 p-3 rounded-xl">
                                    <span className="font-semibold text-slate-800">📍 {st.country}</span>
                                    <span className="text-sky-600 font-bold">{st.total} {t('intelligence.businesses')}</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Active Industries */}
                    <div className="tradeon-card p-5">
                        <h3 className="font-bold text-slate-900 text-sm mb-4">{t('intelligence.sectors')}</h3>
                        <div className="space-y-3">
                            {industryStats && industryStats.map((ind) => (
                                <div key={ind.industry} className="flex items-center justify-between text-xs bg-slate-50 border border-slate-100 p-3 rounded-xl">
                                    <span className="font-semibold text-slate-800">⚙️ {ind.industry}</span>
                                    <span className="text-sky-600 font-bold">{ind.total} {t('intelligence.companies')}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

            </div>
        </TradeonLayout>
    );
}
