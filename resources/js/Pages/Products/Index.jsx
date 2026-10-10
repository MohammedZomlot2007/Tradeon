import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';
import { useState } from 'react';

export default function Index({ products, filters, categories }) {
    const { flash, locale } = usePage().props;
    const t = (key, replacements) => translate(key, locale || 'en', replacements);
    const [search, setSearch] = useState(filters.search || '');
    const [category, setCategory] = useState(filters.category || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(
            route('products.index'),
            { search, category, status },
            { preserveState: true, replace: true }
        );
    };

    const handleReset = () => {
        setSearch('');
        setCategory('');
        setStatus('');
        router.get(route('products.index'));
    };

    const handleDelete = (id, name) => {
        if (confirm(`${t('products.delete_confirm')} "${name}"?`)) {
            router.delete(route('products.destroy', id));
        }
    };

    const statusBadge = (s) => {
        switch (s) {
            case 'active': return 'badge-emerald';
            case 'draft': return 'badge-amber';
            case 'archived': return 'badge-slate';
            default: return 'badge-slate';
        }
    };

    return (
        <TradeonLayout>
            <Head title={t('products.catalog')} />

            <div className="space-y-5">
                <div className="page-hero flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="page-title">📦 {t('products.catalog')}</h1>
                    </div>
                    <Link href={route('products.create')} className="btn-primary">
                        + {t('products.add')}
                    </Link>
                </div>

                {flash?.success && (
                    <div className="alert-success" role="status">
                        <span>{flash.success}</span>
                    </div>
                )}

                <div className="tradeon-card p-4 sm:p-5">
                    <form onSubmit={handleSearch} className="grid grid-cols-1 gap-3 sm:grid-cols-4" role="search">
                        <div>
                            <label className="tradeon-label">{t('products.search')}</label>
                            <input
                                type="text"
                                placeholder={t('products.search_ph')}
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="tradeon-input"
                            />
                        </div>

                        <div>
                            <label className="tradeon-label">{t('products.category')}</label>
                            <select value={category} onChange={(e) => setCategory(e.target.value)} className="tradeon-input">
                                <option value="">{t('products.all_categories')}</option>
                                {categories?.map((c) => (
                                    <option key={c} value={c}>{c}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="tradeon-label">{t('products.status')}</label>
                            <select value={status} onChange={(e) => setStatus(e.target.value)} className="tradeon-input">
                                <option value="">{t('products.all_statuses')}</option>
                                <option value="active">{t('products.status_active')}</option>
                                <option value="draft">{t('products.status_draft')}</option>
                                <option value="archived">{t('products.status_archived')}</option>
                            </select>
                        </div>

                        <div className="flex items-end gap-2">
                            <button type="submit" className="btn-primary flex-1">
                                {t('products.filter')}
                            </button>
                            <button type="button" onClick={handleReset} className="btn-secondary">
                                {t('products.reset')}
                            </button>
                        </div>
                    </form>
                </div>

                <div className="tradeon-card overflow-hidden">
                    <div className="tradeon-table-wrap !rounded-none !border-0">
                        <table className="tradeon-table">
                            <thead>
                                <tr>
                                    <th>{t('products.product')}</th>
                                    <th>SKU</th>
                                    <th>{t('products.category')}</th>
                                    <th>{t('products.price')}</th>
                                    <th>{t('products.stock')}</th>
                                    <th>{t('products.status')}</th>
                                    <th className="text-right rtl:text-left">{t('products.actions')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.data?.length > 0 ? (
                                    products.data.map((product) => (
                                        <tr key={product.id}>
                                            <td className="font-semibold text-slate-900">{product.name}</td>
                                            <td className="font-mono text-[11px] text-slate-400">{product.sku}</td>
                                            <td><span className="badge-slate badge">{product.category}</span></td>
                                            <td className="font-bold text-slate-900">${parseFloat(product.price).toFixed(2)}</td>
                                            <td>{product.stock}</td>
                                            <td><span className={`badge capitalize ${statusBadge(product.status)}`}>{product.status}</span></td>
                                            <td className="whitespace-nowrap text-right rtl:text-left">
                                                <Link href={route('products.show', product.id)} className="mr-3 text-xs font-semibold text-slate-500 hover:text-sky-700 rtl:ml-3 rtl:mr-0">{t('products.view')}</Link>
                                                <Link href={route('products.edit', product.id)} className="mr-3 text-xs font-semibold text-sky-600 hover:underline rtl:ml-3 rtl:mr-0">{t('products.edit')}</Link>
                                                <button onClick={() => handleDelete(product.id, product.name)} className="text-xs font-semibold text-rose-600 hover:underline">{t('products.delete')}</button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="7" className="px-6 py-12 text-center text-slate-400">
                                            {t('products.no_products')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {products.links?.length > 3 && (
                        <div className="flex justify-center gap-1 border-t border-slate-100 p-4">
                            {products.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url || '#'}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    className={`rounded-lg px-3 py-1.5 text-xs ${link.active ? 'bg-sky-600 font-bold text-white' : link.url ? 'border border-slate-200 bg-white text-slate-600 hover:border-sky-200' : 'cursor-not-allowed text-slate-300'}`}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </TradeonLayout>
    );
}
