import { useState } from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function CompanyOnboarding({ businessTypes = [], industry = '', options }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const hasSessionTypes = businessTypes.length > 0 && !!industry;
    const [selected, setSelected] = useState(businessTypes);
    const { data, setData, post, processing, errors } = useForm({
        company_name: '', country: '', city: '', company_size: '', founded_year: '', website: '', description: '',
        industry: industry || '',
    });

    const toggleType = (type) => {
        setSelected((prev) => (prev.includes(type) ? prev.filter((t) => t !== type) : [...prev, type]));
    };

    const submit = (e) => {
        e.preventDefault();
        if (hasSessionTypes) {
            post(route('onboarding.company.store'));
        } else {
            post(route('onboarding.company.store'), { ...data, business_types: selected });
        }
    };

    return (
        <GuestLayout>
            <Head title={t('onboarding.title')} />
            <div className="mx-auto max-w-2xl">
                <div className="mb-6">
                    <div className="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600">{t('onboarding.step')}</div>
                    <h1 className="mt-1 text-2xl font-bold text-slate-900">{t('onboarding.title')}</h1>
                    <p className="mt-2 text-sm text-slate-500">{t('onboarding.subtitle')}</p>
                </div>

                {hasSessionTypes ? (
                    <div className="mb-6 rounded-2xl border border-cyan-100 bg-cyan-50 p-4 text-sm text-cyan-900">
                        <div className="font-semibold">{t('onboarding.selected_types')}</div>
                        <div className="mt-2 flex flex-wrap gap-2">{businessTypes.map((type) => <span key={type} className="rounded-full bg-white px-3 py-1 text-xs font-semibold">{type}</span>)}</div>
                        <div className="mt-3 text-xs"><span className="font-semibold">{t('onboarding.industry')}:</span> {industry}</div>
                    </div>
                ) : (
                    <div className="mb-6 space-y-4 rounded-2xl border border-cyan-100 bg-cyan-50 p-4">
                        <div>
                            <InputLabel value={t('onboarding.choose_types')} />
                            <div className="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                {(options?.businessTypes ?? []).map((type) => {
                                    const active = selected.includes(type);
                                    return <button type="button" key={type} onClick={() => toggleType(type)} className={`rounded-xl border px-3 py-2 text-left text-xs font-semibold transition ${active ? 'border-cyan-500 bg-white text-cyan-700' : 'border-transparent bg-white/60 text-slate-600 hover:border-slate-300'}`}>{type}</button>;
                                })}
                            </div>
                            <InputError message={errors.business_types} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="industry" value={t('onboarding.industry')} />
                            <select id="industry" value={data.industry} onChange={(e) => setData('industry', e.target.value)} className="mt-1 block w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                <option value="">{t('auth.select_industry')}</option>
                                {(options?.industries ?? []).map((ind) => <option key={ind} value={ind}>{ind}</option>)}
                            </select>
                            <InputError message={errors.industry} className="mt-2" />
                        </div>
                    </div>
                )}

                <form onSubmit={submit} className="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div>
                        <InputLabel htmlFor="company_name" value={t('onboarding.company_name')} />
                        <TextInput id="company_name" value={data.company_name} className="mt-1 block w-full" isFocused onChange={(e) => setData('company_name', e.target.value)} required />
                        <InputError message={errors.company_name} className="mt-2" />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div><InputLabel htmlFor="country" value={t('onboarding.country')} /><TextInput id="country" value={data.country} className="mt-1 block w-full" onChange={(e) => setData('country', e.target.value)} required /><InputError message={errors.country} className="mt-2" /></div>
                        <div><InputLabel htmlFor="city" value={t('onboarding.city')} /><TextInput id="city" value={data.city} className="mt-1 block w-full" onChange={(e) => setData('city', e.target.value)} required /><InputError message={errors.city} className="mt-2" /></div>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="company_size" value={t('onboarding.company_size')} />
                            <select id="company_size" value={data.company_size} onChange={(e) => setData('company_size', e.target.value)} className="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-cyan-500 focus:ring-cyan-500"><option value="">{t('onboarding.select_size')}</option>{(options?.companySizes ?? []).map((size) => <option key={size} value={size}>{size}</option>)}</select>
                            <InputError message={errors.company_size} className="mt-2" />
                        </div>
                        <div><InputLabel htmlFor="founded_year" value={t('onboarding.founded_year')} /><TextInput id="founded_year" type="number" value={data.founded_year} className="mt-1 block w-full" onChange={(e) => setData('founded_year', e.target.value)} /><InputError message={errors.founded_year} className="mt-2" /></div>
                    </div>
                    <div><InputLabel htmlFor="website" value={t('onboarding.website')} /><TextInput id="website" type="url" placeholder="https://" value={data.website} className="mt-1 block w-full" onChange={(e) => setData('website', e.target.value)} /><InputError message={errors.website} className="mt-2" /></div>
                    <div><InputLabel htmlFor="description" value={t('onboarding.business_description')} /><textarea id="description" value={data.description} onChange={(e) => setData('description', e.target.value)} rows={5} className="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-cyan-500 focus:ring-cyan-500" placeholder={t('onboarding.description_placeholder')} /><InputError message={errors.description} className="mt-2" /></div>
                    <div className="flex justify-end pt-2"><PrimaryButton disabled={processing || (!hasSessionTypes && (selected.length === 0 || !data.industry))}>{t('onboarding.create_profile')}</PrimaryButton></div>
                </form>
            </div>
        </GuestLayout>
    );
}
