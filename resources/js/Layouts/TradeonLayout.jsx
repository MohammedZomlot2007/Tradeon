import React, { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { translate } from '@/i18n';
import TradeonLogo from '@/Components/TradeonLogo';

export default function TradeonLayout({ children }) {
    const { auth, flash, notifications: serverNotifications = [], locale: serverLocale = 'en' } = usePage().props;
    const [lang, setLang] = useState(serverLocale || (typeof window !== 'undefined' && (localStorage.getItem('tradeon_lang') || document.cookie.match(/tradeon_locale=(en|ar)/)?.[1])) || 'en');
    const [unreadCount, setUnreadCount] = useState(serverNotifications?.unread_count || 0);
    const [searchQuery, setSearchQuery] = useState('');
    const [toast, setToast] = useState(flash?.success || flash?.error || null);
    const [mobileOpen, setMobileOpen] = useState(false);
    const t = (key, replacements) => translate(key, lang, replacements);

    const path = typeof window !== 'undefined' ? window.location.pathname : '';
    const isActive = (href) => path === href || (href !== '/feed' && path.startsWith(href + '/')) || (href === '/feed' && path === '/feed');
    const isAdmin = auth?.user?.role === 'admin';

    useEffect(() => {
        if (flash?.success) setToast(flash.success);
        else if (flash?.error) setToast(flash.error);
    }, [flash]);

    useEffect(() => {
        if (serverLocale && serverLocale !== lang) setLang(serverLocale);
    }, [serverLocale]);

    useEffect(() => {
        if (typeof document !== 'undefined') {
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            document.documentElement.lang = lang;
        }
        try { localStorage.setItem('tradeon_lang', lang); } catch { /* ignore */ }
    }, [lang]);

    const toggleLanguage = () => {
        const nextLang = lang === 'en' ? 'ar' : 'en';
        setLang(nextLang);
        try {
            localStorage.setItem('tradeon_lang', nextLang);
            document.cookie = `tradeon_locale=${nextLang}; path=/; max-age=31536000`;
        } catch { /* ignore */ }
        // router.post handles CSRF + session + works for guests (route is outside auth)
        router.post('/locale', { locale: nextLang }, { preserveScroll: true, preserveState: true });
    };

    useEffect(() => {
        if (!auth?.user) return;
        const refresh = () => fetch('/notifications', { headers: { Accept: 'application/json' } })
            .then((r) => (r.ok ? r.json() : null))
            .then((data) => { if (data) setUnreadCount(data.unread_count || 0); })
            .catch(() => {});
        refresh();
        const timer = setInterval(refresh, 30000);
        return () => clearInterval(timer);
    }, [auth?.user?.id]);

    const groups = [
        {
            label: t('nav.group_workspace'),
            items: [
                { key: 'nav.home', href: '/feed', icon: '🏠' },
                { key: 'nav.dashboard', href: '/biz-dashboard', icon: '📈', auth: true },
                { key: 'nav.verification', href: '/verification', icon: '🛡️', auth: true },
                ...(isAdmin ? [{ key: 'nav.admin', href: '/admin', icon: '⚙️' }] : []),
            ],
        },
        {
            label: t('nav.group_discover'),
            items: [
                { key: 'nav.explore', href: '/explore', icon: '🧭' },
                { key: 'nav.businesses', href: '/businesses', icon: '🏢' },
                { key: 'nav.manufacturers', href: '/manufacturers', icon: '🏭' },
                { key: 'nav.suppliers', href: '/suppliers', icon: '📦' },
                { key: 'nav.world_map', href: '/map', icon: '🌍' },
                { key: 'nav.intelligence', href: '/intelligence', icon: '📊' },
            ],
        },
        {
            label: t('nav.group_business'),
            items: [
                { key: 'nav.requests', href: '/requests', icon: '📋' },
                { key: 'nav.smart_match', href: '/smart-match', icon: '✨' },
                { key: 'nav.pricing', href: '/pricing', icon: '💳' },
            ],
        },
        {
            label: t('nav.group_communication'),
            items: [
                { key: 'nav.messages', href: '/messages', icon: '💬', auth: true, badge: null },
                { key: 'nav.notifications', href: '/notifications', icon: '🔔', auth: true, badge: unreadCount > 0 ? unreadCount : null },
                { key: 'nav.communities', href: '/communities', icon: '👥' },
                { key: 'nav.events', href: '/events', icon: '📅' },
            ],
        },
    ];

    const sidebar = (
        <div className="flex h-full flex-col">
            <div className="px-4 pb-2 pt-5">
                <Link href="/feed" aria-label="Tradeon home"><TradeonLogo /></Link>
                <form action="/search" method="GET" className="relative mt-4" role="search">
                    <input
                        type="text"
                        name="q"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        placeholder={t('nav.search_placeholder_short')}
                        aria-label={t('nav.search')}
                        className="tradeon-input py-2.5 pl-9 text-xs rtl:pl-3 rtl:pr-9"
                    />
                    <span aria-hidden="true" className="absolute left-3 top-2.5 text-sm text-slate-400 rtl:left-auto rtl:right-3">🔍</span>
                </form>
            </div>

            <nav aria-label={t('nav.main_navigation')} className="scrollbar-subtle flex-1 overflow-y-auto px-3 pb-4">
                {groups.map((group) => (
                    <div key={group.label}>
                        <div className="side-group-label">{group.label}</div>
                        {group.items.filter((i) => !i.auth || auth?.user).map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                aria-current={isActive(item.href) ? 'page' : undefined}
                                className={`side-link ${isActive(item.href) ? 'side-link-active' : ''}`}
                            >
                                <span aria-hidden="true" className="w-5 text-center">{item.icon}</span>
                                <span className="flex-1">{t(item.key)}</span>
                                {item.badge ? (
                                    <span className="min-w-5 rounded-full bg-rose-500 px-1.5 text-center text-[10px] font-bold leading-5 text-white">
                                        {item.badge > 99 ? '99+' : item.badge}
                                    </span>
                                ) : null}
                            </Link>
                        ))}
                    </div>
                ))}
                {auth?.user && (
                    <div>
                        <div className="side-group-label">{t('nav.group_account')}</div>
                        <Link href="/network" className={`side-link ${isActive('/network') ? 'side-link-active' : ''}`}>
                            <span aria-hidden="true" className="w-5 text-center">📇</span><span className="flex-1">{t('nav.my_network')}</span>
                        </Link>
                        <Link href="/profile" className={`side-link ${isActive('/profile') ? 'side-link-active' : ''}`}>
                            <span aria-hidden="true" className="w-5 text-center">👤</span><span className="flex-1">{t('nav.profile')}</span>
                        </Link>
                        <Link href="/logout" method="post" as="button" className="side-link">
                            <span aria-hidden="true" className="w-5 text-center">🚪</span><span className="flex-1">{t('nav.logout')}</span>
                        </Link>
                    </div>
                )}
            </nav>

            <div className="border-t border-slate-200/80 p-3">
                <button onClick={toggleLanguage} className="side-link" aria-label={t('nav.switch_language')}>
                    <span aria-hidden="true" className="w-5 text-center">🌐</span>
                    <span className="flex-1">{lang === 'en' ? 'العربية' : 'English'}</span>
                </button>
                {auth?.user ? (
                    <div className="mt-1 flex items-center gap-2.5 rounded-xl bg-slate-50 px-3 py-2">
                        <div aria-hidden="true" className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">
                            {auth.user.name.charAt(0)}
                        </div>
                        <div className="min-w-0">
                            <div className="truncate text-xs font-bold text-slate-900">{auth.user.name}</div>
                            <div className="truncate text-[10px] font-medium text-sky-700">
                                {isAdmin ? t('nav.administrator') : auth.company ? auth.company.name : t('nav.business_user')}
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="mt-1 grid grid-cols-2 gap-2">
                        <Link href="/login" className="btn-secondary !px-2">{t('nav.sign_in')}</Link>
                        <Link href="/register" className="btn-primary !px-2">{t('nav.join_short')}</Link>
                    </div>
                )}
            </div>
        </div>
    );

    return (
        <div className="tradeon-shell">
            {toast && (
                <div role="status" className="alert-info fixed left-1/2 top-3 z-[60] w-[calc(100%-2rem)] max-w-md -translate-x-1/2 shadow-lg">
                    <span className="flex-1">✨ {toast}</span>
                    <button onClick={() => setToast(null)} aria-label={t('common.dismiss')} className="font-bold">✕</button>
                </div>
            )}

            {/* Desktop sidebar */}
            <aside className="fixed inset-y-0 left-0 z-40 hidden w-60 border-r border-slate-200/80 bg-white lg:block rtl:left-auto rtl:right-0 rtl:border-l rtl:border-r-0">
                {sidebar}
            </aside>

            {/* Mobile drawer */}
            {mobileOpen && (
                <div className="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label={t('nav.menu')}>
                    <div className="absolute inset-0 bg-slate-900/40" onClick={() => setMobileOpen(false)} />
                    <aside className="absolute inset-y-0 left-0 w-72 max-w-[85vw] bg-white shadow-2xl rtl:left-auto rtl:right-0">
                        {sidebar}
                    </aside>
                </div>
            )}

            <div className="lg:pl-60 rtl:lg:pl-0 rtl:lg:pr-60">
                {/* Slim topbar */}
                <header className="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                    <div className="mx-auto flex h-14 max-w-5xl items-center gap-2 px-4">
                        <button className="tradeon-icon-btn lg:hidden" onClick={() => setMobileOpen(true)} aria-label={t('nav.menu')}>
                            <span aria-hidden="true" className="text-lg">☰</span>
                        </button>
                        <Link href="/feed" className="lg:hidden" aria-label="Tradeon home"><TradeonLogo compact /></Link>
                        <div className="flex-1" />
                        {auth?.user ? (
                            <>
                                <Link href="/smart-match" className="btn-secondary hidden !py-1.5 sm:inline-flex">
                                    <span aria-hidden="true">✨</span> {t('nav.smart_match')}
                                </Link>
                                <Link href="/messages" className="tradeon-icon-btn relative" aria-label={t('nav.messages')}>
                                    <span aria-hidden="true">💬</span>
                                </Link>
                                <Link href="/notifications" className="tradeon-icon-btn relative" aria-label={t('nav.notifications')}>
                                    <span aria-hidden="true">🔔</span>
                                    {unreadCount > 0 && (
                                        <span className="absolute right-0.5 top-0.5 min-w-4 rounded-full bg-rose-500 px-1 text-center text-[9px] font-bold leading-4 text-white">
                                            {unreadCount > 99 ? '99+' : unreadCount}
                                        </span>
                                    )}
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="btn-ghost !px-3">{t('nav.sign_in')}</Link>
                                <Link href="/register" className="btn-primary !px-3">{t('nav.join_short')}</Link>
                            </>
                        )}
                    </div>
                </header>

                <main className="mx-auto max-w-5xl px-4 py-6 pb-24 lg:pb-12">
                    {children}
                </main>
            </div>

            {/* Mobile bottom navigation */}
            <nav aria-label={t('nav.main_navigation')} className="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-slate-200 bg-white/95 px-2 py-1.5 backdrop-blur lg:hidden">
                {[
                    { key: 'nav.home', href: '/feed', icon: '🏠' },
                    { key: 'nav.explore', href: '/explore', icon: '🧭' },
                    { key: 'nav.requests', href: '/requests', icon: '📋' },
                    { key: 'nav.messages', href: '/messages', icon: '💬' },
                    { key: 'nav.my_network', href: '/network', icon: '📇' },
                ].map((item) => (
                    <Link
                        key={item.href}
                        href={item.href}
                        aria-current={isActive(item.href) ? 'page' : undefined}
                        className={`flex min-w-14 flex-col items-center gap-0.5 rounded-xl px-2 py-1.5 text-[10px] font-semibold ${isActive(item.href) ? 'text-sky-700' : 'text-slate-500'}`}
                    >
                        <span aria-hidden="true" className="text-lg leading-none">{item.icon}</span>
                        {t(item.key)}
                    </Link>
                ))}
            </nav>
        </div>
    );
}
