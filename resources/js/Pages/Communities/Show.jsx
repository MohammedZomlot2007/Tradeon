import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import PostCard from '@/Components/Tradeon/PostCard';
import CompanyCard from '@/Components/Tradeon/CompanyCard';
import EmptyState from '@/Components/Tradeon/EmptyState';

import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function CommunityShow({ community, posts, companies }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-6">

                {/* Header */}
                <div className="page-hero">
                    <span className="badge-sky">
                        {community.industry}
                    </span>
                    <h1 className="page-title mt-3 !text-2xl">{community.name}</h1>
                    <p className="page-subtitle mt-2 max-w-2xl">{community.description}</p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Community Posts */}
                    <div className="lg:col-span-2 space-y-4">
                        <h2 className="text-base font-bold text-slate-900">{t('communities.posts_title')}</h2>
                        {posts && posts.data && posts.data.length > 0 ? (
                            posts.data.map((post) => <PostCard key={post.id} post={post} />)
                        ) : (
                            <EmptyState icon="📢" title={t('communities.no_posts')} description={t('communities.no_posts_desc')} />
                        )}
                    </div>

                    {/* Active Companies */}
                    <div className="space-y-4">
                        <h2 className="text-base font-bold text-slate-900">{t('communities.featured')}</h2>
                        {companies && companies.length > 0 ? (
                            companies.map((comp) => <CompanyCard key={comp.id} company={comp} />)
                        ) : (
                            <div className="empty-state !py-6 text-xs text-slate-400">{t('communities.no_companies')}</div>
                        )}
                    </div>
                </div>

            </div>
        </TradeonLayout>
    );
}
