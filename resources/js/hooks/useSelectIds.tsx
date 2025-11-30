import { useEffect, useState } from 'react';

interface useSelectedIdsProps {
    storageKey: string;
    maxSelection?: number;
}

export const useSelectIds = ({ storageKey, maxSelection = 100 }: useSelectedIdsProps) => {
    const [selectedIds, setSelectedIds] = useState<number[]>(() => {
        const saved = sessionStorage.getItem(storageKey);
        return saved ? JSON.parse(saved) : [];
    });

    useEffect(() => {
        sessionStorage.setItem(storageKey, JSON.stringify(selectedIds));
    }, [selectedIds, storageKey]);

    const handleSelectIds = (id: number) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((itemId) => itemId !== id) : prev.length < maxSelection ? [...prev, id] : [...prev],
        );
    };

    const handleSelectAllIds = (items: { id: number }[]) => {
        const itemIds = items.map((item) => item.id);

        if (items.every((item) => selectedIds.includes(item.id))) {
            setSelectedIds((prev) => prev.filter((id) => !itemIds.includes(id)));
        } else {
            setSelectedIds((prev) => {
                const idsToAdd = itemIds.filter((id) => !prev.includes(id));
                const availableSlots = maxSelection - prev.length;
                const newIds = idsToAdd.slice(0, availableSlots);
                return [...prev, ...newIds];
            });
        }
    };

    const clearSelection = () => {
        sessionStorage.removeItem(storageKey);
        setSelectedIds([]);
    };

    return { selectedIds, handleSelectIds, handleSelectAllIds, clearSelection };
};
