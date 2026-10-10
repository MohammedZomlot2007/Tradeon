import React from 'react';
import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function EmptyState({ icon = '🌐', title, description, actionText, onAction }) {
    const locale = usePage().props.locale || 'en';
    const t = (key) => translate(key, locale);
    const finalTitle = title || t('common.empty_title');
    const finalDesc = description || t('common.empty_desc');
    return (
        <div className="tradeon-card mx-auto my-6 max-w-md p-8 text-center">
            <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-3xl">{icon}</div>
            <h3 className="mb-1 text-base font-bold text-slate-900">{finalTitle}</h3>
            <p className="mb-5 text-xs leading-relaxed text-slate-500">{finalDesc}</p>
            {actionText && (
                <button
                    onClick={onAction}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-sky-600"
                >
                    {actionText}
                </button>
            )}
        </div>
    );
}
