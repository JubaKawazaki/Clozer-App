import { Head, useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Item {
    id: number;
    name: string | null;
    photo_path: string;
    categories: string[];
}

interface ItemsPageProps {
    items: Item[];
}

export default function CreateOutfit() {
    const { items } = usePage<ItemsPageProps>().props;
    const { post, processing, errors } = useForm({
        name: '',
        items: [] as Array<{ id: number; slot: string; position: number }>,
    });

    const [selectedSlots, setSelectedSlots] = useState<Record<string, number>>({
        headwear: 0,
        outerwear: 0,
        top: 0,
        bottom: 0,
        shoes: 0,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('outfits.store'));
    };

    const itemsByCategory = items.reduce((acc, item) => {
        item.categories.forEach((category) => {
            if (!acc[category]) {
                acc[category] = [];
            }
            acc[category].push(item);
        });
        return acc;
    }, {} as Record<string, Item[]>);

    const slots = ['headwear', 'outerwear', 'top', 'bottom', 'shoes'] as const;

    return (
        <div>
            <Head title="Créer une tenue" />

            <div className="max-w-4xl mx-auto">
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                    Créer une tenue
                </h1>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <form onSubmit={submit}>
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

                        {/* Rangées */}
                        {slots.map((slot) => (
                            <div key={slot} className="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    {slot.charAt(0).toUpperCase() + slot.slice(1)}
                                    {selectedSlots[slot] > 0 && (
                                        <span className="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                            (Couche {selectedSlots[slot]})
                                        </span>
                                    )}
                                </h3>

                                <div className="flex items-center space-x-4 mb-4">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setSelectedSlots({
                                                ...selectedSlots,
                                                [slot]: Math.max(0, selectedSlots[slot] - 1),
                                            })
                                        }
                                        className="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors disabled:opacity-50"
                                        disabled={selectedSlots[slot] === 0}
                                    >
                                        -
                                    </button>
                                    <span className="text-gray-700 dark:text-gray-300">
                                        {selectedSlots[slot] + 1} pièce{selectedSlots[slot] + 1 > 1 ? 's' : ''}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setSelectedSlots({
                                                ...selectedSlots,
                                                [slot]: Math.min(2, selectedSlots[slot] + 1),
                                            })
                                        }
                                        className="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors disabled:opacity-50"
                                        disabled={selectedSlots[slot] === 2}
                                    >
                                        +
                                    </button>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {itemsByCategory[slot]?.slice(0, selectedSlots[slot] + 1).map((item) => (
                                        <div key={item.id} className="flex items-center space-x-3 bg-white dark:bg-gray-800 p-3 rounded-lg">
                                            <img
                                                src={item.photo_path}
                                                alt={item.name || `Pièce ${item.id}`}
                                                className="w-16 h-16 object-cover rounded"
                                            />
                                            <div className="flex-1">
                                                <p className="font-medium text-gray-900 dark:text-white">
                                                    {item.name || `Pièce ${item.id}`}
                                                </p>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                                    {item.categories.join(', ')}
                                                </p>
                                            </div>
                                            <input
                                                type="radio"
                                                name={`items[${slot}]`}
                                                value={item.id}
                                                checked={post.items.some((i) => i.id === item.id && i.slot === slot)}
                                                onChange={(e) => {
                                                    const newItem = {
                                                        id: parseInt(e.target.value),
                                                        slot,
                                                        position: selectedSlots[slot],
                                                    };
                                                    post({
                                                        ...post,
                                                        items: [newItem],
                                                    });
                                                }}
                                                className="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500"
                                            />
                                        </div>
                                    ))}
                                    {itemsByCategory[slot]?.length === 0 && (
                                        <p className="text-gray-500 dark:text-gray-400 col-span-full text-center py-4">
                                            Aucune pièce trouvée pour cette catégorie.
                                        </p>
                                    )}
                                </div>
                            </div>
                        ))}

                        {/* Boutons */}
                        <div className="flex justify-end space-x-4">
                            <Link
                                href={route('outfits.index')}
                                className="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Annuler
                            </Link>
                            <button
                                type="submit"
                                disabled={processing || post.items.length === 0}
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
