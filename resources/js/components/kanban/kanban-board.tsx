import { ItemDetailDialog } from '@/components/kanban/item-detail-dialog';
import { ItemFormDialog } from '@/components/kanban/item-form-dialog';
import { KanbanColumn } from '@/components/kanban/kanban-column';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    STAGE_ORDER,
    cloneItemsByStage,
    emptyItemsByStage,
    type ItemsByStage,
    type KanbanItem,
    type StageKey,
    type StagesMap,
} from '@/types/kanban';
import {
    closestCorners,
    DndContext,
    DragEndEvent,
    DragOverEvent,
    DragOverlay,
    DragStartEvent,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import { arrayMove } from '@dnd-kit/sortable';
import { router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

function itemMatchesQuery(item: KanbanItem, query: string): boolean {
    if (query === '') {
        return true;
    }

    const haystack = `${item.id} ${item.title} ${item.description ?? ''}`.toLowerCase();

    return haystack.includes(query);
}

interface KanbanBoardProps {
    itemsByStage: ItemsByStage;
    stages: StagesMap;
    storeUrl: string;
    updateUrl: (item: KanbanItem) => string;
    destroyUrl: (item: KanbanItem) => string;
    getItemHref?: (item: KanbanItem) => string | undefined;
    createTitle: string;
    createDescription: string;
    editTitle: string;
    editDescription: string;
}

function findStageForItem(itemsByStage: ItemsByStage, id: string): StageKey | null {
    for (const stage of STAGE_ORDER) {
        if ((itemsByStage[stage] ?? []).some((item) => String(item.id) === id)) {
            return stage;
        }
    }

    return null;
}

export function KanbanBoard({
    itemsByStage,
    stages,
    storeUrl,
    updateUrl,
    destroyUrl,
    getItemHref,
    createTitle,
    createDescription,
    editTitle,
    editDescription,
}: KanbanBoardProps) {
    const [board, setBoard] = useState<ItemsByStage>(() => cloneItemsByStage(itemsByStage));
    const [activeItem, setActiveItem] = useState<KanbanItem | null>(null);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [detailOpen, setDetailOpen] = useState(false);
    const [editingItem, setEditingItem] = useState<KanbanItem | null>(null);
    const [detailItem, setDetailItem] = useState<KanbanItem | null>(null);
    const [defaultStage, setDefaultStage] = useState<StageKey>('todo');
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Partial<Record<'title' | 'description' | 'stage', string>>>({});
    const [search, setSearch] = useState('');

    useEffect(() => {
        setBoard(cloneItemsByStage(itemsByStage));
    }, [itemsByStage]);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 6 },
        }),
    );

    const normalizedSearch = search.trim().toLowerCase();

    const visibleBoard = useMemo(() => {
        if (normalizedSearch === '') {
            return board;
        }

        const filtered = emptyItemsByStage();

        for (const stage of STAGE_ORDER) {
            filtered[stage] = (board[stage] ?? []).filter((item) => itemMatchesQuery(item, normalizedSearch));
        }

        return filtered;
    }, [board, normalizedSearch]);

    const visibleCount = useMemo(
        () => STAGE_ORDER.reduce((total, stage) => total + (visibleBoard[stage]?.length ?? 0), 0),
        [visibleBoard],
    );

    const flatItems = useMemo(() => STAGE_ORDER.flatMap((stage) => board[stage] ?? []), [board]);

    const handleDragStart = (event: DragStartEvent) => {
        const item = flatItems.find((entry) => String(entry.id) === String(event.active.id));
        setActiveItem(item ?? null);
    };

    const handleDragOver = (event: DragOverEvent) => {
        const { active, over } = event;

        if (!over) {
            return;
        }

        const activeId = String(active.id);
        const overId = String(over.id);

        const activeStage = findStageForItem(board, activeId);
        const overStage = (STAGE_ORDER.includes(overId as StageKey) ? overId : findStageForItem(board, overId)) as StageKey | null;

        if (!activeStage || !overStage || activeStage === overStage) {
            return;
        }

        setBoard((previous) => {
            const next = cloneItemsByStage(previous);
            const activeIndex = next[activeStage].findIndex((item) => String(item.id) === activeId);

            if (activeIndex === -1) {
                return previous;
            }

            const [moved] = next[activeStage].splice(activeIndex, 1);
            const updated = { ...moved, stage: overStage };

            const overIndex = next[overStage].findIndex((item) => String(item.id) === overId);
            if (overIndex === -1) {
                next[overStage].push(updated);
            } else {
                next[overStage].splice(overIndex, 0, updated);
            }

            return next;
        });
    };

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;
        setActiveItem(null);

        if (!over) {
            setBoard(cloneItemsByStage(itemsByStage));
            return;
        }

        const activeId = String(active.id);
        const overId = String(over.id);
        const activeStage = findStageForItem(board, activeId);
        const overStage = (STAGE_ORDER.includes(overId as StageKey) ? overId : findStageForItem(board, overId)) as StageKey | null;

        if (!activeStage || !overStage) {
            setBoard(cloneItemsByStage(itemsByStage));
            return;
        }

        let nextBoard = cloneItemsByStage(board);

        if (activeStage === overStage) {
            const oldIndex = nextBoard[activeStage].findIndex((item) => String(item.id) === activeId);
            const newIndex = nextBoard[overStage].findIndex((item) => String(item.id) === overId);

            if (oldIndex !== -1 && newIndex !== -1 && oldIndex !== newIndex) {
                nextBoard[activeStage] = arrayMove(nextBoard[activeStage], oldIndex, newIndex).map((item, index) => ({
                    ...item,
                    position: index,
                    stage: activeStage,
                }));
            }
        } else {
            nextBoard = {
                ...nextBoard,
                [activeStage]: nextBoard[activeStage].map((item, index) => ({ ...item, position: index, stage: activeStage })),
                [overStage]: nextBoard[overStage].map((item, index) => ({ ...item, position: index, stage: overStage })),
            };
        }

        setBoard(nextBoard);

        const movedItem = nextBoard[overStage].find((item) => String(item.id) === activeId);

        if (!movedItem) {
            return;
        }

        const original = STAGE_ORDER.flatMap((stage) => itemsByStage[stage] ?? []).find((item) => item.id === movedItem.id);

        if (original && original.stage === movedItem.stage && original.position === movedItem.position) {
            return;
        }

        router.patch(
            updateUrl(movedItem),
            {
                stage: movedItem.stage,
                position: movedItem.position,
            },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const openCreate = (stage: StageKey) => {
        setEditingItem(null);
        setDefaultStage(stage);
        setErrors({});
        setDialogOpen(true);
    };

    const openDetail = (item: KanbanItem) => {
        setDetailItem(item);
        setDetailOpen(true);
    };

    const openEdit = (item: KanbanItem) => {
        setEditingItem(item);
        setDefaultStage(item.stage);
        setErrors({});
        setDialogOpen(true);
    };

    const handleDelete = (item: KanbanItem) => {
        if (!window.confirm(`Supprimer « ${item.title} » ?`)) {
            return;
        }

        router.delete(destroyUrl(item), {
            preserveScroll: true,
        });
    };

    const handleSubmit = (data: { title: string; description: string; stage: StageKey }) => {
        setProcessing(true);
        setErrors({});

        if (editingItem) {
            router.patch(updateUrl(editingItem), data, {
                preserveScroll: true,
                onSuccess: () => {
                    setDialogOpen(false);
                    if (detailItem?.id === editingItem.id) {
                        setDetailItem({ ...editingItem, ...data });
                    }
                },
                onError: (formErrors) => setErrors(formErrors as Partial<Record<'title' | 'description' | 'stage', string>>),
                onFinish: () => setProcessing(false),
            });

            return;
        }

        router.post(storeUrl, data, {
            preserveScroll: true,
            onSuccess: () => setDialogOpen(false),
            onError: (formErrors) => setErrors(formErrors as Partial<Record<'title' | 'description' | 'stage', string>>),
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <>
            <div className="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                <div className="relative max-w-md flex-1">
                    <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                    <Input
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Rechercher par ID, titre ou description…"
                        className="pr-9 pl-9"
                        aria-label="Rechercher"
                    />
                    {search ? (
                        <button
                            type="button"
                            className="text-muted-foreground hover:text-foreground absolute top-1/2 right-2 -translate-y-1/2 rounded p-1"
                            onClick={() => setSearch('')}
                            aria-label="Effacer la recherche"
                        >
                            <X className="size-4" />
                        </button>
                    ) : null}
                </div>
                {normalizedSearch ? (
                    <p className="text-muted-foreground text-sm">
                        {visibleCount} résultat{visibleCount > 1 ? 's' : ''}
                    </p>
                ) : null}
            </div>

            <DndContext
                sensors={sensors}
                collisionDetection={closestCorners}
                onDragStart={handleDragStart}
                onDragOver={handleDragOver}
                onDragEnd={handleDragEnd}
            >
                <div className="flex gap-4 overflow-x-auto pb-4">
                    {STAGE_ORDER.map((stage) => (
                        <KanbanColumn
                            key={stage}
                            stage={stage}
                            label={stages[stage]}
                            items={visibleBoard[stage] ?? []}
                            onAdd={openCreate}
                            onOpen={openDetail}
                            onEdit={openEdit}
                            onDelete={handleDelete}
                        />
                    ))}
                </div>

                <DragOverlay>
                    {activeItem ? (
                        <Card className="border-sidebar-border/70 w-72 shadow-lg">
                            <CardHeader className="p-3">
                                <CardTitle className="text-sm">{activeItem.title}</CardTitle>
                                <Badge variant="secondary" className="mt-2 w-fit">
                                    {stages[activeItem.stage]}
                                </Badge>
                            </CardHeader>
                        </Card>
                    ) : null}
                </DragOverlay>
            </DndContext>

            <ItemDetailDialog
                open={detailOpen}
                onOpenChange={setDetailOpen}
                item={detailItem}
                stages={stages}
                tasksHref={detailItem && getItemHref ? getItemHref(detailItem) : undefined}
                onEdit={openEdit}
            />

            <ItemFormDialog
                open={dialogOpen}
                onOpenChange={setDialogOpen}
                title={editingItem ? editTitle : createTitle}
                description={editingItem ? editDescription : createDescription}
                stages={stages}
                defaultStage={defaultStage}
                item={editingItem}
                onSubmit={handleSubmit}
                processing={processing}
                errors={errors}
            />
        </>
    );
}
