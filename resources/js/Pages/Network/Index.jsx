import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import EmptyState from '@/Components/Tradeon/EmptyState';
import CompanyCard from '@/Components/Tradeon/CompanyCard';
import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function NetworkIndex({ connections, following, savedItems }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-6">

                <div className="page-hero">
                    <h1 className="page-title">📇 {t('network.title')}</h1>
                    <p className="page-subtitle">{t('network.subtitle')}</p>
                </div>

                <div className="space-y-4">
                    <h2 className="text-base font-extrabold text-slate-900">{t('network.followed')} ({following?.length || 0})</h2>
                    {following && following.length > 0 ? (
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {following.map((f) => (
                                f.target_company && <CompanyCard key={f.id} company={f.target_company} />
                            ))}
                        </div>
                    ) : (
                        <EmptyState icon="📇" title={t('network.no_followed')} description={t('network.no_followed_desc')} />
                    )}
                </div>

                <div className="space-y-4">
                    <h2 className="text-base font-extrabold text-slate-900">🔖 {t('network.saved')} ({savedItems?.length || 0})</h2>
                    {savedItems && savedItems.length > 0 ? (
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {savedItems.map((item) => item.company && <CompanyCard key={item.id} company={item.company} isSaved />)}
                        </div>
                    ) : (
                        <EmptyState icon="🔖" title={t('network.no_saved')} description={t('network.no_saved_desc')} />
                    )}
                </div>

            </div>
        </TradeonLayout>
    );
}
