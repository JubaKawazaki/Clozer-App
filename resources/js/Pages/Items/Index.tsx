import { Head, Link } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

interface Item {
    id: number;
    name: string | null;
    photo_path: string;
    thumb_path: string | null;
    categories: string[];
    weather: string[];
    styles: string[];
    archived: boolean;
}

interface ItemsPageProps {
    items: {
        data: Item[];
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    filters: {
        category?: string;
        archived: boolean;
    };
}

export default function Items() {
    const { items, filters } = usePage<ItemsPageProps>().props;
    const [archived, setArchived] = useState(filters.archived);
    const [category, setCategory] = useState(filters.category || 'all');

    const categories = ['all', 'headwear', 'outerwear', 'top', 'bottom', 'shoes'];

    const filteredItems = items.data.filter((item: Item) => {
        if (category !== 'all' && !item.categories.includes(category)) return false;
        return true;
    });

    const fetchPage = (url: string) => {
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then((r) => r.json()).then((data) => {
            window.history.replaceState({}, '', url);
            window.location.reload();
        });
    };

    return (
        <div>
            <Head title="Dressing" />

            <div className="mb-8 flex items-center justify-between">
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                    Dressing
                </h1>
                <Link
                    href={route('items.create')}
                    className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    Ajouter une pièce
                </Link>
            </div>

            {/* Filtres */}
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-6">
                <div className="flex items-center space-x-4">
                    <select
                        value={category}
                        onChange={(e) => setCategory(e.target.value)}
                        className="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        {categories.map((cat) => (
                            <option key={cat} value={cat}>
                                {cat === 'all' ? 'Toutes les catégories' : cat.charAt(0).toUpperCase() + cat.slice(1)}
                            </option>
                        ))}
                    </select>

                    <label className="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            checked={archived}
                            onChange={(e) => setArchived(e.target.checked)}
                            className="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
                        />
                        <span className="text-gray-700 dark:text-gray-300">
                            Afficher les pièces archivées
                        </span>
                    </label>
                </div>
            </div>

            {/* Grille des pièces */}
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {filteredItems.map((item: Item) => (
                    <Link
                        key={item.id}
                        href={route('items.show', { item: item.id })}
                        className="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <img
                            src={item.thumb_path ? `/storage/${item.thumb_path}` : `/storage/${item.photo_path}`}
                            alt={item.name || `Pièce ${item.id}`}
                            className="w-full h-64 object-cover"
                        />
                        <div className="p-4">
                            <h3 className="font-medium text-gray-900 dark:text-white">
                                {item.name || `Pièce ${item.id}`}
                            </h3>
                            <div className="flex flex-wrap gap-1 mt-2">
                                {item.categories.slice(0, 3).map((cat) => (
                                    <span
                                        key={cat}
                                        className="text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded"
                                    >
                                        {cat}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </Link>
                ))}
            </div>

            {/* Pagination */}
            {items.last_page > 1 && (
                <div className="mt-8 flex justify-center space-x-2">
                    {items.prev_page_url && (
                        <button
                            onClick={() => fetchPage(items.prev_page_url!)}
                            className="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            ← Précédent
                        </button>
                    )}
                    <span className="px-4 py-2 text-gray-600 dark:text-gray-400">
                        Page {items.current_page} / {items.last_page}
                    </span>
                    {items.next_page_url && (
                        <button
                            onClick={() => fetchPage(items.next_page_url!)}
                            className="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Suivant →
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}