import React, { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

const emptyForm = { title: '', description: '', industry: '', category: '', moq: '', lead_time: '' };

export default function ShowcaseManager({ company }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const items = company.product_showcases || [];
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState(emptyForm);
    const [saving, setSaving] = useState(false);

    const set = (key) => (e) => setForm({ ...form, [key]: e.target.value });

    const startCreate = () => {
        setEditing(null);
        setForm({ ...emptyForm, industry: company.industry || '' });
        setOpen(true);
    };

    const startEdit = (item) => {
        setEditing(item);
        setForm({ title: item.title || '', description: item.description || '', industry: item.industry || '', category: item.category || '', moq: item.moq || '', lead_time: item.lead_time || '' });
        setOpen(true);
    };

    const submit = (e) => {
        e.preventDefault();
        setSaving(true);
        const done = () => {
            setSaving(false);
            setOpen(false);
        };
        if (editing) router.put(`/showcases/${editing.id}`, form, { preserveScroll: true, onFinish: done });
        else router.post('/showcases', form, { preserveScroll: true, onFinish: done });
    };

    const remove = (item) => {
        if (!confirm(t('showcase.remove_confirm'))) return;
        router.delete(`/showcases/${item.id}`, { preserveScroll: true });
    };

    const input = 'tradeon-input px-3 py-2 text-xs';

    return (
        <div className="space-y-4">
            <div className="flex justify-end">
                <button onClick={startCreate} className="btn-primary">+ {t('showcase.add')}</button>
            </div>

            {open && (
                <form onSubmit={submit} className="rounded-2xl border border-sky-200 bg-sky-50/50 p-4">
                    <h4 className="mb-3 text-xs font-bold text-slate-900">{editing ? t('showcase.edit_title') : t('showcase.new_title')}</h4>
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div><label className="tradeon-label">{t('showcase.title_label')} *</label><input value={form.title} onChange={set('title')} required className={input} /></div>
                        <div><label className="tradeon-label">{t('showcase.industry')} *</label><input value={form.industry} onChange={set('industry')} required className={input} /></div>
                        <div><label className="tradeon-label">{t('showcase.category')}</label><input value={form.category} onChange={set('category')} className={input} /></div>
                        <div><label className="tradeon-label">{t('showcase.moq')}</label><input value={form.moq} onChange={set('moq')} className={input} /></div>
                        <div className="sm:col-span-2"><label className="tradeon-label">{t('showcase.lead_time')}</label><input value={form.lead_time} onChange={set('lead_time')} className={input} /></div>
                        <div className="sm:col-span-2"><label className="tradeon-label">{t('showcase.description')} *</label><textarea rows="3" value={form.description} onChange={set('description')} required className={input} /></div>
                    </div>
                    <div className="mt-3 flex justify-end gap-2">
                        <button type="button" onClick={() => setOpen(false)} className="btn-secondary">{t('common.cancel')}</button>
                        <button disabled={saving} className="btn-primary">{saving ? t('showcase.saving') : t('showcase.save')}</button>
                    </div>
                </form>
            )}

            {items.length > 0 ? (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {items.map((prod) => (
                        <div key={prod.id} className="rounded-2xl border border-slate-200 bg-white p-4">
                            <h4 className="text-sm font-bold text-slate-900">{prod.title}</h4>
                            <p className="mt-1 line-clamp-2 text-xs text-slate-500">{prod.description}</p>
                            <div className="mt-3 flex items-center justify-between border-t border-slate-100 pt-2">
                                <span className="text-xs text-slate-500">{t('common.moq')}: <strong>{prod.moq || '—'}</strong></span>
                                <div className="flex gap-2">
                                    <button onClick={() => startEdit(prod)} className="text-xs font-bold text-sky-600 hover:underline">{t('showcase.edit')}</button>
                                    <button onClick={() => remove(prod)} className="text-xs font-bold text-rose-600 hover:underline">{t('showcase.delete')}</button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                !open && <p className="rounded-xl border border-dashed border-slate-200 py-6 text-center text-xs text-slate-400">{t('showcase.empty')}</p>
            )}
        </div>
    );
}
