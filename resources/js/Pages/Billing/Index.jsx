import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import TradeonLayout from '../../Layouts/TradeonLayout';
import { translate } from '@/i18n';

export default function BillingIndex({ subscription, invoices = [], plans = [], storage = null }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const percent = storage ? Math.min(100, Math.max(0, storage.percent || 0)) : 0;
    return (
        <TradeonLayout>
            <Head title={t('billing.title')} />
            <div className="mx-auto max-w-5xl space-y-6">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div><p className="text-xs font-bold uppercase tracking-[0.16em] text-sky-600">{t('billing.account')}</p><h1 className="page-title mt-1 !text-2xl sm:!text-3xl">{t('billing.title')}</h1></div>
                    <Link href="/pricing" className="btn-primary">{t('billing.change_plan')}</Link>
                </div>
                <div className="tradeon-card p-6">
                    {subscription ? <div className="grid gap-4 md:grid-cols-4"><div><div className="text-xs text-slate-500">{t('billing.plan')}</div><div className="mt-1 font-bold text-slate-900">{subscription.plan?.name}</div></div><div><div className="text-xs text-slate-500">{t('billing.status')}</div><div className="mt-1 font-bold text-emerald-600">{subscription.status}</div></div><div><div className="text-xs text-slate-500">{t('billing.interval')}</div><div className="mt-1 font-bold text-slate-900">{subscription.billing_interval}</div></div><div><div className="text-xs text-slate-500">{t('billing.renews')}</div><div className="mt-1 font-bold text-slate-900">{subscription.renews_at ? new Date(subscription.renews_at).toLocaleDateString() : '—'}</div></div></div> : <div className="py-4 text-center text-sm text-slate-500">{t('billing.no_subscription')} <Link href="/pricing" className="font-bold text-sky-600">{t('billing.view_plans')}</Link></div>}
                </div>
                <div className="tradeon-card p-6">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h2 className="font-bold text-slate-900">💾 {t('billing.storage_title')}</h2>
                        {storage && !storage.unlimited && <Link href="/pricing" className="btn-secondary !px-3 !py-1.5 text-xs">{t('billing.upgrade_for_storage')}</Link>}
                    </div>
                    {!storage || !storage.has_company ? (
                        <div className="mt-3 text-sm text-slate-500">{t('billing.storage_no_company')}</div>
                    ) : storage.unlimited ? (
                        <div className="mt-3 text-sm font-semibold text-emerald-600">{t('billing.storage_unlimited')} ({storage.used_mb} MB)</div>
                    ) : (
                        <div className="mt-3">
                            <div className="text-sm text-slate-600">{t('billing.storage_used', { used: storage.used_mb, quota: storage.quota_mb })}</div>
                            <div className="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div className={`h-full rounded-full ${percent >= 90 ? 'bg-rose-500' : 'bg-sky-500'}`} style={{ width: `${percent}%` }} />
                            </div>
                        </div>
                    )}
                </div>
                <div className="tradeon-card overflow-hidden">
                    <div className="border-b border-slate-100 px-6 py-4"><h2 className="font-bold text-slate-900">🧾 {t('billing.invoices')}</h2></div>
                    {invoices.length === 0 ? <div className="px-6 py-10 text-center text-sm text-slate-400">{t('billing.no_invoices')}</div> : <div className="divide-y divide-slate-100">{invoices.map((invoice) => <div key={invoice.id} className="grid gap-2 px-6 py-4 md:grid-cols-4"><div className="font-semibold text-slate-900">{invoice.invoice_number}</div><div className="text-slate-500">{invoice.currency} {invoice.total}</div><div className="text-slate-500">{invoice.status}</div><div className="text-slate-400">{invoice.issued_at ? new Date(invoice.issued_at).toLocaleDateString() : '—'}</div></div>)}</div>}
                </div>
                <div className="text-center text-xs text-slate-400">{t('billing.webhook_note')}</div>
            </div>
        </TradeonLayout>
    );
}
