import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

const TYPE_ICONS = {
    post_like: '❤️',
    post_comment: '💬',
    chat_message: '💬',
    chat_request: '📨',
    connection_request: '🤝',
    rfq_quote: '📑',
    verification_status: '🛡️',
    account_status: '⚠️',
};

export default function NotificationsIndex({ unreadCount, notifications }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const markRead = (id) => router.post(`/notifications/${id}/read`, {}, { preserveScroll: true });
    const markAll = () => router.post('/notifications/read-all', {}, { preserveScroll: true });

    const items = notifications?.data || [];

    return (
        <TradeonLayout>
            <Head title={t('notifications.title')} />
            <div className="mx-auto max-w-3xl space-y-5">
                <div className="page-hero flex items-center justify-between">
                    <div>
                        <h1 className="page-title">🔔 {t('notifications.title')}</h1>
                        <p className="page-subtitle">{unreadCount > 0 ? `${unreadCount} ${t('notifications.unread')}` : t('notifications.caught_up')}</p>
                    </div>
                    {unreadCount > 0 && (
                        <button onClick={markAll} className="btn-primary">{t('notifications.mark_all')}</button>
                    )}
                </div>

                {items.length === 0 ? (
                    <div className="empty-state">
                        <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-3xl">🔔</div>
                        <h3 className="font-bold text-slate-900">{t('notifications.empty_title')}</h3>
                        <p className="mt-1 text-xs text-slate-500">{t('notifications.empty_desc')}</p>
                    </div>
                ) : (
                    <div className="space-y-2">
                        {items.map((n) => (
                            <div key={n.id} className={`tradeon-card flex items-start gap-3 p-4 ${!n.read_at ? 'border-l-4 border-l-sky-500' : ''}`}>
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-lg">
                                    {TYPE_ICONS[n.data?.type] || '🔔'}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <div className="text-sm font-bold text-slate-900">{n.data?.title || t('notifications.title')}</div>
                                    <div className="mt-0.5 text-xs leading-5 text-slate-600">{n.data?.message}</div>
                                    <div className="mt-1 text-[10px] text-slate-400">{new Date(n.created_at).toLocaleString()}</div>
                                </div>
                                <div className="flex shrink-0 flex-col gap-1">
                                    {n.data?.url && <Link href={n.data.url} className="rounded-lg bg-slate-100 px-3 py-1.5 text-center text-[11px] font-bold text-slate-700 hover:bg-sky-50 hover:text-sky-700">{t('notifications.open')}</Link>}
                                    {!n.read_at && <button onClick={() => markRead(n.id)} className="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-500">{t('notifications.mark_read')}</button>}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {notifications?.links && notifications.links.length > 3 && (
                    <div className="flex justify-center gap-1">
                        {notifications.links.map((link, i) => (
                            <Link key={i} href={link.url || '#'} dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`rounded-lg px-3 py-1.5 text-xs ${link.active ? 'bg-sky-500 font-bold text-white' : link.url ? 'border border-slate-200 bg-white text-slate-600' : 'text-slate-300'}`} />
                        ))}
                    </div>
                )}
            </div>
        </TradeonLayout>
    );
}
