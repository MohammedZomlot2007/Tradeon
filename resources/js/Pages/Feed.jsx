import React, { useState } from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import PostCard from '@/Components/Tradeon/PostCard';
import CreatePostModal from '@/Components/Tradeon/CreatePostModal';
import EmptyState from '@/Components/Tradeon/EmptyState';
import { Link, router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function Feed({ posts, filters, userCompany, recommendedManufacturers }) {
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);

    const handleFilterChange = (filterType) => {
        router.get('/feed', { filter: filterType }, { preserveState: true });
    };

    return (
        <TradeonLayout title="Tradeon Feed">
            <div className="mx-auto max-w-7xl px-1">
                <div className="mb-5">
                    <div className="tradeon-card overflow-hidden bg-gradient-to-r from-white via-sky-50 to-cyan-50 p-5 sm:p-6">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-[0.18em] text-sky-600">{t('feed.title')}</p>
                                <h1 className="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{t('feed.headline')}</h1>
                                <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{t('feed.subheadline')}</p>
                            </div>
                            <button onClick={() => setIsCreateModalOpen(true)} className="inline-flex items-center justify-center rounded-xl bg-sky-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-sky-500/20 transition hover:bg-sky-600">+ {t('feed.create_post')}</button>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-[240px_minmax(0,1fr)_280px]">
                    <aside className="hidden lg:block">
                        <div className="tradeon-card sticky top-24 p-4">
                            {userCompany ? (
                                <>
                                    <div className="flex items-center gap-3">
                                        {userCompany.logo ? <img loading="lazy" decoding="async" src={userCompany.logo} alt={userCompany.name} className="h-12 w-12 rounded-xl object-cover ring-1 ring-slate-200" /> : <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-xl text-sky-600">🏢</div>}
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-bold text-slate-900">{userCompany.name}</p>
                                            <p className="truncate text-xs text-sky-600">{userCompany.industry || t('feed.fallback_industry')}</p>
                                        </div>
                                    </div>
                                    <div className="mt-4 grid grid-cols-2 gap-2 text-center">
                                        <div className="tradeon-soft rounded-xl p-3"><div className="text-base font-bold text-slate-900">{userCompany.followers_count || 0}</div><div className="text-[10px] text-slate-500">{t('feed.followers')}</div></div>
                                        <div className="tradeon-soft rounded-xl p-3"><div className="text-base font-bold text-slate-900">{userCompany.connections_count || 0}</div><div className="text-[10px] text-slate-500">{t('feed.connections')}</div></div>
                                    </div>
                                    <Link href={`/company/${userCompany.slug}`} className="mt-4 block rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center text-xs font-semibold text-slate-700 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700">{t('feed.view_company')}</Link>
                                </>
                            ) : (
                                <div className="text-center">
                                    <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-xl">🏢</div>
                                    <h3 className="mt-3 text-sm font-bold text-slate-900">{t('feed.no_company_title')}</h3>
                                    <p className="mt-1 text-xs leading-5 text-slate-500">{t('feed.no_company_desc')}</p>
                                    <Link href="/biz-dashboard" className="mt-4 block rounded-xl bg-sky-500 px-3 py-2 text-xs font-bold text-white hover:bg-sky-600">{t('feed.setup_business')}</Link>
                                </div>
                            )}
                            <div className="mt-5 border-t border-slate-200 pt-4">
                                <p className="mb-2 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{t('feed.quick_access')}</p>
                                <div className="space-y-1">
                                    <Link href="/manufacturers" className="block rounded-lg px-2 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-sky-600">🏭 {t('feed.verified_manufacturers')}</Link>
                                    <Link href="/requests" className="block rounded-lg px-2 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-sky-600">📋 {t('feed.filter_rfq')}</Link>
                                    <Link href="/smart-match" className="block rounded-lg px-2 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-sky-600">✨ {t('feed.action_smart_match')}</Link>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main className="min-w-0">
                        <div className="mb-4 tradeon-card p-2">
                            <div className="grid grid-cols-2 gap-1 sm:grid-cols-4">
                                {[['all', t('feed.filter_all')], ['following', t('feed.filter_following')], ['companies', t('feed.filter_companies')], ['rfq', t('feed.filter_rfq')]].map(([key,label]) => (
                                    <button key={key} onClick={() => handleFilterChange(key)} className={`rounded-xl px-3 py-2.5 text-xs font-bold transition ${filters.currentFilter === key ? 'bg-sky-500 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'}`}>{label}</button>
                                ))}
                            </div>
                        </div>

                        <div className="tradeon-card mb-4 p-3 sm:p-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-cyan-500 font-bold text-white">{userCompany?.name?.charAt(0) || 'T'}</div>
                                <button onClick={() => setIsCreateModalOpen(true)} className="flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm text-slate-400 transition hover:border-sky-200 hover:bg-white">{t('feed.share_placeholder')}</button>
                            </div>
                            <div className="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3 text-xs">
                                <button onClick={() => setIsCreateModalOpen(true)} className="rounded-lg px-3 py-2 font-semibold text-slate-500 hover:bg-sky-50 hover:text-sky-600">📷 {t('feed.action_media')}</button>
                                <button onClick={() => setIsCreateModalOpen(true)} className="rounded-lg px-3 py-2 font-semibold text-slate-500 hover:bg-sky-50 hover:text-sky-600">🏭 {t('feed.action_factory')}</button>
                                <button onClick={() => setIsCreateModalOpen(true)} className="rounded-lg px-3 py-2 font-semibold text-slate-500 hover:bg-sky-50 hover:text-sky-600">📋 {t('feed.action_rfq')}</button>
                            </div>
                        </div>

                        {posts?.data?.length ? posts.data.map((post) => <PostCard key={post.id} post={post} />) : <EmptyState icon="🏭" title={t('feed.empty_title')} description={t('feed.empty_description')} actionText={t('feed.create_post')} onAction={() => setIsCreateModalOpen(true)} />}
                    </main>

                    <aside className="hidden lg:block">
                        <div className="tradeon-card sticky top-24 p-4">
                            <div className="flex items-center justify-between">
                                <div><p className="text-sm font-bold text-slate-900">{t('feed.verified_manufacturers')}</p><p className="text-[11px] text-slate-500">{t('feed.sidebar_hint')}</p></div>
                                <Link href="/manufacturers" className="text-xs font-bold text-sky-600 hover:text-sky-700">{t('feed.see_all')}</Link>
                            </div>
                            <div className="mt-4 space-y-3">
                                {recommendedManufacturers?.length ? recommendedManufacturers.map((m) => (
                                    <Link key={m.id} href={`/company/${m.slug}`} className="flex items-center gap-3 rounded-xl p-2 transition hover:bg-slate-50">
                                        {m.logo ? <img loading="lazy" decoding="async" src={m.logo} alt={m.name} className="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200" /> : <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-lg">🏭</div>}
                                        <div className="min-w-0"><p className="truncate text-xs font-bold text-slate-900">{m.name}</p><p className="truncate text-[10px] text-slate-500">{m.industry || t('feed.fallback_manufacturing')} • {m.country || t('feed.fallback_global')}</p></div>
                                    </Link>
                                )) : <p className="text-xs text-slate-400">{t('feed.sidebar_empty')}</p>}
                            </div>
                            <div className="mt-5 rounded-2xl bg-gradient-to-br from-sky-50 to-cyan-50 p-4">
                                <p className="text-xs font-bold text-sky-800">{t('feed.grow_network')}</p>
                                <p className="mt-1 text-[11px] leading-5 text-sky-700/80">{t('feed.grow_network_hint')}</p>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
            <CreatePostModal isOpen={isCreateModalOpen} onClose={() => setIsCreateModalOpen(false)} />
        </TradeonLayout>
    );
}
