import { Head, useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';

export default function Profile() {
    const { auth } = usePage().props;
    const { patch, processing, errors, setData } = useForm({
        name: auth.user.name,
        email: auth.user.email,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('profile.update'));
    };

    return (
        <div>
            <Head title="Profil" />

            <div className="max-w-2xl mx-auto">
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                    Profil
                </h1>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <form onSubmit={submit}>
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nom
                            </label>
                            <input
                                type="text"
                                name="name"
                                value={post.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="mb-6">
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

                        {errors.name && (
                            <p className="mb-4 text-red-600">{errors.name}</p>
                        )}

                        {errors.email && (
                            <p className="mb-4 text-red-600">{errors.email}</p>
                        )}

                        <button
                            type="submit"
                            disabled={processing}
                            className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:bg-indigo-400 transition-colors"
                        >
                            {processing ? 'Enregistrement...' : 'Enregistrer'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
