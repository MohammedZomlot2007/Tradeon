import React, { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function CreatePostModal({ isOpen, onClose }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [type, setType] = useState('business_update');
    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [country, setCountry] = useState('');
    const [industry, setIndustry] = useState('');

    if (!isOpen) return null;

    const postTypes = [
        { id: 'business_update', key: 'composer.types.business_update' },
        { id: 'product_launch', key: 'composer.types.product_launch' },
        { id: 'factory_update', key: 'composer.types.factory_update' },
        { id: 'manufacturing_process', key: 'composer.types.manufacturing_process' },
        { id: 'looking_for_supplier', key: 'composer.types.looking_for_supplier' },
        { id: 'looking_for_manufacturer', key: 'composer.types.looking_for_manufacturer' },
        { id: 'rfq_sourcing', key: 'composer.types.rfq_sourcing' },
        { id: 'partnership_request', key: 'composer.types.partnership_request' },
    ];

    const handleSubmit = (e) => {
        e.preventDefault();
        router.post('/posts', { type, title, content, country, industry }, {
            onSuccess: () => {
                onClose();
                setTitle('');
                setContent('');
            }
        });
    };

    const field = 'tradeon-input px-3 py-2 text-xs';

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/30 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label={t('composer.title')}>
            <div className="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                <button onClick={onClose} aria-label={t('common.dismiss')} className="absolute right-4 top-4 text-lg text-slate-400 hover:text-slate-900">✕</button>

                <h2 className="mb-4 flex items-center gap-2 text-lg font-bold text-slate-900">
                    <span aria-hidden="true">📢</span>
                    <span>{t('composer.title')}</span>
                </h2>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="tradeon-label">{t('composer.post_type')}</label>
                        <select value={type} onChange={(e) => setType(e.target.value)} className={field}>
                            {postTypes.map((pt) => (
                                <option key={pt.id} value={pt.id}>{t(pt.key)}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="tradeon-label">{t('composer.title_label')}</label>
                        <input
                            type="text"
                            placeholder={t('composer.title_ph')}
                            className={field}
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                        />
                    </div>

                    <div>
                        <label className="tradeon-label">{t('composer.details')}</label>
                        <textarea
                            rows="4"
                            placeholder={t('composer.details_ph')}
                            className={`${field} p-3`}
                            value={content}
                            onChange={(e) => setContent(e.target.value)}
                            required
                        ></textarea>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="tradeon-label">{t('composer.country')}</label>
                            <input
                                type="text"
                                placeholder={t('composer.country_ph')}
                                className={field}
                                value={country}
                                onChange={(e) => setCountry(e.target.value)}
                            />
                        </div>
                        <div>
                            <label className="tradeon-label">{t('composer.industry')}</label>
                            <input
                                type="text"
                                placeholder={t('composer.industry_ph')}
                                className={field}
                                value={industry}
                                onChange={(e) => setIndustry(e.target.value)}
                            />
                        </div>
                    </div>

                    <div className="flex justify-end gap-2 pt-3">
                        <button type="button" onClick={onClose} className="btn-secondary">
                            {t('common.cancel')}
                        </button>
                        <button type="submit" className="btn-primary">
                            {t('composer.publish')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
