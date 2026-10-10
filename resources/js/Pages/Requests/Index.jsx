import React, { useState } from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import EmptyState from '@/Components/Tradeon/EmptyState';
import { router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function RequestsIndex({ requests, rfqs }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [selectedRfq, setSelectedRfq] = useState(null);
    const [unitPrice, setUnitPrice] = useState('');
    const [leadTime, setLeadTime] = useState('');
    const [quoteNotes, setQuoteNotes] = useState('');

    const handleQuoteSubmit = (e) => {
        e.preventDefault();
        if (!selectedRfq) return;
        router.post(`/rfq/${selectedRfq.id}/quote`, {
            unit_price: unitPrice,
            lead_time: leadTime,
            quote_notes: quoteNotes,
        }, {
            onSuccess: () => {
                setSelectedRfq(null);
                setUnitPrice('');
                setLeadTime('');
                setQuoteNotes('');
            }
        });
    };

    return (
        <TradeonLayout>
            <div className="space-y-8">

                {/* Header Pitch */}
                <div className="page-hero">
                    <h1 className="page-title">📋 {t('requests.title')}</h1>
                    <p className="page-subtitle">{t('requests.subtitle')}</p>
                </div>

                {/* RFQs Section */}
                <div className="space-y-4">
                    <h2 className="flex items-center space-x-2 text-lg font-extrabold text-slate-900">
                        <span>📑</span>
                        <span>{t('requests.active_title')}</span>
                    </h2>

                    {rfqs && rfqs.length > 0 ? (
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {rfqs.map((rfq) => (
                                <div key={rfq.id} className="tradeon-card p-5 transition hover:-translate-y-[1px]">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <h3 className="text-sm font-bold text-slate-900">{rfq.product_name}</h3>
                                            <p className="mt-0.5 text-xs text-slate-500">{t('requests.delivery')}: 📍 {rfq.delivery_country}</p>
                                        </div>
                                        <span className="shrink-0 rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[10px] font-bold text-sky-700">
                                            {rfq.quantity}
                                        </span>
                                    </div>

                                    <p className="mt-3 line-clamp-3 rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                                        {rfq.specifications}
                                    </p>

                                    <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                        <div className="text-[11px] text-slate-500">
                                            {t('requests.quotes')}: <strong className="text-slate-900">{rfq.quotes?.length || 0}</strong>
                                        </div>
                                        <button
                                            onClick={() => setSelectedRfq(rfq)}
                                            className="btn-primary !py-1.5"
                                        >
                                            {t('requests.send_quotation')}
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <EmptyState icon="📑" title={t('requests.no_rfqs')} description={t('requests.no_rfqs_desc')} />
                    )}
                </div>

                {/* Submit Quotation Modal */}
                {selectedRfq && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm">
                        <div className="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                            <button onClick={() => setSelectedRfq(null)} aria-label={t('common.dismiss')} className="absolute top-4 right-4 text-slate-400 hover:text-slate-900">✕</button>
                            <h3 className="mb-2 text-base font-bold text-slate-900">{t('requests.quote_for')} {selectedRfq.product_name}</h3>

                            <form onSubmit={handleQuoteSubmit} className="space-y-4 text-xs">
                                <div>
                                    <label className="tradeon-label">{t('requests.unit_price')}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        placeholder="e.g. 12.50"
                                        className="tradeon-input p-2.5"
                                        value={unitPrice}
                                        onChange={(e) => setUnitPrice(e.target.value)}
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="tradeon-label">{t('requests.lead_time')}</label>
                                    <input
                                        type="text"
                                        placeholder={t('requests.lead_time_ph')}
                                        className="tradeon-input p-2.5"
                                        value={leadTime}
                                        onChange={(e) => setLeadTime(e.target.value)}
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="tradeon-label">{t('requests.notes')}</label>
                                    <textarea
                                        rows="3"
                                        placeholder={t('requests.notes_ph')}
                                        className="tradeon-input p-2.5"
                                        value={quoteNotes}
                                        onChange={(e) => setQuoteNotes(e.target.value)}
                                        required
                                    ></textarea>
                                </div>
                                <div className="flex justify-end gap-2 pt-2">
                                    <button type="button" onClick={() => setSelectedRfq(null)} className="btn-secondary">{t('common.cancel')}</button>
                                    <button type="submit" className="btn-primary">{t('requests.submit_quote')}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

            </div>
        </TradeonLayout>
    );
}
