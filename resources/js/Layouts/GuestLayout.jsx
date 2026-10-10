import TradeonLogo from '@/Components/TradeonLogo';
import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { translate } from '@/i18n';

export default function GuestLayout({ children }) {
    const serverLocale = usePage().props.locale || 'en';
    const [lang, setLang] = useState(serverLocale);

    useEffect(() => {
        if (serverLocale) setLang(serverLocale);
    }, [serverLocale]);

    useEffect(() => {
        if (typeof document !== 'undefined') {
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            document.documentElement.lang = lang;
        }
    }, [lang]);

    const t = (key, replacements) => translate(key, lang, replacements);

    const toggleLanguage = () => {
        const next = lang === 'en' ? 'ar' : 'en';
        setLang(next);
        try {
            localStorage.setItem('tradeon_lang', next);
            document.cookie = `tradeon_locale=${next}; path=/; max-age=31536000`;
        } catch { /* ignore */ }
        router.post('/locale', { locale: next }, { preserveScroll: true, preserveState: true });
    };

    return (
        <div className="flex min-h-screen flex-col items-center bg-slate-100 pt-6 sm:justify-center sm:pt-0">
            <div>
                <Link href="/" aria-label="Tradeon home">
                    <TradeonLogo />
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                {children}
            </div>

            <button
                onClick={toggleLanguage}
                aria-label={t('nav.switch_language')}
                className="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 shadow-sm transition hover:border-sky-200 hover:text-sky-700"
            >
                🌐 {lang === 'en' ? 'العربية' : 'English'}
            </button>
        </div>
    );
}
