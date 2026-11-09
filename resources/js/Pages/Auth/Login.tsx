import { Head, useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';

export default function Login() {
    const { auth } = usePage().props;
    const { post, processing, errors, setData } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
            <Head title="Connexion" />

            <div className="max-w-md w-full mx-auto">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
                    <div className="text-center mb-8">
                        <ApplicationLogo className="h-12 w-auto text-gray-500 mx-auto mb-4" />
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                            Connexion
                        </h2>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            {auth.user ? 'Vous êtes déjà connecté.' : 'Connectez-vous à votre compte'}
                        </p>
                    </div>

                    {auth.user && (
                        <div className="mb-6 p-4 bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">
                            Redirection vers le dashboard...
                        </div>
                    )}

                    {errors.email && (
                        <div className="mb-4 p-4 bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">
                            {errors.email}
                        </div>
                    )}

                    {errors.password && (
                        <div className="mb-4 p-4 bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">
                            {errors.password}
                        </div>
                    )}

                    <form onSubmit={submit}>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input
                                type="email"
                                name="email"
                                value={post.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Mot de passe
                            </label>
                            <input
                                type="password"
                                name="password"
                                value={post.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="mb-6">
                            <label className="flex items-center">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    checked={post.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
                                />
                                <span className="ml-2 text-gray-700 dark:text-gray-300">
                                    Se souvenir de moi
                                </span>
                            </label>
                        </div>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:bg-indigo-400 transition-colors"
                            >
                                {processing ? 'Connexion...' : 'Se connecter'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}

function ApplicationLogo() {
    return (
        <svg
            className="h-12 w-auto"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
            />
        </svg>
    );
}
