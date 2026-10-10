import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';
import VerifiedBadge from './VerifiedBadge';

const badgeStyles = {
    business_update: 'bg-sky-50 text-sky-700 border-sky-200',
    product_launch: 'bg-violet-50 text-violet-700 border-violet-200',
    factory_update: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    manufacturing_process: 'bg-amber-50 text-amber-700 border-amber-200',
    rfq_sourcing: 'bg-rose-50 text-rose-700 border-rose-200',
    looking_for_supplier: 'bg-cyan-50 text-cyan-700 border-cyan-200',
    looking_for_manufacturer: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    looking_for_distributor: 'bg-purple-50 text-purple-700 border-purple-200',
    partnership_request: 'bg-teal-50 text-teal-700 border-teal-200',
    trade_event: 'bg-orange-50 text-orange-700 border-orange-200',
};

const badgeLabels = {
    business_update: 'post.types.business_update',
    product_launch: 'post.types.product_launch',
    factory_update: 'post.types.factory_update',
    manufacturing_process: 'post.types.manufacturing_process',
    rfq_sourcing: 'post.types.rfq_sourcing',
    looking_for_supplier: 'post.types.looking_for_supplier',
    looking_for_manufacturer: 'post.types.looking_for_manufacturer',
    looking_for_distributor: 'post.types.looking_for_distributor',
    partnership_request: 'post.types.partnership_request',
    trade_event: 'post.types.trade_event',
};

