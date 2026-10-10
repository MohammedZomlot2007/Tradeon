import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';
import { businessTypeLabel, industryLabel } from '@/taxonomy';
import VerifiedBadge from './VerifiedBadge';

export default function CompanyCard({ company, isFollowing = company.viewer_following ?? false, isConnected = company.viewer_connected ?? false, isSaved = company.viewer_saved ?? false }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const postAction = (url) => router.post(url, {}, { preserveScroll: true });
    const businessTypes = Array.isArray(company.business_types) ? company.business_types : [];

    return (
        <div className="tradeon-card group flex flex-col justify-between overflow-hidden transition hover:-translate-y-[1px]">
            <div>
                {/* Cover & Logo */}
                <div className="relative h-24 overflow-hidden bg-gradient-to-r from-sky-100 to-cyan-50">
                    {company.cover ? (
                        <img loading="lazy" decoding="async"
                            src={company.cover}
                            alt={company.name}
                            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                    ) : (
                        <div className="flex h-full w-full items-center justify-center text-3xl">🏢</div>
                    )}
                    <div className="absolute -bottom-5 left-4 rtl:right-4 rtl:left-auto">
                        {company.logo ? (
                            <img loading="lazy" decoding="async"
                                src={company.logo}
                                alt={company.name}
                                className="h-14 w-14 rounded-xl border-2 border-white bg-white object-cover shadow-md"
                            />
                        ) : (
                            <div className="flex h-14 w-14 items-center justify-center rounded-xl border-2 border-white bg-sky-500 text-xl font-bold text-white shadow-md">
                                {company.name?.charAt(0)}
                            </div>
                        )}
                    </div>
                </div>

                {/* Info Content */}
                <div className="px-4 pt-7 pb-3">
                    <div className="flex items-start justify-between gap-2">
                        <div className="min-w-0">
                            <Link href={`/company/${company.slug}`} className="line-clamp-1 text-sm font-bold text-slate-900 transition hover:text-sky-600">
                                {company.name}
                            </Link>
                            <div className="mt-0.5 text-xs text-slate-500">
                                <span>📍 {company.city}, {company.country}</span>
                            </div>
                        </div>
                        <VerifiedBadge tier={company.verified_tier} />
                    </div>

                    {/* Business Types Tags */}
                    <div className="mt-3 flex flex-wrap gap-1.5">
                        <span className="rounded-md border border-sky-200 bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                            {industryLabel(company.industry, locale)}
                        </span>
                        {businessTypes.map((type) => (
                            <span key={type} className="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                {businessTypeLabel(type, locale)}
                            </span>
                        ))}
                    </div>

                    {/* Description snippet */}
                    <p className="mt-2.5 line-clamp-2 text-xs leading-relaxed text-slate-500">
                        {company.description}
                    </p>
                </div>
            </div>

            {/* Footer Stats & Action Buttons */}
            <div className="border-t border-slate-100 bg-slate-50/70 px-4 py-3">
                <div className="flex items-center justify-between gap-2">
                    <div className="text-[11px] text-slate-500">
                        <span className="font-bold text-slate-900">{company.followers_count || 0}</span> {t('company.followers')}
                    </div>
                    <div className="flex items-center gap-2">
                        <Link
                            href={`/company/${company.slug}`}
                            className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-sky-200 hover:text-sky-600"
                        >
                            {t('company.view')}
                        </Link>
                        <button type="button" onClick={() => postAction(`/company/${company.id}/follow`)} className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition ${isFollowing ? 'border border-slate-200 bg-white text-slate-500' : 'bg-sky-500 text-white hover:bg-sky-600'}`}>
                            {isFollowing ? `✓ ${t('company.following')}` : `+ ${t('company.follow')}`}
                        </button>
                    </div>
                </div>
                <div className="mt-2 flex items-center gap-2">
                    <button type="button" onClick={() => postAction(`/company/${company.id}/connect`)} className="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:text-sky-600">
                        {isConnected ? `✓ ${t('company.connected')}` : t('company.connect')}
                    </button>
                    <button type="button" onClick={() => postAction(`/company/${company.id}/save`)} className={`rounded-lg border px-3 py-1.5 text-xs font-semibold transition ${isSaved ? 'border-amber-200 bg-amber-50 text-amber-600' : 'border-slate-200 bg-white text-slate-400 hover:text-amber-500'}`} aria-label={isSaved ? t('company.unsave_business') : t('company.save_business')}>
                        {isSaved ? '★' : '☆'}
                    </button>
                    <Link
                        href={`/messages?recipient=${company.user_id}`}
                        className="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700"
                    >
                        💬 {t('company.contact')}
                    </Link>
                </div>
            </div>
        </div>
    );
}
