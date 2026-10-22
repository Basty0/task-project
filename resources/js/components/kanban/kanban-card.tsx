import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type KanbanItem } from '@/types/kanban';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { Check, Copy, Eye, GripVertical, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface KanbanCardProps {
    item: KanbanItem;
    stageLabel: string;
    onOpen: (item: KanbanItem) => void;
    onEdit: (item: KanbanItem) => void;
    onDelete: (item: KanbanItem) => void;
}

export function KanbanCard({ item, stageLabel, onOpen, onEdit, onDelete }: KanbanCardProps) {
    const [copied, setCopied] = useState(false);
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id: String(item.id),
        data: { type: 'card', item },
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const copyId = async () => {
        await navigator.clipboard.writeText(String(item.id));
        setCopied(true);
        window.setTimeout(() => setCopied(false), 1500);
    };

    return (
        <Card
            ref={setNodeRef}
            style={style}
            className={`group border-sidebar-border/70 dark:border-sidebar-border gap-0 py-0 shadow-none ${isDragging ? 'opacity-60 ring-2 ring-primary/40' : ''}`}
        >
            <CardHeader className="flex flex-row items-start gap-2 space-y-0 p-3 pb-2">
                <button
                    type="button"
                    className="text-muted-foreground hover:text-foreground mt-0.5 cursor-grab touch-none opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-100 active:cursor-grabbing max-sm:opacity-100"
                    aria-label="Déplacer"
                    {...attributes}
                    {...listeners}
                >
                    <GripVertical className="size-4" />
                </button>
                <div className="min-w-0 flex-1">
                    <CardTitle className="text-sm leading-snug">
                        <button
                            type="button"
                            onClick={() => onOpen(item)}
                            className="hover:text-primary line-clamp-2 w-full text-left font-medium transition-colors"
                        >
                            {item.title}
                        </button>
                    </CardTitle>
                    <div className="mt-2 flex flex-wrap items-center gap-1.5">
                        <button
                            type="button"
                            onClick={copyId}
                            className="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 font-mono text-[11px] transition-colors"
                            title="Copier l'identifiant"
                            aria-label={`Copier l'identifiant ${item.id}`}
                        >
                            #{item.id}
                            {copied ? <Check className="size-3 text-green-600" /> : <Copy className="size-3" />}
                        </button>
                        <Badge variant="secondary">{stageLabel}</Badge>
                    </div>
                </div>
            </CardHeader>
            {item.description ? (
                <CardContent className="text-muted-foreground px-3 pb-2 pl-10 text-xs leading-relaxed">
                    <button type="button" onClick={() => onOpen(item)} className="line-clamp-2 w-full text-left hover:underline">
                        {item.description.replace(/[#*_`>\-\[\]()]/g, ' ').replace(/\s+/g, ' ').trim()}
                    </button>
                </CardContent>
            ) : null}
            <CardContent className="flex justify-end gap-1 px-3 pb-3 opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-100 max-sm:opacity-100">
                <Button type="button" variant="ghost" size="icon" className="size-8" onClick={copyId} aria-label="Copier l'identifiant">
                    {copied ? <Check className="size-3.5 text-green-600" /> : <Copy className="size-3.5" />}
                </Button>
                <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onOpen(item)} aria-label="Voir le détail">
                    <Eye className="size-3.5" />
                </Button>
                <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onEdit(item)} aria-label="Modifier">
                    <Pencil className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="text-destructive hover:text-destructive size-8"
                    onClick={() => onDelete(item)}
                    aria-label="Supprimer"
                >
                    <Trash2 className="size-3.5" />
                </Button>
            </CardContent>
        </Card>
    );
}
