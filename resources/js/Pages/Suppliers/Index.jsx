import React from 'react';
import TradeonLayout from '@/Layouts/TradeonLayout';
import CompanyCard from '@/Components/Tradeon/CompanyCard';
import EmptyState from '@/Components/Tradeon/EmptyState';

import { usePage } from '@inertiajs/react';
import { translate } from '@/i18n';

export default function SuppliersIndex({ suppliers }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <div className="space-y-6">

                <div className="page-hero">
                    <h1 className="page-title">📦 {t('directories.suppliers_title')}</h1>
                    <p className="page-subtitle">{t('directories.suppliers_sub')}</p>
                </div>

                {suppliers && suppliers.data && suppliers.data.length > 0 ? (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {suppliers.data.map((sup) => (
                            <CompanyCard key={sup.id} company={sup} />
                        ))}
                    </div>
                ) : (
                    <EmptyState icon="📦" title={t('directories.no_suppliers')} description={t('directories.no_suppliers_desc')} />
                )}

            </div>
        </TradeonLayout>
    );
}
