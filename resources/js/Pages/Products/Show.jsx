import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function Show({ product }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <Head title={`${product.name} - TRADEON`} />

            <div className="mx-auto max-w-4xl space-y-5">
                <div className="page-hero flex flex-wrap items-center justify-between gap-3">
                    <h1 className="page-title">📦 {t('products.details')}</h1>
                    <div className="flex gap-2">
                        <Link href={route('products.edit', product.id)} className="btn-primary">
                            {t('products.edit')}
                        </Link>
                        <Link href={route('products.index')} className="btn-secondary">
                            {t('products.back')}
                        </Link>
                    </div>
                </div>

                <div className="tradeon-card space-y-6 p-6 sm:p-8">
                    <div className="border-b border-slate-100 pb-4">
                        <span className="badge-sky">{product.category}</span>
                        <h2 className="mt-2 text-2xl font-extrabold text-slate-900">
                            {product.name}
                        </h2>
                        <p className="mt-1 font-mono text-xs text-slate-400">SKU: {product.sku}</p>
                    </div>

                    <div className="grid grid-cols-1 gap-4 py-2 sm:grid-cols-3">
                        <div className="tradeon-soft p-4">
                            <span className="block text-xs font-medium text-slate-500">{t('products.price')}</span>
                            <span className="text-2xl font-extrabold text-slate-900">
                                ${parseFloat(product.price).toFixed(2)}
                            </span>
                        </div>

                        <div className="tradeon-soft p-4">
                            <span className="block text-xs font-medium text-slate-500">{t('products.stock')}</span>
                            <span className="text-2xl font-extrabold text-slate-900">
                                {product.stock} {t('products.units')}
                            </span>
                        </div>

                        <div className="tradeon-soft p-4">
                            <span className="block text-xs font-medium text-slate-500">{t('products.status')}</span>
                            <span className="text-xl font-bold capitalize text-emerald-600">
                                {product.status}
                            </span>
                        </div>
                    </div>

                    {product.description && (
                        <div>
                            <h3 className="mb-2 text-sm font-semibold text-slate-700">
                                {t('products.description')}:
                            </h3>
                            <p className="whitespace-pre-line rounded-xl bg-slate-50 p-4 text-sm leading-relaxed text-slate-600">
                                {product.description}
                            </p>
                        </div>
                    )}

                    <div className="flex justify-between border-t border-slate-100 pt-4 text-xs text-slate-400">
                        <span>{t('products.created_by')}: {product.user?.name || t('products.owner_fallback')}</span>
                        <span>{t('products.created_on')}: {new Date(product.created_at).toLocaleDateString()}</span>
                    </div>
                </div>
            </div>
        </TradeonLayout>
    );
}
