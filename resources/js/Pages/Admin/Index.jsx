import React, { useState } from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import { router, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function AdminIndex({ stats, users, verifications, reports, auditLogs = [], filters = {} }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    const [search, setSearch] = useState(filters.user_search || '');
    const [status, setStatus] = useState(filters.user_status || '');
    const [role, setRole] = useState(filters.user_role || '');
    const [notes, setNotes] = useState({});

    const applyFilters = (e) => {
        e.preventDefault();
        router.get('/admin', { user_search: search || undefined, user_status: status || undefined, user_role: role || undefined }, { preserveState: true });
    };

    const moderateVerification = (id, action) => {
        router.post(`/admin/verifications/${id}/${action}`, { admin_notes: notes[id] || null }, { preserveScroll: true });
    };

    const moderateReport = (id, reportStatus) => {
        router.post(`/admin/reports/${id}`, { status: reportStatus }, { preserveScroll: true });
    };

    const statCards = [
        { key: 'admin.users', value: stats.users, icon: '👥', accent: 'text-slate-900' },
        { key: 'admin.companies', value: stats.companies, icon: '🏢', accent: 'text-sky-600' },
        { key: 'admin.pending_verifications', value: stats.pending_verifications, icon: '⏳', accent: 'text-amber-600' },
        { key: 'admin.pending_reports', value: stats.pending_reports, icon: '🚨', accent: 'text-rose-600' },
        { key: 'admin.active_users', value: stats.active_users, icon: '✅', accent: 'text-emerald-600' },
        { key: 'admin.suspended_users', value: stats.suspended_users, icon: '⛔', accent: 'text-rose-500' },
    ];

    return (
        <TradeonLayout>
            <div className="space-y-6">

                <div className="rounded-2xl border border-slate-700 bg-slate-900 p-5 sm:p-6">
                    <h1 className="flex items-center gap-2 text-xl font-extrabold text-white">
                        <span aria-hidden="true">🛡️</span>
                        <span>{t('admin.title')}</span>
                    </h1>
                    <p className="mt-1 text-xs text-slate-300">{t('admin.subtitle')}</p>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    {statCards.map((s) => (
                        <div key={s.key} className="tradeon-card p-4 text-center">
                            <div className="text-2xl" aria-hidden="true">{s.icon}</div>
                            <div className={`mt-1 text-xl font-extrabold ${s.accent}`}>{s.value}</div>
                            <div className="text-[11px] text-slate-500">{t(s.key)}</div>
                        </div>
                    ))}
                </div>

                <div className="tradeon-card space-y-4 p-5">
                    <h3 className="text-sm font-bold text-slate-900">👥 {t('admin.user_management')}</h3>
                    <form onSubmit={applyFilters} className="grid grid-cols-1 gap-2 sm:grid-cols-4" role="search">
                        <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder={t('admin.search_ph')} aria-label={t('admin.search_ph')} className="tradeon-input px-3 py-2 text-xs" />
                        <select value={status} onChange={(e) => setStatus(e.target.value)} aria-label={t('admin.all_statuses')} className="tradeon-input px-3 py-2 text-xs">
                            <option value="">{t('admin.all_statuses')}</option>
                            <option value="active">{t('admin.status_active')}</option>
                            <option value="suspended">{t('admin.status_suspended')}</option>
                        </select>
                        <select value={role} onChange={(e) => setRole(e.target.value)} aria-label={t('admin.all_roles')} className="tradeon-input px-3 py-2 text-xs">
                            <option value="">{t('admin.all_roles')}</option>
                            <option value="user">{t('admin.role_user')}</option>
                            <option value="admin">{t('admin.role_admin')}</option>
                        </select>
                        <button className="btn-primary">{t('admin.search_btn')}</button>
                    </form>
                    <div className="tradeon-table-wrap">
                        <table className="tradeon-table">
                            <thead><tr><th>{t('admin.table_user')}</th><th>{t('admin.table_role')}</th><th>{t('admin.table_status')}</th><th className="text-right rtl:text-left">{t('admin.table_action')}</th></tr></thead>
                            <tbody>
                                {(users?.data || []).map((u) => <tr key={u.id}><td><div className="font-semibold text-slate-900">{u.name}</div><div className="text-slate-400">{u.email}</div></td><td><span className={`badge ${u.role === 'admin' ? 'badge-slate' : 'badge-slate'}`}>{u.role === 'admin' ? t('admin.role_admin') : t('admin.role_user')}</span></td><td><span className={`badge ${u.status === 'suspended' ? 'badge-rose' : 'badge-emerald'}`}>{u.status === 'suspended' ? t('admin.status_suspended') : t('admin.status_active')}</span></td><td className="text-right rtl:text-left"><button onClick={() => router.post(`/admin/users/${u.id}/suspension`, {}, { preserveScroll: true })} className="rounded-lg bg-slate-900 px-3 py-1 text-[10px] font-bold text-white hover:bg-slate-700">{u.status === 'suspended' ? t('admin.restore') : t('admin.suspend')}</button></td></tr>)}
                            </tbody>
                        </table>
                    </div>
                    {(!users?.data || users.data.length === 0) && <div className="py-4 text-center text-xs text-slate-400">{t('admin.no_users')}</div>}
                </div>

                {/* Verification Requests Table */}
                <div className="tradeon-card space-y-4 p-5">
                    <h3 className="text-sm font-bold text-slate-900">✅ {t('admin.verification_queue')}</h3>

                    {verifications?.data && verifications.data.length > 0 ? (
                        <div className="space-y-3">
                            {verifications.data.map((v) => (
                                <div key={v.id} className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <div className="text-sm font-bold text-slate-900">{v.company?.name || t('admin.fallback_company')}</div>
                                            <div className="mt-0.5 text-[11px] text-slate-500">{v.verification_type} · {v.document_type} · {new Date(v.created_at).toLocaleDateString()}</div>
                                        </div>
                                        <span className={`badge ${v.status === 'approved' ? 'badge-emerald' : v.status === 'rejected' ? 'badge-rose' : 'badge-amber'}`}>{v.status === 'approved' ? t('verification.status_approved') : v.status === 'rejected' ? t('verification.status_rejected') : t('verification.status_pending')}</span>
                                    </div>
                                    <div className="mt-3 flex flex-wrap items-center gap-2">
                                        <a href={`/verification/${v.id}/document`} className="btn-secondary !py-1.5 !text-[11px]">{t('admin.view_document')}</a>
                                        <input value={notes[v.id] || ''} onChange={(e) => setNotes({ ...notes, [v.id]: e.target.value })} placeholder={t('admin.notes_ph')} aria-label={t('admin.notes_ph')} className="tradeon-input min-w-52 flex-1 px-3 py-1.5 text-[11px]" />
                                        <button onClick={() => moderateVerification(v.id, 'approve')} className="btn-success !py-1.5 !text-[11px]">{t('admin.approve')}</button>
                                        <button onClick={() => moderateVerification(v.id, 'reject')} className="btn-danger !py-1.5 !text-[11px]">{t('admin.reject')}</button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="empty-state !py-6 text-xs text-slate-400">{t('admin.no_verifications')}</div>
                    )}
                </div>

                {/* Report Moderation */}
                <div className="tradeon-card space-y-4 p-5">
                    <h3 className="text-sm font-bold text-slate-900">🚨 {t('admin.report_moderation')}</h3>
                    {reports?.data && reports.data.length > 0 ? (
                        <div className="space-y-3">
                            {reports.data.map((r) => (
                                <div key={r.id} className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <div className="text-sm font-bold text-slate-900">{r.reason}</div>
                                            <div className="mt-0.5 text-[11px] text-slate-500">
                                                {t('admin.report_by')} {r.reporter?.name || t('admin.report_unknown')} · {r.reportable_type} #{r.reportable_id} · {new Date(r.created_at).toLocaleDateString()}
                                            </div>
                                            {r.details && <p className="mt-1 text-xs text-slate-600">{r.details}</p>}
                                        </div>
                                        <span className="badge-slate badge">{r.status === 'reviewed' ? t('admin.reviewed') : r.status === 'resolved' ? t('admin.resolved') : r.status === 'dismissed' ? t('admin.dismissed') : r.status}</span>
                                    </div>
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        {['reviewed', 'resolved', 'dismissed'].map((s) => (
                                            <button key={s} onClick={() => moderateReport(r.id, s)} className="btn-secondary !py-1.5 !text-[11px] capitalize">{t(`admin.${s}`)}</button>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="empty-state !py-6 text-xs text-slate-400">{t('admin.no_reports')}</div>
                    )}
                </div>

                <div className="tradeon-card space-y-3 p-5">
                    <h3 className="text-sm font-bold text-slate-900">📜 {t('admin.audit_log')}</h3>
                    <div className="max-h-72 space-y-2 overflow-auto">
                        {auditLogs.map((log) => <div key={log.id} className="rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs"><div className="font-semibold text-slate-800">{log.action}</div><div className="text-slate-400">{log.user?.name || t('admin.fallback_system')} · {new Date(log.created_at).toLocaleString()}</div></div>)}
                    </div>
                </div>

            </div>
        </TradeonLayout>
    );
}
