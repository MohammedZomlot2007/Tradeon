import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import EmptyState from '@/Components/Tradeon/EmptyState';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

const TYPE_KEYS = {
    identity: 'verification.identity',
    company: 'verification.company_v',
    factory: 'verification.factory',
};

const STATUS_STYLES = {
    pending: 'badge-amber',
    approved: 'badge-emerald',
    rejected: 'badge-rose',
};

export default function VerificationIndex({ company, verifications = [], options }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const form = useForm({ verification_type: 'company', document_type: '', document: null });

    const submit = (e) => {
        e.preventDefault();
        form.post('/verification', { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset('document', 'document_type') });
    };

    if (!company) {
        return (
            <TradeonLayout>
                <Head title={t('verification.title')} />
                <EmptyState icon="🛡️" title={t('verification.no_company_title')} description={t('verification.no_company_desc')} actionText={t('verification.no_company_cta')} onAction={() => router.visit('/onboarding/company')} />
            </TradeonLayout>
        );
    }

    return (
        <TradeonLayout>
            <Head title={t('verification.title')} />
            <div className="mx-auto max-w-4xl space-y-6">
                <div className="page-hero">
                    <h1 className="page-title">🛡️ {t('verification.title')}</h1>
                    <p className="page-subtitle">
                        {t('verification.subtitle')}
                    </p>
                    <div className="mt-3 text-xs font-semibold text-slate-700">
                        {t('verification.current_tier')}: <span className="badge-sky badge">{company.verified_tier || t('verification.tier_unverified')}</span>
                    </div>
                </div>

                <div className="tradeon-card p-6">
                    <h2 className="mb-4 text-sm font-bold text-slate-900">📤 {t('verification.submit_title')}</h2>
                    <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label className="tradeon-label">{t('verification.type')}</label>
                            <select value={form.data.verification_type} onChange={(e) => form.setData('verification_type', e.target.value)} className="tradeon-input px-3 py-2.5 text-xs">
                                {(options?.types || Object.keys(TYPE_KEYS)).map((type) => <option key={type} value={type}>{t(TYPE_KEYS[type] || 'verification.company_v')}</option>)}
                            </select>
                            {form.errors.verification_type && <p className="tradeon-error">{form.errors.verification_type}</p>}
                        </div>
                        <div>
                            <label className="tradeon-label">{t('verification.document_type')}</label>
                            <input value={form.data.document_type} onChange={(e) => form.setData('document_type', e.target.value)} placeholder={t('verification.document_type_ph')} className="tradeon-input px-3 py-2.5 text-xs" />
                            {form.errors.document_type && <p className="tradeon-error">{form.errors.document_type}</p>}
                        </div>
                        <div className="sm:col-span-2">
                            <label className="tradeon-label">{t('verification.file')}</label>
                            <input type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" onChange={(e) => form.setData('document', e.target.files[0] || null)} className="tradeon-input px-3 py-2.5 text-xs" />
                            {form.errors.document && <p className="tradeon-error">{form.errors.document}</p>}
                        </div>
                        <div className="sm:col-span-2">
                            <button disabled={form.processing} className="btn-primary">
                                {form.processing ? t('verification.uploading') : t('verification.submit')}
                            </button>
                        </div>
                    </form>
                </div>

                <div className="tradeon-card p-6">
                    <h2 className="mb-4 text-sm font-bold text-slate-900">📜 {t('verification.history')}</h2>
                    {verifications.length === 0 ? (
                        <div className="empty-state !py-8 text-xs text-slate-400">{t('verification.no_history')}</div>
                    ) : (
                        <div className="space-y-3">
                            {verifications.map((v) => (
                                <div key={v.id} className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div>
                                        <div className="text-sm font-bold text-slate-900">{t(TYPE_KEYS[v.verification_type] || 'verification.company_v')}</div>
                                        <div className="mt-0.5 text-xs text-slate-500">{v.document_type} · {new Date(v.created_at).toLocaleDateString()}</div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className={`badge ${STATUS_STYLES[v.status] || 'badge-slate'}`}>{v.status}</span>
                                        <a href={`/verification/${v.id}/document`} className="btn-secondary !py-1.5 !text-xs">{t('verification.view_document')}</a>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="text-center text-[11px] text-slate-400">
                    {t('verification.help')} <Link href="/messages" className="font-semibold text-sky-600">{t('verification.contact_support')}</Link>
                </div>
            </div>
        </TradeonLayout>
    );
}
