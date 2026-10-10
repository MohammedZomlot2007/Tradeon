import React, { useEffect, useRef } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import TradeonLayout from '../../Layouts/TradeonLayout';

import { translate } from '@/i18n';

export default function Pricing() {
    const { plans, billing, flash, auth, locale } = usePage().props;
    const t = (key, replacements) => translate(key, locale || 'en', replacements);
    const checkoutStarted = useRef(false);

    useEffect(() => {
        if (!billing?.client_token || !flash?.billing_checkout || checkoutStarted.current) return;
        checkoutStarted.current = true;

        const open = () => {
            if (!window.Paddle) return;
            if (billing.environment === 'sandbox' && window.Paddle.Environment?.set) {
                window.Paddle.Environment.set('sandbox');
            }
            window.Paddle.Initialize({ token: billing.client_token });
            window.Paddle.Checkout.open({
                items: [{ priceId: flash.billing_checkout.price_id, quantity: 1 }],
                customer: { email: flash.billing_checkout.email },
                customData: {
                    plan: flash.billing_checkout.plan,
                    interval: flash.billing_checkout.interval,
                    tradeon_user_id: auth?.user?.id,
                },
            });
        };

        if (window.Paddle) open();
        else {
            const script = document.createElement('script');
            script.src = 'https://cdn.paddle.com/paddle/v2/paddle.js';
            script.async = true;
            script.onload = open;
            document.head.appendChild(script);
        }
    }, [billing, flash, auth?.user?.id]);

    const selectPlan = (plan) => {
        if (!auth?.user) {
            window.location.href = '/register';
            return;
        }
        router.post('/billing/checkout/' + plan.id, { interval: 'monthly' }, { preserveScroll: true });
    };

    const plan = plans[0];

    return (
        <TradeonLayout>
            <Head title={t('billing.title')} />
            <section className="max-w-6xl mx-auto">
                <div className="text-center mb-10">
                    <span className="badge-sky !px-4 !py-1 !text-xs">{t('billing.pro_badge')}</span>
                    <h1 className="page-title mt-4 !text-3xl md:!text-4xl">{t('billing.pricing_title')}</h1>
                    <p className="page-subtitle mx-auto mt-3 max-w-2xl">{t('billing.pricing_sub')}</p>
                    {!billing?.configured && (
                        <div className="alert-warning mx-auto mt-4 max-w-2xl justify-center">{t('billing.setup_mode')}</div>
                    )}
                </div>

                {plan && (
                    <div className="max-w-md mx-auto">
                        <div className="tradeon-card p-6 border-2 border-sky-400 shadow-xl shadow-sky-100">
                            <div className="flex items-center justify-between gap-3">
                                <h2 className="text-xl font-bold text-slate-900">{plan.name}</h2>
                            </div>
                            <p className="mt-2 min-h-10 text-sm text-slate-500">{plan.description}</p>
                            <div className="mt-6 text-4xl font-black text-slate-900">
                                ${plan.monthly_price}
                                <span className="text-sm font-medium text-slate-400"> / {t('billing.monthly')}</span>
                            </div>
                            <div className="mt-6 space-y-3">
                                {(plan.features || []).map((feature) => (
                                    <div key={feature} className="flex gap-2 text-sm text-slate-600">
                                        <span className="text-emerald-500">✓</span>{feature}
                                    </div>
                                ))}
                            </div>
                            <button
                                onClick={() => selectPlan(plan)}
                                disabled={!billing?.configured}
                                className="btn-primary mt-8 w-full !py-3 !text-sm"
                            >
                                {billing?.configured ? t('billing.start_checkout') : t('billing.setup_required')}
                            </button>
                        </div>
                    </div>
                )}

                {auth?.user && (
                    <div className="mt-8 text-center">
                        <Link href="/billing" className="text-sm font-semibold text-sky-600 hover:text-sky-700">
                            {t('billing.open_billing')}
                        </Link>
                    </div>
                )}
            </section>
        </TradeonLayout>
    );
}
