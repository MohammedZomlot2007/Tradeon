import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function Create() {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        sku: '',
        category: '',
        price: '',
        stock: '0',
        status: 'active',
        description: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('products.store'));
    };

    const req = <span aria-hidden="true" className="text-rose-500">*</span>;

    return (
        <TradeonLayout>
            <Head title={t('products.add')} />

            <div className="mx-auto max-w-3xl">
                <div className="page-hero mb-5">
                    <h1 className="page-title">＋ {t('products.add')}</h1>
                </div>
                <div className="tradeon-card p-6 sm:p-8">
                    <form onSubmit={handleSubmit} className="space-y-5">
                        <div>
                            <label className="tradeon-label" htmlFor="name">{t('products.name')} {req}</label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="tradeon-input"
                                placeholder={t('products.name_ph')}
                                required
                            />
                            {errors.name && <p className="tradeon-error">{errors.name}</p>}
                        </div>

                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label className="tradeon-label" htmlFor="sku">{t('products.sku')} {req}</label>
                                <input
                                    id="sku"
                                    type="text"
                                    value={data.sku}
                                    onChange={(e) => setData('sku', e.target.value)}
                                    className="tradeon-input uppercase"
                                    placeholder={t('products.sku_ph')}
                                    required
                                />
                                {errors.sku && <p className="tradeon-error">{errors.sku}</p>}
                            </div>

                            <div>
                                <label className="tradeon-label" htmlFor="category">{t('products.category')} {req}</label>
                                <input
                                    id="category"
                                    type="text"
                                    value={data.category}
                                    onChange={(e) => setData('category', e.target.value)}
                                    className="tradeon-input"
                                    placeholder={t('products.category_ph')}
                                    required
                                />
                                {errors.category && <p className="tradeon-error">{errors.category}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <label className="tradeon-label" htmlFor="price">{t('products.price')} ($) {req}</label>
                                <input
                                    id="price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={data.price}
                                    onChange={(e) => setData('price', e.target.value)}
                                    className="tradeon-input"
                                    placeholder="99.99"
                                    required
                                />
                                {errors.price && <p className="tradeon-error">{errors.price}</p>}
                            </div>

                            <div>
                                <label className="tradeon-label" htmlFor="stock">{t('products.stock')} {req}</label>
                                <input
                                    id="stock"
                                    type="number"
                                    min="0"
                                    value={data.stock}
                                    onChange={(e) => setData('stock', e.target.value)}
                                    className="tradeon-input"
                                    placeholder="10"
                                    required
                                />
                                {errors.stock && <p className="tradeon-error">{errors.stock}</p>}
                            </div>

                            <div>
                                <label className="tradeon-label" htmlFor="status">{t('products.status')} {req}</label>
                                <select
                                    id="status"
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                    className="tradeon-input"
                                >
                                    <option value="active">{t('products.status_active')}</option>
                                    <option value="draft">{t('products.status_draft')}</option>
                                    <option value="archived">{t('products.status_archived')}</option>
                                </select>
                                {errors.status && <p className="tradeon-error">{errors.status}</p>}
                            </div>
                        </div>

                        <div>
                            <label className="tradeon-label" htmlFor="description">{t('products.description')}</label>
                            <textarea
                                id="description"
                                rows="4"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                className="tradeon-input"
                                placeholder={t('products.description_ph')}
                            />
                            {errors.description && <p className="tradeon-error">{errors.description}</p>}
                        </div>

                        <div className="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                            <Link href={route('products.index')} className="btn-secondary">
                                {t('products.cancel')}
                            </Link>
                            <button type="submit" disabled={processing} className="btn-primary">
                                {processing ? t('products.saving') : t('products.save')}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </TradeonLayout>
    );
}
