import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';
import TradeonLogo from '@/Components/TradeonLogo';

export default function Landing() {
    const locale = usePage().props.locale || (typeof window !== 'undefined' && localStorage.getItem('tradeon_lang')) || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);

    const categories = ['🏭', '📦', '🚚', '🏷️', '🌐', '💼'];
    const features = [
        { icon: '🔍', title: t('landing.f_discovery'), desc: t('landing.f_discovery_desc') },
        { icon: '📋', title: t('landing.f_rfq'), desc: t('landing.f_rfq_desc') },
        { icon: '✨', title: t('landing.f_match'), desc: t('landing.f_match_desc') },
    ];
    const howItWorks = [
        { icon: '🏢', title: t('landing.how_1t'), desc: t('landing.how_1d') },
    ];

    return (
        <div className="min-h-screen bg-white font-sans text-slate-700 antialiased">
            <nav className="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
                <Link href="/" aria-label="Tradeon home"><TradeonLogo /></Link>
                <div className="flex items-center gap-2">
                    <Link href="/login" className="btn-ghost !px-3">{t('landing.sign_in')}</Link>
                    <Link href="/register" className="btn-primary !px-4">{t('landing.join')}</Link>
                </div>
            </nav>

            {/* Hero */}
            <section className="mx-auto max-w-6xl px-6 pb-16 pt-10 text-center sm:pt-16">
                <span className="badge-sky !px-4 !py-1.5 !text-xs">✨ {t('landing.badge')}</span>
                <h1 className="mx-auto mt-6 max-w-4xl text-4xl font-black tracking-tight text-slate-900 sm:text-6xl">
                    TRADEON
                    <span className="mt-2 block bg-gradient-to-r from-sky-600 to-cyan-500 bg-clip-text text-2xl text-transparent sm:text-4xl">
                        {t('landing.hero_a')} — {t('landing.hero_b')}
                    </span>
                </h1>
                <p className="mx-auto mt-5 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">{t('landing.hero_sub')}</p>
                <div className="mt-8 flex flex-wrap justify-center gap-3">
                    <Link href="/register" className="btn-primary px-7 py-3.5 text-sm">{t('landing.create_profile')}</Link>
                    <Link href="/explore" className="btn-secondary px-7 py-3.5 text-sm">{t('landing.explore')}</Link>
                </div>
            </section>

            {/* How it works */}
            <section className="border-y border-slate-200/70 bg-slate-50/70 py-14">
                <div className="mx-auto max-w-6xl px-6">
                    <h2 className="text-center text-xl font-extrabold text-slate-900 sm:text-2xl">{t('landing.how_title')}</h2>
                    <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            { n: '1', title: t('landing.how_1t'), desc: t('landing.how_1d') },
                            { n: '2', title: t('landing.how_2t'), desc: t('landing.how_2d') },
                            { n: '3', title: t('landing.how_3t'), desc: t('landing.how_3d') },
                            { n: '4', title: t('landing.how_4t'), desc: t('landing.how_4d') },
                        ].map((s) => (
                            <div key={s.n} className="tradeon-card p-5">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-sm font-black text-white">{s.n}</div>
                                <h3 className="mt-3 text-sm font-bold text-slate-900">{s.title}</h3>
                                <p className="mt-1 text-xs leading-5 text-slate-500">{s.desc}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Features */}
            <section className="mx-auto max-w-6xl px-6 py-14">
                <div className="mb-8 text-center">
                    <h2 className="text-xl font-extrabold text-slate-900 sm:text-2xl">{t('landing.built_title')}</h2>
                    <p className="mt-1 text-xs text-slate-500 sm:text-sm">{t('landing.built_sub')}</p>
                </div>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    {features.map((f) => (
                        <div key={f.title} className="tradeon-card p-6">
                            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-2xl" aria-hidden="true">{f.icon}</div>
                            <h3 className="mb-1.5 mt-4 text-base font-bold text-slate-900">{f.title}</h3>
                            <p className="text-xs leading-5 text-slate-500">{f.desc}</p>
                        </div>
                    ))}
                </div>
                <div className="mt-8 flex flex-wrap justify-center gap-2">
                    {categories.map((c, i) => (
                        <span key={i} aria-hidden="true" className="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200/80 bg-white text-xl">{c}</span>
                    ))}
                </div>
            </section>

            {/* CTA */}
            <section className="mx-auto max-w-5xl px-6 pb-16">
                <div className="rounded-3xl bg-slate-900 p-8 text-center sm:p-12">
                    <h2 className="text-xl font-extrabold text-white sm:text-3xl">{t('landing.cta_title')}</h2>
                    <p className="mx-auto mt-2 max-w-xl text-xs text-slate-300 sm:text-sm">{t('landing.cta_sub')}</p>
                    <Link href="/register" className="mt-6 inline-block rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-slate-900 transition hover:bg-sky-50">
                        {t('landing.cta_btn')}
                    </Link>
                </div>
            </section>

            <footer className="border-t border-slate-200/70 py-8">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-6 sm:flex-row">
                    <TradeonLogo compact />
                    <p className="text-[11px] text-slate-400">{t('landing.footer')}</p>
                </div>
            </footer>
        </div>
    );
}
