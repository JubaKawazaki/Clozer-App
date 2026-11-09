import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { usePage } from '@inertiajs/react';

interface Outfit {
    id: number;
    name: string | null;
    photo_path: string | null;
    items: Array<{
        id: number;
        slot: string;
        position: number;
        item: {
            id: number;
            name: string | null;
            photo_path: string;
        };
    }>;
}

interface OutfitsPageProps {
    outfits: Outfit[];
}

export default function Outfits() {
    const { outfits } = usePage<OutfitsPageProps>().props;
    const [slotFilter, setSlotFilter] = useState<string>('all');

    // Placeholder pour les slots (à remplacer par un appel API backend)
    const slots = ['all', 'headwear', 'outerwear', 'top', 'bottom', 'shoes'];

    const filteredOutfits = slots.includes(slotFilter)
        ? slotFilter === 'all'
            ? outfits
            : outfits.filter((outfit) =>
                  outfit.items.some((item) => item.slot === slotFilter)
              )
        : outfits;

    return (
        <div>
            <Head title="Tenues" />

            <div className="mb-8 flex items-center justify-between">
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                    Tenues
                </h1>
                <Link
                    href={route('outfits.create')}
                    className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    Créer une tenue
                </Link>
            </div>

            {/* Filtres par slot */}
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-6">
                <div className="flex items-center space-x-4">
                    <select
                        value={slotFilter}
                        onChange={(e) => setSlotFilter(e.target.value)}
                        className="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        {slots.map((slot) => (
                            <option key={slot} value={slot}>
                                {slot === 'all' ? 'Tous les slots' : slot.charAt(0).toUpperCase() + slot.slice(1)}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Grille des tenues */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {filteredOutfits.map((outfit) => (
                    <div key={outfit.id} className="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div className="relative">
                            {outfit.photo_path ? (
                                <img
                                    src={outfit.photo_path}
                                    alt={outfit.name || `Tenue ${outfit.id}`}
                                    className="w-full h-64 object-cover"
                                />
                            ) : (
                                <div className="w-full h-64 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span className="text-gray-500 dark:text-gray-400">
                                        Pas de photo
                                    </span>
                                </div>
                            )}
                            {outfit.name && (
                                <div className="absolute top-4 right-4 bg-indigo-600 text-white px-3 py-1 rounded-full text-sm">
                                    {outfit.name}
                                </div>
                            )}
                        </div>
                        <div className="p-4">
                            <h3 className="font-medium text-gray-900 dark:text-white mb-2">
                                {outfit.name || `Tenue ${outfit.id}`}
                            </h3>
                            <div className="flex flex-wrap gap-2">
                                {outfit.items.slice(0, 3).map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center space-x-2 bg-gray-50 dark:bg-gray-700 px-3 py-1 rounded-full"
                                    >
                                        <img
                                            src={item.item.photo_path}
                                            alt={item.item.name || `Pièce ${item.item.id}`}
                                            className="w-8 h-8 object-cover rounded-full"
                                        />
                                        <span className="text-sm text-gray-700 dark:text-gray-300">
                                            {item.slot}
                                        </span>
                                    </div>
                                ))}
                                {outfit.items.length > 3 && (
                                    <span className="text-sm text-gray-500 dark:text-gray-400">
                                        +{outfit.items.length - 3} plus
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {filteredOutfits.length === 0 && (
                <div className="text-center py-12">
                    <p className="text-gray-500 dark:text-gray-400">
                        Aucune tenue trouvée.
                    </p>
                </div>
            )}
        </div>
    );
}
