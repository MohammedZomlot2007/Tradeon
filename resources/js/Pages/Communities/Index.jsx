import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import EmptyState from '@/Components/Tradeon/EmptyState';
import { Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function CommunitiesIndex({ communities }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-6">

                <div className="page-hero">
                    <h1 className="page-title">👥 {t('communities.title')}</h1>
                    <p className="page-subtitle">{t('communities.subtitle')}</p>
                </div>

                {communities && communities.length > 0 ? (
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {communities.map((comm) => (
                            <div key={comm.id} className="tradeon-card flex flex-col justify-between p-5 transition hover:-translate-y-[1px]">
                                <div>
                                    <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-xl ring-1 ring-sky-100">
                                        👥
                                    </div>
                                    <h3 className="text-sm font-bold text-slate-900">{comm.name}</h3>
                                    <span className="text-[10px] font-bold uppercase text-sky-600">{comm.industry}</span>
                                    <p className="mt-2 line-clamp-3 text-xs leading-5 text-slate-500">{comm.description}</p>
                                </div>
                                <Link
                                    href={`/communities/${comm.slug}`}
                                    className="btn-secondary mt-4 w-full"
                                >
                                    {t('communities.enter')}
                                </Link>
                            </div>
                        ))}
                    </div>
                ) : (
                    <EmptyState icon="👥" title={t('communities.no_communities')} description={t('communities.no_communities_desc')} />
                )}

            </div>
        </TradeonLayout>
    );
}
