import { Head, useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';

export default function CreateItem() {
    const { auth } = usePage().props;
    const { post, processing, errors } = useForm({
        name: '',
        categories: ['top'],
        weather: [],
        styles: [],
        dress: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('items.store'));
    };

    return (
        <div>
            <Head title="Ajouter une pièce" />

            <div className="max-w-2xl mx-auto">
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                    Ajouter une pièce
                </h1>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <form onSubmit={submit} encType="multipart/form-data">
                        {/* Photo */}
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Photo
                            </label>
                            <input
                                type="file"
                                name="photo"
                                accept="image/jpeg,image/png,image/webp"
                                required
                                className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.photo && (
                                <p className="mt-1 text-sm text-red-600">{errors.photo}</p>
                            )}
                        </div>

                        {/* Nom */}
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nom (optionnel)
                            </label>
                            <input
                                type="text"
                                name="name"
                                value={post.name}
                                onChange={(e) => post({ ...post, name: e.target.value })}
                                className="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        {/* Catégories */}
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catégories
                            </label>
                            <div className="space-x-2">
                                {['headwear', 'outerwear', 'top', 'bottom', 'shoes'].map((cat) => (
                                    <label key={cat} className="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            name="categories"
                                            value={cat}
                                            checked={post.categories.includes(cat)}
                                            onChange={(e) => {
                                                const newCategories = e.target.checked
                                                    ? [...post.categories, cat]
                                                    : post.categories.filter((c) => c !== cat);
                                                post({ ...post, categories: newCategories });
                                            }}
                                            className="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
                                        />
                                        <span className="ml-2 text-gray-700 dark:text-gray-300">
                                            {cat}
                                        </span>
                                    </label>
                                ))}
                            </div>
                        </div>

                        {/* Boutons */}
                        <div className="flex justify-end space-x-4">
                            <Link
                                href={route('items.index')}
                                className="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Annuler
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:bg-indigo-400 transition-colors"
                            >
                                {processing ? 'Enregistrement...' : 'Enregistrer'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
