import { KanbanCard } from '@/components/kanban/kanban-card';
import { Button } from '@/components/ui/button';
import { type KanbanItem, type StageKey } from '@/types/kanban';
import { useDroppable } from '@dnd-kit/core';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { Plus } from 'lucide-react';

interface KanbanColumnProps {
    stage: StageKey;
    label: string;
    items: KanbanItem[];
    onAdd: (stage: StageKey) => void;
    onOpen: (item: KanbanItem) => void;
    onEdit: (item: KanbanItem) => void;
    onDelete: (item: KanbanItem) => void;
}

export function KanbanColumn({ stage, label, items, onAdd, onOpen, onEdit, onDelete }: KanbanColumnProps) {
    const { setNodeRef, isOver } = useDroppable({
        id: stage,
        data: { type: 'column', stage },
    });

    return (
        <div
            className={`bg-muted/40 flex min-h-[28rem] w-72 shrink-0 flex-col rounded-xl border p-3 ${isOver ? 'border-primary/50 bg-primary/5' : 'border-sidebar-border/70 dark:border-sidebar-border'
                }`}
        >
            <div className="mb-3 flex items-center justify-between gap-2 px-1">
                <div>
                    <h2 className="text-sm font-semibold">{label}</h2>
                    <p className="text-muted-foreground text-xs">
                        {items.length} élément{items.length > 1 ? 's' : ''}
                    </p>
                </div>
                <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onAdd(stage)} aria-label={`Ajouter à ${label}`}>
                    <Plus className="size-4" />
                </Button>
            </div>

            <div ref={setNodeRef} className="flex flex-1 flex-col gap-2">
                <SortableContext items={items.map((item) => String(item.id))} strategy={verticalListSortingStrategy}>
                    {items.map((item) => (
                        <KanbanCard key={item.id} item={item} stageLabel={label} onOpen={onOpen} onEdit={onEdit} onDelete={onDelete} />
                    ))}
                </SortableContext>
            </div>
        </div>
    );
}
