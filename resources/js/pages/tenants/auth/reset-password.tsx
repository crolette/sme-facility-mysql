import { Head, useForm } from '@inertiajs/react';
import { Check, Eye, EyeClosed, LoaderCircle, X } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { useLaravelReactI18n } from 'laravel-react-i18n';

interface ResetPasswordProps {
    token: string;
    email: string;
}

type ResetPasswordForm = {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const { t } = useLaravelReactI18n();
    const { data, setData, post, processing, errors, reset } = useForm<Required<ResetPasswordForm>>({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmationPassword, setShowConfirmationPassword] = useState(false);

    const regexSymbols = /^(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?])/;
    const regexNumber = /^(?=.*\d)/;
    const regexUppercase = /^(?=.*[A-Z])/;
    const regexLowercase = /^(?=.*[a-z])/;
    const regexLength = /^.{12,}$/;
    const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{12,}$/;

    return (
        <AuthLayout title={t('auth.password_reset')} description={t('auth.password_reset_description')}>
            <Head title={t('auth.password_reset')} />

            <form onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('common.email')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autoComplete="email"
                            value={data.email}
                            className="mt-1 block w-full"
                            readOnly
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError message={errors.email} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">{t('auth.password')}</Label>
                        <div className="flex items-center gap-2">
                            <Input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                autoComplete="new-password"
                                value={data.password}
                                minLength={12}
                                required
                                className="mt-1 block w-full"
                                autoFocus
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder={t('auth.password')}
                            />
                            {showPassword ? (
                                <Eye onClick={() => setShowPassword(!showPassword)} />
                            ) : (
                                <EyeClosed onClick={() => setShowPassword(!showPassword)} />
                            )}
                        </div>
                        <span className="text-xs">{t('auth.password_constraints.description')}</span>
                        <InputError message={errors.password} />
                        <ul>
                            <li className="flex items-center gap-3 text-xs">
                                {regexSymbols.test(data.password) ? <Check className="text-success" /> : <X className="text-destructive" />}
                                {t('auth.password_constraints.special')}
                            </li>
                            <li className="flex items-center gap-3 text-xs">
                                {regexNumber.test(data.password) ? <Check className="text-success" /> : <X className="text-destructive" />}
                                {t('auth.password_constraints.numbers')}
                            </li>
                            <li className="flex items-center gap-3 text-xs">
                                {regexLowercase.test(data.password) ? <Check className="text-success" /> : <X className="text-destructive" />}
                                {t('auth.password_constraints.lowercase')}
                            </li>
                            <li className="flex items-center gap-3 text-xs">
                                {regexUppercase.test(data.password) ? <Check className="text-success" /> : <X className="text-destructive" />}
                                {t('auth.password_constraints.uppercase')}
                            </li>
                            <li className="flex items-center gap-3 text-xs">
                                {regexLength.test(data.password) ? <Check className="text-success" /> : <X className="text-destructive" />}
                                {t('auth.password_constraints.length')}
                            </li>
                        </ul>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">{t('auth.password_confirm')}</Label>
                        <div className="flex items-center gap-2">
                            <Input
                                id="password_confirmation"
                                type={showConfirmationPassword ? 'text' : 'password'}
                                name="password_confirmation"
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                minLength={12}
                                required
                                className="mt-1 block w-full"
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder={t('auth.password_confirm')}
                            />
                            {showConfirmationPassword ? (
                                <Eye onClick={() => setShowConfirmationPassword(!showConfirmationPassword)} />
                            ) : (
                                <EyeClosed onClick={() => setShowConfirmationPassword(!showConfirmationPassword)} />
                            )}
                            <InputError message={errors.password_confirmation} className="mt-2" />
                        </div>
                    </div>

                    <Button
                        type="submit"
                        className="mt-4 w-full"
                        disabled={processing || !passwordRegex.test(data.password) || data.password !== data.password_confirmation}
                    >
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        {t('auth.password_reset')}
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
