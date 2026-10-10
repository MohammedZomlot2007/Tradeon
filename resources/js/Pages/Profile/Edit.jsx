import TradeonLayout from '@/Layouts/TradeonLayout';
import { Head, usePage } from '@inertiajs/react';
import { translate } from '@/i18n';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({ mustVerifyEmail, status }) {
    const locale = usePage().props.locale || 'en';
    const t = (key, replacements) => translate(key, locale, replacements);
    return (
        <TradeonLayout>
            <Head title={t('nav.profile')} />

            <div className="mx-auto max-w-3xl space-y-5">
                <div className="page-hero">
                    <h1 className="page-title">👤 {t('nav.profile')}</h1>
                    <p className="page-subtitle">{t('profile.subtitle')}</p>
                </div>
                <div className="tradeon-card p-5 sm:p-7">
                    <UpdateProfileInformationForm
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                        className="max-w-xl"
                    />
                </div>

                <div className="tradeon-card p-5 sm:p-7">
                    <UpdatePasswordForm className="max-w-xl" />
                </div>

                <div className="tradeon-card border-rose-100 p-5 sm:p-7">
                    <DeleteUserForm className="max-w-xl" />
                </div>
            </div>
        </TradeonLayout>
    );
}
