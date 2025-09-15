import axios from 'axios';
import { XIcon } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { Input } from './ui/input';

// Types conditionnels pour une API propre
interface BaseSearchableSelectProps<T> {
    searchUrl: string;
    searchParams?: object;
    getDisplayText: (item: T) => string;
    getKey: (item: T) => string | number;
    placeholder?: string;
    className?: string;
    minSearchLength?: number;
    debounceDelay?: number;
    required?: boolean;
}

interface SingleSelectProps<T> extends BaseSearchableSelectProps<T> {
    multiple?: false;
    onSelect: (item: T) => void;
    onDelete: () => void;
    displayValue?: string;
}

interface MultiSelectProps<T> extends BaseSearchableSelectProps<T> {
    multiple: true;
    onSelect: (items: T[]) => void;
    selectedItems: T[];
}

type SearchableSelectProps<T> = SingleSelectProps<T> | MultiSelectProps<T>;

// Composant Chip pour les sélections multiples
interface ChipProps<T> {
    item: T;
    getDisplayText: (item: T) => string;
    onRemove: (item: T) => void;
}

function Chip<T>({ item, getDisplayText, onRemove }: ChipProps<T>) {
    return (
        <span className="mr-2 mb-2 inline-flex items-center rounded-full bg-gray-400 px-2 py-1 text-sm text-blue-800 dark:bg-gray-700 dark:text-gray-200">
            {getDisplayText(item)}
            <button
                type="button"
                onClick={() => onRemove(item)}
                className="ml-1 cursor-pointer text-blue-600 hover:text-blue-800 focus:outline-none dark:text-gray-200 dark:hover:text-gray-100"
            >
                ×
            </button>
        </span>
    );
}

function SearchableSelect<T>(props: SearchableSelectProps<T>) {
    const {
        searchUrl,
        searchParams,
        onSelect,
        onDelete,
        getDisplayText,
        getKey,
        placeholder = 'Rechercher...',
        className = '',
        minSearchLength = 2,
        debounceDelay = 500,
        multiple = false,
        required = false,
    } = props;

    const [items, setItems] = useState<T[]>([]);
    const [search, setSearch] = useState<string>('');
    const [debouncedSearch, setDebouncedSearch] = useState(search);
    const [listIsOpen, setListIsOpen] = useState(false);
    const [isSearching, setIsSearching] = useState(false);

    // Récupération des valeurs selon le mode
    const displayValue = multiple ? '' : (props as SingleSelectProps<T>).displayValue || '';
    const selectedItems = multiple ? (props as MultiSelectProps<T>).selectedItems : [];

    // Debounce de la recherche
    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(search);
        }, debounceDelay);

        return () => {
            clearTimeout(handler);
        };
    }, [search, debounceDelay]);

    // Fonction de recherche
    const fetchItems = async (query: string) => {
        try {
            setIsSearching(true);
            const response = await axios.get(searchUrl, {
                params: { q: query, ...searchParams },
            });
            setItems(response.data.data || response.data);
            setIsSearching(false);
            setListIsOpen(true);
        } catch (error) {
            console.error('Erreur lors de la recherche:', error);
            setIsSearching(false);
            setItems([]);
        }
    };

    // Effet pour déclencher la recherche
    useEffect(() => {
        if (debouncedSearch.length < minSearchLength) {
            setItems([]);
            setListIsOpen(false);
        }

        if (debouncedSearch.length >= minSearchLength) {
            fetchItems(debouncedSearch);
        }
    }, [debouncedSearch, minSearchLength]);

    // Vérifier si un item est déjà sélectionné
    const isItemSelected = (item: T): boolean => {
        if (!multiple) return false;
        return selectedItems.some((selected) => getKey(selected) === getKey(item));
    };

    // Gestion de la sélection
    const handleSelect = (item: T) => {
        if (multiple) {
            // Mode multiple
            if (isItemSelected(item)) {
                return; // Empêcher la duplication
            }

            const newSelectedItems = [...selectedItems, item];
            (onSelect as MultiSelectProps<T>['onSelect'])(newSelectedItems);
            setSearch(''); // Vider la recherche mais garder la liste ouverte
        } else {
            // Mode single
            (onSelect as SingleSelectProps<T>['onSelect'])(item);
            setSearch('');
            setListIsOpen(false);
            setItems([]);
        }
    };

    // Suppression d'un item en mode multiple
    const handleRemove = (itemToRemove: T) => {
        if (!multiple) return;

        const newSelectedItems = selectedItems.filter((item) => getKey(item) !== getKey(itemToRemove));
        (onSelect as MultiSelectProps<T>['onSelect'])(newSelectedItems);
    };

    // Gestion du changement de l'input
    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearch(e.target.value);
    };

    // Fermeture de la liste si clic en dehors
    const handleBlur = () => {
        // Petit délai pour permettre le clic sur un item
        setTimeout(() => {
            setListIsOpen(false);
        }, 200);
    };

    return (
        <div className={`relative ${className}`}>
            <Input
                type="text"
                required={required}
                value={search.length > 0 ? search : displayValue}
                onChange={handleInputChange}
                onBlur={handleBlur}
                onFocus={() => {
                    if (items.length > 0) {
                        setListIsOpen(true);
                    }
                }}
                placeholder={placeholder}
                className="w-full rounded border border-gray-300 p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                autoComplete="off"
            />

            {!multiple && displayValue && <XIcon onClick={onDelete} className="absolute top-1.5 right-2" />}

            {/* Affichage des chips en mode multiple */}
            {multiple && selectedItems.length > 0 && (
                <div className="mt-2">
                    {selectedItems.map((item) => (
                        <Chip key={getKey(item)} item={item} getDisplayText={getDisplayText} onRemove={handleRemove} />
                    ))}
                </div>
            )}

            {listIsOpen && (
                <ul
                    className="absolute z-10 max-h-60 w-full overflow-y-auto rounded-b border border-gray-300 bg-white shadow-lg dark:border-gray-600"
                    role="listbox"
                >
                    {isSearching && <li className="p-2 text-sm text-gray-500">Recherche en cours...</li>}

                    {!isSearching && items.length === 0 && debouncedSearch.length >= minSearchLength && (
                        <li className="bg-background text-foreground p-2 text-sm">Aucun résultat trouvé</li>
                    )}

                    {!isSearching &&
                        items.map((item) => {
                            const isSelected = isItemSelected(item);
                            return (
                                <li
                                    key={getKey(item)}
                                    role="option"
                                    onClick={() => handleSelect(item)}
                                    className={`bg-background text-foreground border-b border-gray-100 p-2 text-sm last:border-b-0 dark:border-gray-600 ${
                                        isSelected
                                            ? 'cursor-not-allowed bg-gray-100 text-gray-500 dark:bg-gray-600 dark:text-white'
                                            : 'cursor-pointer hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-gray-500 dark:hover:text-gray-50'
                                    }`}
                                >
                                    {getDisplayText(item)}
                                    {isSelected && <span className="ml-2 text-xs">(déjà sélectionné)</span>}
                                </li>
                            );
                        })}
                </ul>
            )}
        </div>
    );
}

export default SearchableSelect;
