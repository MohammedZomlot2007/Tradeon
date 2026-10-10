import en from '../../lang/en.json';
import ar from '../../lang/ar.json';

const dictionaries = { en, ar };

const SUPPORTED = ['en', 'ar'];

export function normalizeLocale(locale) {
    if (!locale || typeof locale !== 'string') return 'en';
    const short = locale.toLowerCase().split(/[-_]/)[0];
    return SUPPORTED.includes(short) ? short : 'en';
}

/**
 * Translate a dotted key (e.g. "nav.home") using the current locale.
 * Falls back to English, then to the key itself so the UI never breaks
 * when a translation is missing.
 *
 * Supports both `:name` (Laravel style) and `{name}` placeholders,
 * and replaces ALL occurrences (not just the first).
 *
 * Usage in any React component:
 *   import { translate } from '@/i18n';
 *   const locale = usePage().props.locale || 'en';
 *   const t = (key, replacements) => translate(key, locale, replacements);
 *   <span>{t('nav.home')}</span>
 *   <span>{t('company.no_section', { tab: t('company.tab_services') })}</span>
 */
export function translate(key, locale = 'en', replacements = {}) {
    const lang = normalizeLocale(locale);
    const lookup = (dict) => key.split('.').reduce((acc, part) => {
        if (acc && typeof acc === 'object' && part in acc) return acc[part];
        return undefined;
    }, dict);

    let text = lookup(dictionaries[lang]) ?? lookup(dictionaries.en) ?? key;

    if (typeof text !== 'string') return key;

    Object.entries(replacements || {}).forEach(([name, value]) => {
        const str = String(value ?? '');
        // Replace :name, {name} and {{name}} variants globally
        text = text.split(`:${name}`).join(str);
        text = text.split(`{${name}}`).join(str);
        text = text.split(`{{${name}}}`).join(str);
    });

    return text;
}

export function localeDirection(locale) {
    return locale === 'ar' ? 'rtl' : 'ltr';
}
