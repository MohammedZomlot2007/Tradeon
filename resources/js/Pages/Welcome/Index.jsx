import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function WelcomeIndex({ company, progress }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);

    const steps = [
        { label: t('welcome.step_profile'), done: progress.profile >= 80, hint: `${progress.profile}%` },
        { label: t('welcome.step_verification'), done: progress.verified, hint: progress.verification_status || t('welcome.not_started') },
        { label: t('welcome.step_products'), done: progress.products > 0, hint: `${progress.products}` },
        { label: t('welcome.step_network'), done: progress.network > 0, hint: `${progress.network}` },
        { label: t('welcome.step_rfq'), done: progress.rfqs + progress.requests > 0, hint: `${progress.rfqs + progress.requests}` },
    ];
    const doneCount = steps.filter((s) => s.done).length;

    const actions = [
        { icon: '🏢', label: t('welcome.action_profile'), href: '/biz-dashboard' },
        { icon: '🛡️', label: t('welcome.action_verify'), href: '/verification' },
        { icon: '📦', label: t('welcome.action_product'), href: `/company/${company.slug}` },
        { icon: '🏭', label: t('welcome.action_manufacturers'), href: '/manufacturers' },
        { icon: '📦', label: t('welcome.action_suppliers'), href: '/suppliers' },
        { icon: '📋', label: t('welcome.action_rfq'), href: '/requests' },
    ];

    return (
        <TradeonLayout>
            <Head title={t('welcome.title')} />
            <div className="mx-auto max-w-3xl space-y-6">
                <div className="page-hero text-center">
                    <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-600 text-2xl text-white">🎉</div>
                    <p className="text-xs font-bold uppercase tracking-[0.18em] text-sky-600">{t('welcome.step_done')}</p>
                    <h1 className="page-title mt-1 !text-2xl">{t('welcome.headline')}</h1>
                    <p className="page-subtitle mx-auto mt-2 max-w-md">{t('welcome.subheadline', { company: company.name })}</p>
                    <div className="mx-auto mt-4 h-2.5 w-full max-w-sm overflow-hidden rounded-full bg-slate-100">
                        <div className="h-full rounded-full bg-sky-500 transition-all" style={{ width: `${(doneCount / steps.length) * 100}%` }} />
                    </div>
                    <p className="mt-1 text-[11px] font-semibold text-slate-500">{doneCount}/{steps.length} {t('welcome.complete')}</p>
                </div>

                <div className="tradeon-card divide-y divide-slate-100">
                    {steps.map((step) => (
                        <div key={step.label} className="flex items-center gap-3 p-4">
                            <span aria-hidden="true" className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm ${step.done ? 'bg-emerald-100' : 'bg-slate-100'}`}>
                                {step.done ? '✅' : '⭕'}
                            </span>
                            <span className="flex-1 text-sm font-semibold text-slate-800">{step.label}</span>
                            <span className="text-xs text-slate-400">{step.hint}</span>
                        </div>
                    ))}
                </div>

                <div>
                    <h2 className="mb-3 text-sm font-bold text-slate-900">{t('welcome.next_steps')}</h2>
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        {actions.map((action) => (
                            <Link key={action.label + action.href} href={action.href} className="tradeon-card group flex items-center gap-3 p-4 transition hover:-translate-y-px">
                                <span aria-hidden="true" className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-xl">{action.icon}</span>
                                <span className="text-xs font-bold text-slate-800 group-hover:text-sky-700">{action.label}</span>
                            </Link>
                        ))}
                    </div>
                </div>

                <div className="text-center">
                    <Link href="/feed" className="btn-primary px-8 py-3 text-sm">{t('welcome.continue')} →</Link>
                </div>
            </div>
        </TradeonLayout>
    );
}
