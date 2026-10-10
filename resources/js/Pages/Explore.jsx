import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import CompanyCard from '@/Components/Tradeon/CompanyCard';
import EmptyState from '@/Components/Tradeon/EmptyState';
import { Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function Explore({ trendingCompanies, trendingProducts, recommendedManufacturers, popularBusinessRequests, popularPosts }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-10">

                {/* Header Pitch */}
                <div className="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-sky-600 p-8 text-center">
                    <div className="relative z-10 mx-auto max-w-2xl">
                        <span className="rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-widest text-white">
                            {t('explore.badge')}
                        </span>
                        <h1 className="mt-3 text-2xl font-extrabold text-white sm:text-4xl">
                            {t('explore.hero_title')}
                        </h1>
                        <p className="mt-2 text-xs text-sky-100 sm:text-sm">
                            {t('explore.hero_sub')}
                        </p>
                    </div>
                </div>

                {/* Trending Companies Section */}
                <div>
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="flex items-center space-x-2 text-lg font-extrabold text-slate-900">
                            <span>🔥</span>
                            <span>{t('explore.trending')}</span>
                        </h2>
                        <Link href="/businesses" className="text-xs font-semibold text-sky-600 hover:underline">{t('explore.view_directory')}</Link>
                    </div>

                    {trendingCompanies && trendingCompanies.length > 0 ? (
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {trendingCompanies.map((comp) => (
                                <CompanyCard key={comp.id} company={comp} />
                            ))}
                        </div>
                    ) : (
                        <EmptyState icon="🏢" title={t('explore.no_trending')} description={t('explore.no_trending_desc')} />
                    )}
                </div>

                {/* Recommended Manufacturers Section */}
                <div>
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="flex items-center space-x-2 text-lg font-extrabold text-slate-900">
                            <span>🏭</span>
                            <span>{t('explore.recommended')}</span>
                        </h2>
                        <Link href="/manufacturers" className="text-xs font-semibold text-sky-600 hover:underline">{t('explore.view_manufacturers')}</Link>
                    </div>

                    {recommendedManufacturers && recommendedManufacturers.length > 0 ? (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {recommendedManufacturers.map((mfg) => (
                                <div key={mfg.id} className="tradeon-card flex flex-col justify-between p-4">
                                    <div>
                                        {mfg.logo ? (
                                            <img loading="lazy" decoding="async" src={mfg.logo} className="mb-3 h-12 w-12 rounded-xl object-cover ring-1 ring-slate-200" />
                                        ) : (
                                            <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-2xl">🏭</div>
                                        )}
                                        <h3 className="text-sm font-bold text-slate-900">{mfg.name}</h3>
                                        <p className="text-xs font-semibold text-sky-600">{mfg.industry}</p>
                                        <p className="mt-1 text-xs text-slate-500">📍 {mfg.city}, {mfg.country}</p>
                                        {mfg.moq && <div className="mt-2 rounded-lg bg-slate-50 p-2 text-[11px] text-slate-600">{t('common.moq')}: <strong>{mfg.moq}</strong></div>}
                                    </div>
                                    <Link href={`/company/${mfg.slug}`} className="mt-4 block rounded-xl border border-sky-200 bg-sky-50 py-2 text-center text-xs font-bold text-sky-700 transition hover:bg-sky-100">
                                        {t('explore.view_factory')}
                                    </Link>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <EmptyState icon="🏭" title={t('explore.no_manufacturers')} description={t('explore.no_manufacturers_desc')} />
                    )}
                </div>

                {/* Trending Products Showcase */}
                <div>
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="flex items-center space-x-2 text-lg font-extrabold text-slate-900">
                            <span>📦</span>
                            <span>{t('explore.showcase')}</span>
                        </h2>
                    </div>

                    {trendingProducts && trendingProducts.length > 0 ? (
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {trendingProducts.map((prod) => (
                                <div key={prod.id} className="tradeon-card overflow-hidden">
                                    {prod.images?.[0] ? (
                                        <img loading="lazy" decoding="async" src={prod.images?.[0]} className="h-48 w-full object-cover" />
                                    ) : (
                                        <div className="flex h-48 w-full items-center justify-center bg-slate-100 text-4xl">📦</div>
                                    )}
                                    <div className="p-4">
                                        <span className="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-sky-700">{prod.category || prod.industry}</span>
                                        <h3 className="mt-2 text-sm font-bold text-slate-900">{prod.title}</h3>
                                        <p className="mt-1 line-clamp-2 text-xs text-slate-500">{prod.description}</p>
                                        <div className="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                                            <span className="text-slate-500">{t('common.moq')}: <strong>{prod.moq || '—'}</strong></span>
                                            <Link href={`/company/${prod.company?.slug}`} className="font-semibold text-sky-600 hover:underline">{t('explore.contact_supplier')}</Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <EmptyState icon="📦" title={t('explore.no_products')} description={t('explore.no_products_desc')} />
                    )}
                </div>

            </div>
        </TradeonLayout>
    );
}
