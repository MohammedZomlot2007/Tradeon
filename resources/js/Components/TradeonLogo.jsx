import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function TradeonLogo({ compact = false }) {
    let locale = 'en';
    try {
        locale = usePage().props.locale || 'en';
    } catch {
        locale = 'en';
    }
    const t = (key) => translate(key, locale);
    return (
        <span className="flex items-center gap-2.5">
            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-lg font-black text-white">
                T
            </span>
            {!compact && (
                <span className="leading-tight">
                    <span className="block text-[15px] font-black tracking-[0.18em] text-slate-900">TRADEON</span>
                    <span className="block text-[9px] font-semibold uppercase tracking-[0.22em] text-slate-400">{t('brand.tagline')}</span>
                </span>
            )}
        </span>
    );
}
