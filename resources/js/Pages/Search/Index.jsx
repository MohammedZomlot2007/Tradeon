import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

const TABS = [
    { id: 'all', key: 'search.everything' },
    { id: 'companies', key: 'search.companies' },
    { id: 'manufacturers', key: 'search.manufacturers' },
    { id: 'products', key: 'search.products' },
    { id: 'requests', key: 'search.requests' },
];

export default function Index({ results, filters = {}, query = '' }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const form = useForm({
        q: query,
        type: filters.type || 'all',
        country: filters.country || '',
        industry: filters.industry || '',
        verified: !!filters.verified,
    });
    const submit = (e) => {
        e.preventDefault();
        form.transform((data) => ({ ...data, verified: data.verified ? true : undefined }))
            .get('/search', { preserveState: true, replace: true });
    };
    const setType = (type) => {
        form.setData('type', type);
        form.transform((data) => ({ ...data, type, verified: data.verified ? true : undefined }))
            .get('/search', { preserveState: true, replace: true });
    };

    return (
        <TradeonLayout>
            <Head title={t('search.title')} />
            <div className="mx-auto max-w-5xl space-y-5">
                <div className="page-hero">
                    <h1 className="page-title">🔍 {t('search.title')}</h1>
                    <p className="page-subtitle">{t('search.subtitle')}</p>
                    <form onSubmit={submit} className="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        <input
                            className="tradeon-input px-3 py-2.5 text-xs lg:col-span-2"
                            value={form.data.q}
                            onChange={(e) => form.setData('q', e.target.value)}
                            placeholder={t('search.placeholder')}
                            aria-label={t('search.placeholder')}
                        />
                        <input
                            className="tradeon-input px-3 py-2.5 text-xs"
                            value={form.data.country}
                            onChange={(e) => form.setData('country', e.target.value)}
                            placeholder={t('search.country')}
                            aria-label={t('search.country')}
                        />
                        <input
                            className="tradeon-input px-3 py-2.5 text-xs"
                            value={form.data.industry}
                            onChange={(e) => form.setData('industry', e.target.value)}
                            placeholder={t('search.industry')}
                            aria-label={t('search.industry')}
                        />
                    </form>
                    <div className="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <label className="flex cursor-pointer items-center gap-2 text-xs text-slate-600">
                            <input type="checkbox" checked={!!form.data.verified} onChange={(e) => form.setData('verified', e.target.checked)} className="rounded border-slate-300 text-sky-600" />
                            {t('search.verified_only')}
                        </label>
                        <button onClick={submit} disabled={form.processing} className="btn-primary">
                            {t('search.search_btn')}
                        </button>
                    </div>
                </div>

                <div className="flex gap-1 overflow-x-auto border-b border-slate-200" role="tablist" aria-label={t('search.title')}>
                    {TABS.map((tab) => (
                        <button
                            key={tab.id}
                            role="tab"
                            aria-selected={form.data.type === tab.id}
                            onClick={() => setType(tab.id)}
                            className={`whitespace-nowrap border-b-2 px-4 py-2.5 text-xs font-bold transition ${form.data.type === tab.id ? 'border-sky-500 text-sky-700' : 'border-transparent text-slate-500 hover:text-slate-800'}`}
                        >
                            {t(tab.key)}
                        </button>
                    ))}
                </div>

                <section className="space-y-6">
                    {results.companies?.data?.length > 0 && <ResultGroup title={t('search.companies')} items={results.companies.data} render={(c) => <Link href={`/company/${c.slug}`} className="block rounded-xl border border-slate-200/80 bg-white p-4 transition hover:border-sky-200"><div className="text-sm font-bold text-slate-900">{c.name}</div><div className="mt-0.5 text-xs text-slate-500">{c.industry} · {c.city}, {c.country}</div></Link>} />}
                    {results.products?.data?.length > 0 && <ResultGroup title={t('search.products')} items={results.products.data} render={(p) => <div className="rounded-xl border border-slate-200/80 bg-white p-4"><div className="text-sm font-bold text-slate-900">{p.title}</div><div className="mt-0.5 text-xs text-slate-500">{p.industry} · {p.company?.name}</div></div>} />}
                    {results.requests?.data?.length > 0 && <ResultGroup title={t('search.business_requests')} items={results.requests.data} render={(r) => <div className="rounded-xl border border-slate-200/80 bg-white p-4"><div className="text-sm font-bold text-slate-900">{r.title}</div><div className="mt-0.5 text-xs text-slate-500">{r.industry} · {r.target_country || 'Global'}</div></div>} />}
                    {!results.companies?.data?.length && !results.products?.data?.length && !results.requests?.data?.length && (
                        <div className="empty-state"><p className="text-sm text-slate-500">{t('common.no_results')}</p></div>
                    )}
                </section>
            </div>
        </TradeonLayout>
    );
}

function ResultGroup({ title, items, render }) {
    return (
        <div className="space-y-3">
            <h2 className="text-sm font-bold text-slate-900">{title}</h2>
            <div className="grid gap-3 md:grid-cols-3">{items.map((item) => <div key={item.id}>{render(item)}</div>)}</div>
        </div>
    );
}
