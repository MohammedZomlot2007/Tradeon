import React, { useState, useEffect } from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import EmptyState from '@/Components/Tradeon/EmptyState';
import VerifiedBadge from '@/Components/Tradeon/VerifiedBadge';
import { Link, router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function MessengerIndex({ conversations, activeConversation, chatRequests, contactTarget, contactPurpose = 'general', currentUserId }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [messageText, setMessageText] = useState('');
    const [echoLive, setEchoLive] = useState(false);
    const defaultPurposeMessage = contactPurpose === 'catalogue'
        ? t('messenger.default_catalogue')
        : t('messenger.default_general');
    const [requestMessage, setRequestMessage] = useState(defaultPurposeMessage);

    useEffect(() => {
        if (!activeConversation?.id) return;
        // Reverb-first: slow polling when realtime is live (15s), fast fallback (4s) otherwise.
        // Skip polling entirely when the tab is hidden to save battery/server load.
        const interval = echoLive ? 15000 : 4000;
        const timer = setInterval(() => {
            if (typeof document !== 'undefined' && document.visibilityState === 'hidden') return;
            router.reload({ only: ['activeConversation', 'conversations', 'chatRequests'], preserveScroll: true, preserveState: true });
        }, interval);
        return () => clearInterval(timer);
    }, [activeConversation?.id, echoLive]);

    useEffect(() => {
        if (!window.Echo || !activeConversation?.id) {
            setEchoLive(false);
            return;
        }
        const channel = window.Echo.private(`conversations.${activeConversation.id}`);
        channel.listen('.message.sent', () => router.reload({ only: ['activeConversation', 'conversations'], preserveState: true, preserveScroll: true }));
        setEchoLive(true);
        return () => {
            window.Echo.leave(`conversations.${activeConversation.id}`);
            setEchoLive(false);
        };
    }, [activeConversation?.id]);

    const handleSendMessage = (e) => {
        e.preventDefault();
        if (!messageText.trim() || !activeConversation) return;

        router.post(`/messages/${activeConversation.id}`, {
            body: messageText,
        }, {
            preserveScroll: true,
            onSuccess: () => setMessageText(''),
        });
    };

    const handleChatRequestResponse = (id, action) => {
        router.post(`/chat-requests/${id}/respond`, { action }, { preserveScroll: true });
    };

    const handleSendChatRequest = (e) => {
        e.preventDefault();
        if (!contactTarget?.id || !requestMessage.trim()) return;

        router.post('/chat-requests', {
            receiver_id: contactTarget.id,
            purpose: contactPurpose || 'general',
            initial_message: requestMessage.trim(),
        }, { preserveScroll: true });
    };

    const recipientUser = activeConversation
        ? (activeConversation.user_one_id === currentUserId ? activeConversation.user_two : activeConversation.user_one)
        : null;
    const recipientCompany = activeConversation
        ? (activeConversation.company_one_id && activeConversation.user_one_id === currentUserId ? activeConversation.company_two : activeConversation.company_one)
        : null;

    return (
        <TradeonLayout title={t('messenger.title')}>
            <div className="tradeon-card grid h-[700px] grid-cols-1 overflow-hidden md:grid-cols-3 lg:grid-cols-4">

                {/* Conversations Sidebar List */}
                <div className="flex flex-col justify-between border-r border-slate-100 bg-slate-50/60">
                    <div>
                        <div className="flex items-center justify-between border-b border-slate-100 bg-white p-4">
                            <h2 className="flex items-center space-x-2 text-sm font-bold text-slate-900">
                                <span>💬</span>
                                <span>{t('messenger.title')}</span>
                            </h2>
                        </div>

                        {/* Pending Contact Requests */}
                        {chatRequests && chatRequests.length > 0 && (
                            <div className="border-b border-sky-100 bg-sky-50/60 p-3">
                                <div className="mb-2 text-[11px] font-bold text-sky-700">{t('messenger.pending')} ({chatRequests.length})</div>
                                {chatRequests.map((req) => (
                                    <div key={req.id} className="mb-2 rounded-xl border border-slate-200 bg-white p-2.5 text-xs">
                                        <div className="font-bold text-slate-900">{req.sender?.name}</div>
                                        <p className="mt-0.5 line-clamp-2 text-[11px] text-slate-500">{req.initial_message}</p>
                                        <div className="mt-2 flex gap-2">
                                            <button onClick={() => handleChatRequestResponse(req.id, 'accept')} className="rounded bg-sky-500 px-2.5 py-1 text-[10px] font-bold text-white">{t('messenger.accept')}</button>
                                            <button onClick={() => handleChatRequestResponse(req.id, 'decline')} className="rounded bg-slate-100 px-2.5 py-1 text-[10px] text-slate-600">{t('messenger.decline')}</button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        {/* Conversations List */}
                        <div className="max-h-[550px] divide-y divide-slate-100 overflow-y-auto">
                            {conversations && conversations.length > 0 ? (
                                conversations.map((conv) => (
                                    <Link
                                        key={conv.id}
                                        href={`/messages?conversation_id=${conv.id}`}
                                        className={`block flex items-center space-x-3 p-4 transition ${activeConversation?.id === conv.id ? 'border-l-4 border-sky-500 bg-white' : 'hover:bg-white'}`}
                                    >
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700">
                                            {((conv.user_one_id === currentUserId ? conv.user_two?.name : conv.user_one?.name) || t('messenger.fallback_user')).charAt(0)}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate text-xs font-semibold text-slate-900">{conv.user_one_id === currentUserId ? conv.user_two?.name : conv.user_one?.name}</div>
                                            <div className="truncate text-[11px] text-slate-500">
                                                {conv.messages?.[0]?.body || t('messenger.start_conversation')}
                                            </div>
                                        </div>
                                    </Link>
                                ))
                            ) : (
                                <div className="p-6 text-center text-xs text-slate-400">{t('messenger.no_conversations')}</div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Main Chat Conversation View */}
                {activeConversation ? (
                    <div className="flex h-full flex-col justify-between bg-white md:col-span-2">
                        {/* Conversation Header */}
                        <div className="flex items-center justify-between border-b border-slate-100 bg-white p-4">
                            <div className="flex items-center space-x-3">
                                <div className="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-tr from-sky-400 to-cyan-500 text-sm font-bold text-white">
                                    {(recipientUser?.name || t('messenger.fallback_avatar')).charAt(0)}
                                </div>
                                <div>
                                    <h3 className="text-xs font-bold text-slate-900">{recipientUser?.name || t('messenger.fallback_contact')}</h3>
                                    <p className="text-[10px] font-medium text-sky-600">{recipientCompany?.name || t('messenger.fallback_company')}</p>
                                </div>
                            </div>
                        </div>

                        {/* Messages Feed */}
                        <div className="flex-1 space-y-3 overflow-y-auto bg-slate-50/50 p-4">
                            {activeConversation.messages?.map((msg) => (
                                <div
                                    key={msg.id}
                                    className={`flex ${msg.sender_id === currentUserId ? 'justify-end' : 'justify-start'}`}
                                >
                                    <div
                                        className={`max-w-xs rounded-2xl px-4 py-2.5 text-xs leading-relaxed sm:max-w-md ${msg.sender_id === currentUserId ? 'rounded-br-md bg-sky-500 font-medium text-white shadow' : 'rounded-bl-md border border-slate-200 bg-white text-slate-700'}`}
                                    >
                                        <p>{msg.body}</p>
                                        <span className={`mt-1 block text-right text-[9px] opacity-70`}>
                                            {new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Input Box */}
                        <form onSubmit={handleSendMessage} className="flex gap-2 border-t border-slate-100 bg-white p-3">
                            <input
                                type="text"
                                placeholder={t('messenger.placeholder')}
                                className="tradeon-input flex-1 px-4 py-2.5 text-xs"
                                value={messageText}
                                onChange={(e) => setMessageText(e.target.value)}
                            />
                            <button type="submit" className="rounded-xl bg-sky-500 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-sky-600">
                                {t('messenger.send')} ➤
                            </button>
                        </form>
                    </div>
                ) : contactTarget ? (
                    <div className="flex items-center justify-center bg-white p-8 md:col-span-2">
                        <form onSubmit={handleSendChatRequest} className="w-full max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-6">
                            <div>
                                <p className="text-[11px] font-bold uppercase tracking-wider text-sky-600">{t('messenger.new_request')}</p>
                                <h3 className="mt-1 text-lg font-bold text-slate-900">{t('messenger.send_request')}: {contactTarget.company?.name || contactTarget.name}</h3>
                                <p className="mt-1 text-xs text-slate-500">{t('messenger.request_hint')}</p>
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-slate-600">{t('messenger.purpose')}</label>
                                <div className="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs capitalize text-slate-700">{contactPurpose === 'catalogue' ? t('messenger.purpose_catalogue') : t('messenger.purpose_general')}</div>
                            </div>
                            <div>
                                <label className="text-xs font-semibold text-slate-600">{t('messenger.initial_message')}</label>
                                <textarea value={requestMessage} onChange={(e) => setRequestMessage(e.target.value)} rows={5} maxLength={5000} className="tradeon-input mt-1 px-3 py-3 text-sm" />
                            </div>
                            <div className="flex justify-end gap-2">
                                <Link href="/messages" className="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600">{t('common.cancel')}</Link>
                                <button type="submit" disabled={!requestMessage.trim()} className="rounded-xl bg-sky-500 px-4 py-2.5 text-xs font-bold text-white disabled:opacity-50">{t('messenger.send_request')}</button>
                            </div>
                        </form>
                    </div>
                ) : (
                    <div className="flex items-center justify-center bg-white p-8 md:col-span-2">
                        <EmptyState icon="💬" title={t('messenger.no_selection_title')} description={t('messenger.no_selection_desc')} />
                    </div>
                )}

                {/* Right Sidebar: Business Context */}
                <div className="hidden space-y-5 border-l border-slate-100 bg-slate-50/60 p-5 text-xs lg:block">
                    <h3 className="border-b border-slate-200 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-900">{t('messenger.business_context')}</h3>

                    {recipientCompany ? (
                        <div className="space-y-3">
                            {recipientCompany.logo ? (
                                <img loading="lazy" decoding="async" src={recipientCompany.logo} className="h-14 w-14 rounded-xl object-cover ring-1 ring-slate-200" />
                            ) : (
                                <div className="flex h-14 w-14 items-center justify-center rounded-xl bg-sky-100 text-2xl">🏢</div>
                            )}
                            <h4 className="text-sm font-bold text-slate-900">{recipientCompany.name}</h4>
                            <VerifiedBadge tier={recipientCompany.verified_tier} />

                            <div className="space-y-1 pt-2 text-[11px] text-slate-500">
                                <div>{t('messenger.context_industry')}: <strong className="text-slate-800">{recipientCompany.industry}</strong></div>
                                <div>{t('messenger.context_country')}: <strong className="text-slate-800">{recipientCompany.country}</strong></div>
                                <div>{t('messenger.context_response')}: <strong className="text-emerald-600">{recipientCompany.response_rate}%</strong></div>
                            </div>

                            <Link
                                href={`/company/${recipientCompany.slug}`}
                                className="mt-4 block w-full rounded-xl border border-slate-200 bg-white py-2 text-center text-xs font-semibold text-slate-700 transition hover:border-sky-200 hover:text-sky-600"
                            >
                                {t('messenger.view_profile')}
                            </Link>
                        </div>
                    ) : (
                        <div className="py-6 text-center text-slate-400">{t('messenger.context_empty')}</div>
                    )}
                </div>

            </div>
        </TradeonLayout>
    );
}
