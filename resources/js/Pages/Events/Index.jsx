import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import EmptyState from '@/Components/Tradeon/EmptyState';

import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function EventsIndex({ events }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-6">

                <div className="page-hero">
                    <h1 className="page-title">📅 {t('events.title')}</h1>
                    <p className="page-subtitle">{t('events.subtitle')}</p>
                </div>

                {events && events.length > 0 ? (
                    <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                        {events.map((evt) => (
                            <div key={evt.id} className="tradeon-card flex flex-col justify-between overflow-hidden">
                                {evt.image ? (
                                    <img loading="lazy" decoding="async" src={evt.image} className="h-48 w-full object-cover" />
                                ) : (
                                    <div className="flex h-48 w-full items-center justify-center bg-gradient-to-r from-sky-50 to-cyan-50 text-5xl">📅</div>
                                )}
                                <div className="p-5">
                                    <span className="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[10px] font-bold uppercase text-sky-700">
                                        {evt.event_type}
                                    </span>
                                    <h3 className="mt-2 text-base font-bold text-slate-900">{evt.title}</h3>
                                    <p className="mt-1 text-xs text-slate-500">📍 {evt.location}, {evt.country}</p>
                                    <div className="mt-2 text-xs font-semibold text-sky-600">
                                        📆 {new Date(evt.start_date).toLocaleDateString()}
                                    </div>
                                    <p className="mt-3 line-clamp-3 text-xs leading-5 text-slate-600">{evt.description}</p>
                                </div>
                                <div className="p-5 pt-0">
                                    <a
                                        href={evt.website_link || '#'}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="btn-primary block w-full text-center"
                                    >
                                        {t('events.official_link')} ↗
                                    </a>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <EmptyState icon="📅" title={t('events.no_events')} description={t('events.no_events_desc')} />
                )}

            </div>
        </TradeonLayout>
    );
}
