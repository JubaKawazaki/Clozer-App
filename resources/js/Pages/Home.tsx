import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { router } from '@inertiajs/react';

interface SuggestionItem {
    id: number;
    name: string | null;
    photo_path: string;
    categories: string[];
}

interface Suggestion {
    date: string;
    source: 'outfit' | 'pieces' | 'empty';
    outfitId: number | null;
    selected: Record<string, number[]>;
    showOuterwear: boolean;
    weather?: {
        high: number;
        low: number;
        summary: string;
        temperature: string;
    } | null;
    selected_items: Record<string, SuggestionItem[]>;
}

export default function Home() {
    const page = usePage();
    const user = page.props.auth.user as { name: string } | null;
    const [suggestion, setSuggestion] = useState<Suggestion | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchSuggestion = async () => {
        setLoading(true);
        setError(null);
        try {
            const res = await fetch('/api/suggestion', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error(`Erreur ${res.status}`);
            setSuggestion(await res.json());
        } catch (e) {
            setError((e as Error).message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchSuggestion();
    }, []);

    const logWear = async () => {
        if (!suggestion) return;
        const itemIds = Object.values(suggestion.selected).flat();
        try {
            const res = await fetch('/api/wears', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    worn_on: new Date().toISOString().slice(0, 10),
                    outfit_id: suggestion.outfitId,
                    items: itemIds.map((id) => ({ id })),
                }),
            });
            if (!res.ok) throw new Error(`Erreur ${res.status}`);
            alert('Portage enregistré !');
            router.reload({ only: ['suggestion'] });
        } catch (e) {
            alert(`Erreur: ${(e as Error).message}`);
        }
    };

    const slotLabels: Record<string, string> = {
        headwear: 'Tête',
        outerwear: 'Manteau',
        top: 'Haut',
        bottom: 'Bas',
        shoes: 'Chaussures',
    };

    return (
        <div>
            <Head title="Accueil" />

            {user && (
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        Bonjour, {user.name} 👋
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400 mt-2">
                        Voici votre suggestion du jour.
                    </p>
                </div>
            )}

            <div className="max-w-4xl mx-auto">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div className="flex items-center justify-between mb-6">
                        <h2 className="text-2xl font-semibold text-gray-900 dark:text-white">
                            Suggestion du jour
                        </h2>
                        <button
                            onClick={fetchSuggestion}
                            className="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                        >
                            Recommencer
                        </button>
                    </div>

                    {loading ? (
                        <div className="flex items-center justify-center py-12">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        </div>
                    ) : error ? (
                        <div className="text-center py-8">
                            <p className="text-red-600 dark:text-red-400">{error}</p>
                            <button
                                onClick={fetchSuggestion}
                                className="mt-4 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800"
                            >
                                Réessayer
                            </button>
                        </div>
                    ) : suggestion && suggestion.source !== 'empty' ? (
                        <div className="space-y-4">
                            {suggestion.weather && (
                                <div className="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    <span>{suggestion.weather.summary}</span>
                                    <span>☀️ {suggestion.weather.high}°F / {suggestion.weather.low}°F</span>
                                </div>
                            )}
                            <div className="space-y-3">
                                {Object.entries(suggestion.selected).map(([slot, ids]) => {
                                    if (ids.length === 0) return null;
                                    const items = suggestion.selected_items?.[slot] || [];
                                    return (
                                        <div key={slot} className="flex items-start space-x-4">
                                            <div className="w-24 text-sm font-medium text-gray-500 dark:text-gray-400 pt-2">
                                                {slotLabels[slot] || slot}
                                            </div>
                                            <div className="flex flex-wrap gap-3 flex-1">
                                                {items.map((item) => (
                                                    <div key={item.id} className="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center w-40">
                                                        {item.photo_path && (
                                                            <img
                                                                src={`/storage/${item.photo_path}`}
                                                                alt={item.name || 'Pièce'}
                                                                className="w-full h-32 object-cover rounded-lg mb-2"
                                                            />
                                                        )}
                                                        <p className="font-medium text-sm text-gray-900 dark:text-white">
                                                            {item.name || 'Pièce'}
                                                        </p>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                            <div className="flex justify-end mt-6">
                                <button
                                    onClick={logWear}
                                    className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
                                >
                                    Porter cette tenue aujourd'hui
                                </button>
                            </div>
                        </div>
                    ) : (
                        <p className="text-center text-gray-500 dark:text-gray-400 py-8">
                            Aucune suggestion disponible pour le moment. Ajoutez des pièces à votre dressing.
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}