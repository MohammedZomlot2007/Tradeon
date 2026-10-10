import React, { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function FactoryForm({ company }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const existing = company.factory || null;
    const [form, setForm] = useState({
        name: existing?.name || company.name || '',
        factory_type: existing?.factory_type || '',
        location_address: existing?.location_address || '',
        production_lines: existing?.production_lines || '',
        annual_output: existing?.annual_output || '',
        manufacturing_process: existing?.manufacturing_process || '',
    });
    const [saving, setSaving] = useState(false);

    const set = (key) => (e) => setForm({ ...form, [key]: e.target.value });

    const submit = (e) => {
        e.preventDefault();
        setSaving(true);
        const payload = { ...form, production_lines: form.production_lines === '' ? null : Number(form.production_lines) };
        if (existing) router.put(`/factory/${existing.id}`, payload, { preserveScroll: true, onFinish: () => setSaving(false) });
        else router.post('/factory', payload, { preserveScroll: true, onFinish: () => setSaving(false) });
    };

    const remove = () => {
        if (!existing || !confirm(t('factory.remove_confirm'))) return;
        router.delete(`/factory/${existing.id}`, { preserveScroll: true });
    };

    const input = 'tradeon-input px-3 py-2 text-xs';

    return (
        <form onSubmit={submit} className="rounded-2xl border border-sky-200 bg-sky-50/50 p-4">
            <h4 className="mb-3 text-xs font-bold text-slate-900">{existing ? t('factory.edit_title') : t('factory.create_title')}</h4>
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div><label className="tradeon-label">{t('factory.name')} *</label><input value={form.name} onChange={set('name')} required className={input} /></div>
                <div><label className="tradeon-label">{t('factory.type')}</label><input value={form.factory_type} onChange={set('factory_type')} className={input} /></div>
                <div><label className="tradeon-label">{t('factory.location')}</label><input value={form.location_address} onChange={set('location_address')} className={input} /></div>
                <div><label className="tradeon-label">{t('factory.lines')}</label><input type="number" min="0" value={form.production_lines} onChange={set('production_lines')} className={input} /></div>
                <div className="sm:col-span-2"><label className="tradeon-label">{t('factory.output')}</label><input value={form.annual_output} onChange={set('annual_output')} placeholder={t('factory.output_ph')} className={input} /></div>
                <div className="sm:col-span-2"><label className="tradeon-label">{t('factory.process')}</label><textarea rows="3" value={form.manufacturing_process} onChange={set('manufacturing_process')} className={input} /></div>
            </div>
            <div className="mt-3 flex justify-end gap-2">
                {existing && <button type="button" onClick={remove} className="btn-danger !bg-rose-50 !text-rose-600 hover:!bg-rose-100">{t('factory.remove')}</button>}
                <button disabled={saving} className="btn-primary">{saving ? t('factory.saving') : existing ? t('factory.save') : t('factory.create')}</button>
            </div>
        </form>
    );
}
