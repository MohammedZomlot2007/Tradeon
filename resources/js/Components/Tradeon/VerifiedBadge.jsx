import React from 'react';
import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function VerifiedBadge({ tier = 'company_verified' }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);

    if (!tier || tier === 'unverified') return null;

    const badges = {
        identity_verified: { key: 'badges.identity', icon: '👤', color: 'border-blue-200 bg-blue-50 text-blue-700' },
        company_verified: { key: 'badges.company', icon: '✓', color: 'border-sky-200 bg-sky-50 text-sky-700' },
        factory_verified: { key: 'badges.factory', icon: '🏭', color: 'border-emerald-200 bg-emerald-50 text-emerald-700' },
    };

    const config = badges[tier] || badges.company_verified;

    return (
        <span className={`inline-flex shrink-0 items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold ${config.color}`}>
            <span aria-hidden="true">{config.icon}</span>
            <span>{t(config.key)}</span>
        </span>
    );
}
