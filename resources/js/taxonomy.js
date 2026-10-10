import { translate } from '@/i18n';

/**
 * Display-only taxonomy translation.
 * Database values stay in English (stable for filtering/search),
 * only the rendered label is localized.
 */
export function taxonomyLabel(group, value, locale = 'en') {
    if (!value) return value;
    const key = `taxonomy.${group}.${value}`;
    const translated = translate(key, locale);
    // translate() returns the key itself when missing -> fall back to raw value
    return translated === key ? value : translated;
}

export function businessTypeLabel(value, locale) {
    return taxonomyLabel('business_types', value, locale);
}

export function industryLabel(value, locale) {
    return taxonomyLabel('industries', value, locale);
}

export function countryLabel(value, locale) {
    return taxonomyLabel('countries', value, locale);
}
