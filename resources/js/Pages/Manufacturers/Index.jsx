import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import EmptyState from '@/Components/Tradeon/EmptyState';
import VerifiedBadge from '@/Components/Tradeon/VerifiedBadge';
import { Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function ManufacturersIndex({ manufacturers }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-6">

                {/* Header Banner */}
                <div className="page-hero">
                    <h1 className="page-title">🏭 {t('directories.manufacturers_title')}</h1>
                    <p className="page-subtitle">{t('directories.manufacturers_sub')}</p>
                </div>

                {/* Manufacturers Grid */}
                {manufacturers && manufacturers.data && manufacturers.data.length > 0 ? (
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {manufacturers.data.map((mfg) => (
                            <div key={mfg.id} className="tradeon-card flex flex-col justify-between p-5 transition hover:-translate-y-[1px]">
                                <div>
                                    <div className="mb-3 flex items-start justify-between">
                                        {mfg.logo ? (
                                            <img loading="lazy" decoding="async" src={mfg.logo} className="h-12 w-12 rounded-xl object-cover ring-1 ring-slate-200" />
                                        ) : (
                                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-2xl">🏭</div>
                                        )}
                                        <VerifiedBadge tier={mfg.verified_tier} />
                                    </div>

                                    <h3 className="text-sm font-bold text-slate-900">{mfg.name}</h3>
                                    <p className="mt-0.5 text-xs font-semibold text-sky-600">{mfg.industry}</p>
                                    <p className="mt-1 text-xs text-slate-500">📍 {mfg.city}, {mfg.country}</p>

                                    {/* Factory Capabilities Box */}
                                    <div className="mt-4 space-y-1 rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs">
                                        <div className="flex justify-between text-slate-500">
                                            <span>{t('directories.monthly_output')}</span> <strong className="text-slate-800">{mfg.production_capacity || t('directories.na')}</strong>
                                        </div>
                                        <div className="flex justify-between text-slate-500">
                                            <span>{t('directories.moq_label')}</span> <strong className="text-sky-600">{mfg.moq || t('common.contact_factory')}</strong>
                                        </div>
                                        {mfg.factory?.production_lines && (
                                            <div className="flex justify-between text-slate-500">
                                                <span>{t('directories.active_lines')}</span> <strong className="text-slate-800">{mfg.factory.production_lines} {t('directories.lines_suffix')}</strong>
                                            </div>
                                        )}
                                    </div>

                                    {/* Certifications pills */}
                                    {mfg.certifications && (
                                        <div className="mt-3 flex flex-wrap gap-1">
                                            {mfg.certifications.map((cert) => (
                                                <span key={cert} className="rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                                    ✓ {cert}
                                                </span>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                <div className="mt-5 flex gap-2 border-t border-slate-100 pt-3">
                                    <Link href={`/company/${mfg.slug}`} className="flex-1 rounded-xl border border-slate-200 bg-white py-2 text-center text-xs font-bold text-slate-700 transition hover:border-sky-200 hover:text-sky-600">
                                        {t('directories.view_factory_profile')}
                                    </Link>
                                    <Link href={`/messages?recipient=${mfg.user_id}`} className="rounded-xl bg-sky-500 px-3 py-2 text-xs font-bold text-white transition hover:bg-sky-600">
                                        💬 {t('directories.inquire')}
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <EmptyState icon="🏭" title={t('directories.no_manufacturers')} description={t('directories.no_manufacturers_desc')} />
                )}

            </div>
        </TradeonLayout>
    );
}
