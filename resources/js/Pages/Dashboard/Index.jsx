import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function DashboardIndex({ hasCompany, company, metrics }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout title="Business Dashboard">
            <div className="space-y-6">

                <div className="page-hero">
                    <h1 className="page-title">📈 {t('dashboard.title')}</h1>
                    <p className="page-subtitle">{t('dashboard.subtitle')}</p>
                </div>

                {hasCompany && metrics ? (
                    <div className="space-y-6">
                        {/* Metrics Grid */}
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div className="tradeon-card p-5 text-center">
                                <div className="text-3xl">👁️</div>
                                <div className="mt-1 text-2xl font-extrabold text-slate-900">{metrics.profile_views}</div>
                                <div className="text-xs text-slate-500">{t('dashboard.views')}</div>
                            </div>
                            <div className="tradeon-card p-5 text-center">
                                <div className="text-3xl">❤️</div>
                                <div className="mt-1 text-2xl font-extrabold text-sky-600">{metrics.company_followers}</div>
                                <div className="text-xs text-slate-500">{t('dashboard.followers')}</div>
                            </div>
                            <div className="tradeon-card p-5 text-center">
                                <div className="text-3xl">🤝</div>
                                <div className="mt-1 text-2xl font-extrabold text-slate-900">{metrics.connections_count}</div>
                                <div className="text-xs text-slate-500">{t('dashboard.connections')}</div>
                            </div>
                            <div className="tradeon-card p-5 text-center">
                                <div className="text-3xl">⚡</div>
                                <div className="mt-1 text-2xl font-extrabold text-emerald-600">{metrics.response_rate}%</div>
                                <div className="text-xs text-slate-500">{t('dashboard.response_rate')}</div>
                            </div>
                        </div>

                        {/* Profile completeness Bar */}
                        <div className="tradeon-card p-6">
                            <div className="mb-2 flex items-center justify-between text-xs font-bold text-slate-900">
                                <span>{t('dashboard.completeness')}</span>
                                <span className="text-sky-600">{metrics.completeness}% {t('dashboard.complete')}</span>
                            </div>
                            <div className="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                                <div className="h-full rounded-full bg-gradient-to-r from-sky-400 to-cyan-500 transition-all" style={{ width: `${metrics.completeness}%` }}></div>
                            </div>
                        </div>

                        <div className="tradeon-card flex flex-wrap items-center justify-between gap-3 p-5">
                            <div>
                                <div className="text-sm font-bold text-slate-900">🛡️ {t('dashboard.verify_title')}</div>
                                <div className="mt-0.5 text-xs text-slate-500">{t('dashboard.verify_desc')}</div>
                            </div>
                            <Link href="/verification" className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-bold text-white hover:bg-sky-600">{t('dashboard.verify_cta')}</Link>
                        </div>
                    </div>
                ) : (
                    <div className="tradeon-card mx-auto max-w-lg p-8 text-center">
                        <div className="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-50 text-4xl">🏢</div>
                        <h3 className="text-base font-bold text-slate-900">{t('dashboard.no_company_title')}</h3>
                        <p className="mb-5 mt-1 text-xs leading-5 text-slate-500">{t('dashboard.no_company_desc')}</p>
                        <Link href="/onboarding/company" className="rounded-xl bg-sky-500 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-sky-600">
                            + {t('dashboard.no_company_cta')}
                        </Link>
                    </div>
                )}

            </div>
        </TradeonLayout>
    );
}