export default function PostCard({ post }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [liked, setLiked] = useState(Boolean(post.viewer_liked));
    const [saved, setSaved] = useState(Boolean(post.viewer_saved));
    const [reposted, setReposted] = useState(Boolean(post.viewer_reposted));
    const [likesCount, setLikesCount] = useState(post.likes_count || 0);
    const [repostsCount, setRepostsCount] = useState(post.reposts_count || 0);
    const [commentCount, setCommentCount] = useState(post.comments_count || 0);
    const [showComments, setShowComments] = useState(false);
    const [showMenu, setShowMenu] = useState(false);
    const [commentText, setCommentText] = useState('');

    const toggleLike = () => {
        const next = !liked;
        setLiked(next);
        setLikesCount((count) => Math.max(0, count + (next ? 1 : -1)));
        router.post(`/posts/${post.id}/like`, {}, {
            preserveScroll: true,
            onError: () => {
                setLiked(!next);
                setLikesCount((count) => Math.max(0, count + (next ? -1 : 1)));
            },
        });
    };

    const submitComment = (e) => {
        e.preventDefault();
        const text = commentText.trim();
        if (!text) return;
        router.post(`/posts/${post.id}/comment`, { comment: text }, {
            preserveScroll: true,
            onSuccess: () => {
                setCommentText('');
                setCommentCount((count) => count + 1);
                setShowComments(true);
            },
        });
    };

    const toggleSave = () => {
        const next = !saved;
        setSaved(next);
        router.post(`/posts/${post.id}/save`, {}, { preserveScroll: true, onError: () => setSaved(!next) });
    };

    const toggleRepost = () => {
        const next = !reposted;
        setReposted(next);
        setRepostsCount((count) => Math.max(0, count + (next ? 1 : -1)));
        router.post(`/posts/${post.id}/repost`, {}, {
            preserveScroll: true,
            onError: () => {
                setReposted(!next);
                setRepostsCount((count) => Math.max(0, count + (next ? -1 : 1)));
            },
        });
    };

    const sharePost = async () => {
        const url = `${window.location.origin}/feed?post=${post.id}`;
        try {
            if (navigator.share) await navigator.share({ title: post.title || t('post.share_fallback'), text: post.content?.slice(0, 160) || '', url });
            else if (navigator.clipboard) await navigator.clipboard.writeText(url);
        } catch { /* cancelled */ }
    };

    const reportPost = () => {
        router.post(`/posts/${post.id}/report`, { reason: 'business_content' }, { preserveScroll: true, onSuccess: () => setShowMenu(false) });
    };

    const comments = post.comments || [];
    const badge = badgeLabels[post.type] ? t(badgeLabels[post.type]) : post.type;
    const badgeClass = badgeStyles[post.type] || 'bg-slate-50 text-slate-600 border-slate-200';

    return (
        <article className="tradeon-card mb-4 overflow-hidden transition hover:-translate-y-[1px] hover:shadow-[0_18px_50px_-28px_rgba(15,23,42,0.35)]">
            <div className="p-4 sm:p-5">
                <div className="flex items-start justify-between gap-3">
                    <div className="flex min-w-0 items-center gap-3">
                        {post.company?.logo ? (
                            <img loading="lazy" decoding="async" src={post.company.logo} alt={post.company?.name || post.user?.name} className="h-11 w-11 rounded-xl object-cover ring-1 ring-slate-200" />
                        ) : (
                            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-lg ring-1 ring-sky-100">🏢</div>
                        )}
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-1.5">
                                {post.company ? <Link href={`/company/${post.company.slug}`} className="truncate text-sm font-bold text-slate-900 hover:text-sky-600">{post.company.name}</Link> : <span className="text-sm font-bold text-slate-900">{post.user?.name}</span>}
                                <VerifiedBadge tier={post.company?.verified_tier} />
                            </div>
                            <div className="mt-0.5 flex items-center gap-2 text-[11px] text-slate-400">
                                <span>📍 {post.country || post.company?.country || t('post.global_fallback')}</span><span>•</span><span>{new Date(post.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                    </div>
                    <div className="relative">
                        <button onClick={() => setShowMenu(!showMenu)} aria-label={t('post.post_actions')} className="tradeon-icon-btn">•••</button>
                        {showMenu && (
                            <div className="absolute right-0 z-20 mt-2 w-40 rounded-xl border border-slate-200 bg-white p-1 shadow-xl">
                                <button onClick={reportPost} className="w-full rounded-lg px-3 py-2 text-left text-xs font-medium text-slate-600 hover:bg-slate-50">{t('post.report')}</button>
                            </div>
                        )}
                    </div>
                </div>

                <div className="mt-4">
                    <span className={`tradeon-pill ${badgeClass}`}>{post.type === 'factory_update' ? '🏭' : post.type === 'rfq_sourcing' ? '📋' : '•'} {badge}</span>
                    {post.title && <h3 className="mt-3 text-base font-extrabold leading-6 text-slate-900">{post.title}</h3>}
                    <p className="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{post.content}</p>
                </div>

                {post.media_urls?.length > 0 && (
                    <div className={`mt-4 grid gap-2 overflow-hidden rounded-2xl ${post.media_urls.length > 1 ? 'grid-cols-2' : 'grid-cols-1'}`}>
                        {post.media_urls.map((url, idx) => <img loading="lazy" decoding="async" key={idx} src={url} alt={t('post.attachment_alt')} className="h-56 w-full object-cover" />)}
                    </div>
                )}

                <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-500">
                    <div className="flex items-center gap-3">
                        <span>{likesCount} {t('post.likes')}</span>
                        <button onClick={() => setShowComments(!showComments)} className="hover:text-sky-600">{commentCount} {t('post.comments')}</button>
                        <span>{repostsCount} {t('post.reposts')}</span>
                    </div>
                </div>

                <div className="mt-2 grid grid-cols-4 gap-1 border-y border-slate-100 py-1">
                    <button onClick={toggleLike} className={`rounded-xl px-2 py-2.5 text-xs font-bold transition ${liked ? 'bg-rose-50 text-rose-600' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'}`}>❤️ <span className="ml-1">{t('post.like')}</span></button>
                    <button onClick={() => setShowComments(!showComments)} className={`rounded-xl px-2 py-2.5 text-xs font-bold transition ${showComments ? 'bg-sky-50 text-sky-600' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'}`}>💬 <span className="ml-1">{t('post.comment')}</span></button>
                    <button onClick={toggleRepost} className={`rounded-xl px-2 py-2.5 text-xs font-bold transition ${reposted ? 'bg-emerald-50 text-emerald-600' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'}`}>🔁 <span className="ml-1">{t('post.repost')}</span></button>
                    <button onClick={sharePost} className="rounded-xl px-2 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 hover:text-slate-800">↗ <span className="ml-1">{t('post.share')}</span></button>
                </div>

                {showComments && (
                    <div className="mt-4 rounded-2xl bg-slate-50 p-3 sm:p-4">
                        <div className="flex gap-2">
                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-cyan-500 text-xs font-bold text-white">T</div>
                            <form onSubmit={submitComment} className="flex flex-1 gap-2">
                                <input value={commentText} onChange={(e) => setCommentText(e.target.value)} placeholder={t('post.add_comment')} className="tradeon-input px-3 py-2 text-xs" />
                                <button type="submit" className="rounded-xl bg-sky-500 px-3 text-xs font-bold text-white hover:bg-sky-600">{t('post.post_comment')}</button>
                            </form>
                        </div>

                        <div className="mt-3 space-y-3">
                            {comments.length > 0 ? comments.map((comment) => (
                                <div key={comment.id} className="flex gap-2">
                                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-600 ring-1 ring-slate-200">{(comment.company?.name || comment.user?.name || t('post.member_fallback')).charAt(0)}</div>
                                    <div className="min-w-0 rounded-2xl bg-white px-3 py-2 ring-1 ring-slate-100">
                                        <div className="text-[11px] font-bold text-slate-800">{comment.company?.name || comment.user?.name || t('post.member_fallback')}</div>
                                        <p className="mt-0.5 text-xs leading-5 text-slate-600">{comment.comment}</p>
                                    </div>
                                </div>
                            )) : (
                                <p className="py-2 text-center text-xs text-slate-400">{t('post.no_comments')}</p>
                            )}
                        </div>
                    </div>
                )}

                {post.company && <div className="mt-3 flex justify-end"><Link href={`/messages?recipient=${post.company.user_id}`} className="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700 hover:bg-sky-100">{t('post.contact_company')}</Link></div>}
                <div className="mt-2 flex justify-end">
                    <button onClick={toggleSave} className={`text-[11px] font-semibold ${saved ? 'text-amber-600' : 'text-slate-400 hover:text-slate-600'}`}>{saved ? `🔖 ${t('post.saved')}` : t('post.save')}</button>
                </div>
            </div>
        </article>
    );
}
