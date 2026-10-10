import React, { useState } from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import VerifiedBadge from '@/Components/Tradeon/VerifiedBadge';
import PostCard from '@/Components/Tradeon/PostCard';
import EmptyState from '@/Components/Tradeon/EmptyState';
import FactoryForm from '@/Components/Tradeon/FactoryForm';
import ShowcaseManager from '@/Components/Tradeon/ShowcaseManager';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';
import { businessTypeLabel, countryLabel, industryLabel } from '@/taxonomy';

export default function CompanyShow({ company, isConnected, isFollowing, isOwner = false }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [activeTab, setActiveTab] = useState('about');
    const [followingState, setFollowingState] = useState(isFollowing);

    const handleFollow = () => {
        setFollowingState(!followingState);
        router.post(`/company/${company.id}/follow`, {}, { preserveScroll: true });
    };

    const tabs = [
        { id: 'about', key: 'company.tab_about' },
        { id: 'posts', key: 'company.tab_posts' },
        { id: 'products', key: 'company.tab_products' },
        { id: 'services', key: 'company.tab_services' },
        { id: 'factory', key: 'company.tab_factory' },
        { id: 'markets', key: 'company.tab_markets' },
        { id: 'team', key: 'company.tab_team' },
        { id: 'documents', key: 'company.tab_documents' },
        { id: 'requests', key: 'company.tab_requests' },
    ];

    const businessTypes = Array.isArray(company.business_types) ? company.business_types : [];
    const TAB_KEY = { services: 'company.tab_services', team: 'company.tab_team', documents: 'company.tab_documents', requests: 'company.tab_requests' };

    return (
        <TradeonLayout title={company.name}>
            <Head>
                <title>{`${company.name} - Tradeon`}</title>
                <meta head-key="description" name="description" content={(company.description || `${company.name} - ${company.industry || ''} ${company.country || ''}`).slice(0, 160)} />
                <meta head-key="og:title" property="og:title" content={`${company.name} - Tradeon`} />
                <meta head-key="og:description" property="og:description" content={(company.description || '').slice(0, 160)} />
                {company.logo && <meta head-key="og:image" property="og:image" content={company.logo} />}
            </Head>
            <div className="space-y-6">

                {/* Profile Header Box */}
                <div className="tradeon-card overflow-hidden">
                    {/* Cover Image */}
                    <div className="relative h-48 bg-gradient-to-r from-sky-100 via-cyan-50 to-blue-100 sm:h-64">
                        {company.cover && (
                            <img loading="lazy" decoding="async"
                                src={company.cover}
                                alt={t('company.cover_alt')}
                                className="h-full w-full object-cover"
                            />
                        )}
                    </div>

                    {/* Header Info Bar */}
                    <div className="relative px-6 pb-6">
                        <div className="mb-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-end -mt-12 sm:-mt-16">
                            <div className="flex items-end space-x-4 rtl:space-x-reverse">
                                {company.logo ? (
                                    <img loading="lazy" decoding="async"
                                        src={company.logo}
                                        alt={company.name}
                                        className="h-24 w-24 rounded-2xl border-4 border-white bg-white object-cover shadow-xl sm:h-32 sm:w-32"
                                    />
                                ) : (
                                    <div className="flex h-24 w-24 items-center justify-center rounded-2xl border-4 border-white bg-sky-500 text-4xl font-bold text-white shadow-xl sm:h-32 sm:w-32">
                                        {company.name?.charAt(0)}
                                    </div>
                                )}
                                <div className="pb-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h1 className="text-xl font-extrabold text-slate-900 sm:text-2xl">{company.name}</h1>
                                        <VerifiedBadge tier={company.verified_tier} />
                                    </div>
                                    <p className="mt-1 text-xs font-semibold text-sky-600">
                                        {industryLabel(company.industry, locale)} • 📍 {company.city}, {countryLabel(company.country, locale)}
                                    </p>
                                </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex flex-wrap items-center gap-2">
                                <button
                                    onClick={handleFollow}
                                    className={`rounded-xl px-4 py-2 text-xs font-bold transition ${followingState ? 'border border-slate-200 bg-white text-slate-500' : 'bg-sky-500 text-white hover:bg-sky-600'}`}
                                >
                                    {followingState ? `✓ ${t('company.following')}` : `+ ${t('company.follow')}`}
                                </button>

                                <Link
                                    href={`/messages?recipient=${company.user_id}`}
                                    className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-sky-200 hover:text-sky-600"
                                >
                                    💬 {t('company.message')}
                                </Link>

                                <Link
                                    href={`/messages?recipient=${company.user_id}&purpose=catalogue`}
                                    className="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-slate-700"
                                >
                                    📑 {t('company.request_catalogue')}
                                </Link>
                            </div>
                        </div>

                        {/* Business Type Tags & Stats */}
                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 text-xs text-slate-500">
                            <div className="flex flex-wrap gap-1.5">
                                {businessTypes.map((bt) => (
                                    <span key={bt} className="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-600">
                                        {businessTypeLabel(bt, locale)}
                                    </span>
                                ))}
                            </div>

                            <div className="flex items-center space-x-6 text-xs rtl:space-x-reverse">
                                <div><strong className="font-bold text-slate-900">{company.followers_count}</strong> {t('company.followers')}</div>
                                <div><strong className="font-bold text-slate-900">{company.connections_count}</strong> {t('company.connections')}</div>
                                <div>{t('company.response_rate')}: <strong className="text-emerald-600">{company.response_rate}%</strong></div>
                            </div>
                        </div>
                    </div>

                    {/* Profile Tabs */}
                    <div className="border-t border-slate-100 bg-slate-50/60 px-6">
                        <div className="flex space-x-6 overflow-x-auto text-xs font-semibold text-slate-500 rtl:space-x-reverse">
                            {tabs.map((tab) => (
                                <button
                                    key={tab.id}
                                    onClick={() => setActiveTab(tab.id)}
                                    className={`whitespace-nowrap border-b-2 py-3.5 transition ${activeTab === tab.id ? 'border-sky-500 font-bold text-sky-600' : 'border-transparent hover:text-slate-900'}`}
                                >
                                    {t(tab.key)}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Tab Content Display Area */}
                <div className="tradeon-card p-6">

                    {/* ABOUT TAB */}
                    {activeTab === 'about' && (
                        <div className="space-y-6">
                            <div>
                                <h3 className="mb-2 text-sm font-bold uppercase tracking-wider text-slate-900">{t('company.overview')}</h3>
                                <p className="whitespace-pre-line text-sm leading-6 text-slate-600">{company.description}</p>
                            </div>

                            <div className="grid grid-cols-1 gap-4 border-t border-slate-100 pt-5 text-xs md:grid-cols-3">
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div className="mb-1 text-slate-500">{t('company.company_size')}</div>
                                    <div className="font-bold text-slate-900">{company.company_size || t('company.na')}</div>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div className="mb-1 text-slate-500">{t('company.founded_year')}</div>
                                    <div className="font-bold text-slate-900">{company.founded_year || t('company.na')}</div>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div className="mb-1 text-slate-500">{t('company.moq')}</div>
                                    <div className="font-bold text-sky-600">{company.moq || t('company.na')}</div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* POSTS TAB */}
                    {activeTab === 'posts' && (
                        <div>
                            {company.posts && company.posts.length > 0 ? (
                                company.posts.map((post) => <PostCard key={post.id} post={post} />)
                            ) : (
                                <EmptyState icon="📢" title={t('company.no_posts')} description={t('company.no_posts_desc')} />
                            )}
                        </div>
                    )}

                    {/* PRODUCTS TAB */}
                    {activeTab === 'products' && (
                        <div>
                            {isOwner ? (
                                <ShowcaseManager company={company} />
                            ) : company.product_showcases && company.product_showcases.length > 0 ? (
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    {company.product_showcases.map((prod) => (
                                        <div key={prod.id} className="rounded-2xl border border-slate-200 bg-white p-4">
                                            {prod.images?.[0] && <img loading="lazy" decoding="async" src={prod.images?.[0]} className="mb-3 h-40 w-full rounded-xl object-cover" />}
                                            <h4 className="text-sm font-bold text-slate-900">{prod.title}</h4>
                                            <p className="mt-1 line-clamp-2 text-xs text-slate-500">{prod.description}</p>
                                            <div className="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 text-xs">
                                                <span className="text-slate-500">{t('common.moq')}: <strong>{prod.moq || '—'}</strong></span>
                                                <Link href={`/messages?recipient=${company.user_id}`} className="font-bold text-sky-600 hover:underline">{t('directories.inquire')}</Link>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <EmptyState icon="📦" title={t('company.no_products')} description={t('company.no_products_desc')} />
                            )}
                        </div>
                    )}

                    {/* FACTORY TAB */}
                    {activeTab === 'factory' && (
                        <div className="space-y-5">
                            {isOwner && <FactoryForm company={company} />}
                            {company.factory ? (
                                <div className="space-y-4 text-xs">
                                    <h3 className="text-sm font-bold text-slate-900">{t('company.factory_overview')}</h3>
                                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                        <div className="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                            <div className="text-slate-500">{t('company.production_lines')}</div>
                                            <div className="text-sm font-bold text-slate-900">{company.factory.production_lines}</div>
                                        </div>
                                        <div className="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                            <div className="text-slate-500">{t('company.annual_output')}</div>
                                            <div className="text-sm font-bold text-slate-900">{company.factory.annual_output}</div>
                                        </div>
                                    </div>
                                    {company.factory.photos && company.factory.photos.length > 0 && (
                                        <div className="mt-4 grid grid-cols-2 gap-2">
                                            {company.factory.photos.map((photo, i) => (
                                                <img loading="lazy" decoding="async" key={i} src={photo} className="h-48 w-full rounded-xl object-cover" />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <EmptyState icon="🏭" title={t('company.no_factory')} description={t('company.no_factory_desc')} />
                            )}
                        </div>
                    )}

                    {/* MARKETS TAB */}
                    {activeTab === 'markets' && (
                        <div className="space-y-4 text-xs">
                            <h3 className="text-sm font-bold text-slate-900">{t('company.export_destinations')}</h3>
                            <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <h4 className="mb-2 font-bold text-sky-600">{t('company.export_title')}</h4>
                                <div className="flex flex-wrap gap-1.5">
                                    {company.export_markets ? company.export_markets.map(m => (
                                        <span key={m} className="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-slate-600">{m}</span>
                                    )) : <span className="text-slate-400">{t('company.global')}</span>}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* OTHER TABS (FALLBACK EMPTY STATES) */}
                    {['services', 'team', 'documents', 'requests'].includes(activeTab) && (
                        <EmptyState
                            icon="📂"
                            title={t('company.no_section', { tab: t(TAB_KEY[activeTab] || 'company.tab_services') })}
                            description={t('company.no_section_desc', { tab: t(TAB_KEY[activeTab] || 'company.tab_services') })}
                        />
                    )}

                </div>

            </div>
        </TradeonLayout>
    );
}
