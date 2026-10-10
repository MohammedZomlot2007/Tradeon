import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';
import { useState } from 'react';

const fallbackBusinessTypes = [
    'Business Owner', 'Manufacturer', 'Supplier', 'Wholesaler', 'Distributor',
    'Importer', 'Retailer', 'Brand', 'Buyer', 'Service Provider',
    'Entrepreneur', 'Business Professional',
];

const fallbackIndustries = [
    'Technology', 'Manufacturing', 'Textiles & Apparel', 'Food & Beverage',
    'Construction', 'Furniture', 'Automotive', 'Electronics', 'Agriculture',
    'Beauty & Personal Care', 'Pharmaceuticals', 'Logistics & Shipping',
    'Wholesale & Trading', 'Professional Services', 'Other',
];

export default function Register({ businessTypes, industries }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const typeOptions = businessTypes?.length ? businessTypes : fallbackBusinessTypes;
    const industryOptions = industries?.length ? industries : fallbackIndustries;
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '', email: '', password: '', password_confirmation: '',
        business_types: [], industry: '',
    });
    const [selected, setSelected] = useState([]);

    const toggleType = (type) => {
        const next = selected.includes(type)
            ? selected.filter((item) => item !== type)
            : [...selected, type];
        setSelected(next);
        setData('business_types', next);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), { onFinish: () => reset('password', 'password_confirmation') });
    };

    return (
        <GuestLayout>
            <Head title={t('auth.join_title')} />
            <div className="mb-6">
                <div className="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600">TRADEON</div>
                <h1 className="mt-1 text-2xl font-bold text-slate-900">{t('auth.join_title')}</h1>
                <p className="mt-1 text-sm text-slate-500">{t('auth.join_subtitle')}</p>
            </div>

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel htmlFor="name" value={t('auth.name')} />
                    <TextInput id="name" value={data.name} className="mt-1 block w-full" autoComplete="name" isFocused onChange={(e) => setData('name', e.target.value)} required />
                    <InputError message={errors.name} className="mt-2" />
                </div>
                <div>
                    <InputLabel htmlFor="email" value={t('auth.email')} />
                    <TextInput id="email" type="email" value={data.email} className="mt-1 block w-full" autoComplete="username" onChange={(e) => setData('email', e.target.value)} required />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel value={t('auth.i_am')} />
                    <div className="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        {typeOptions.map((type) => {
                            const active = selected.includes(type);
                            return <button type="button" key={type} onClick={() => toggleType(type)} className={`rounded-xl border px-3 py-2 text-left text-xs font-semibold transition ${active ? 'border-cyan-500 bg-cyan-50 text-cyan-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'}`}>{type}</button>;
                        })}
                    </div>
                    <InputError message={errors.business_types} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="industry" value={t('auth.primary_industry')} />
                    <select id="industry" value={data.industry} onChange={(e) => setData('industry', e.target.value)} className="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-cyan-500 focus:ring-cyan-500" required>
                        <option value="">{t('auth.select_industry')}</option>
                        {industryOptions.map((industry) => <option key={industry} value={industry}>{industry}</option>)}
                    </select>
                    <InputError message={errors.industry} className="mt-2" />
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="password" value={t('auth.password')} />
                        <TextInput id="password" type="password" value={data.password} className="mt-1 block w-full" autoComplete="new-password" onChange={(e) => setData('password', e.target.value)} required />
                        <InputError message={errors.password} className="mt-2" />
                    </div>
                    <div>
                        <InputLabel htmlFor="password_confirmation" value={t('auth.confirm_password')} />
                        <TextInput id="password_confirmation" type="password" value={data.password_confirmation} className="mt-1 block w-full" autoComplete="new-password" onChange={(e) => setData('password_confirmation', e.target.value)} required />
                        <InputError message={errors.password_confirmation} className="mt-2" />
                    </div>
                </div>

                <div className="flex items-center justify-between gap-3 pt-2">
                    <Link href={route('login')} className="text-sm text-slate-500 hover:text-slate-800">{t('auth.already_registered')}</Link>
                    <PrimaryButton disabled={processing || selected.length === 0 || !data.industry}>{t('auth.continue')}</PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
