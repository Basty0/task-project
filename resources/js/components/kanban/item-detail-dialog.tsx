import { MarkdownContent } from '@/components/kanban/markdown-content';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { type KanbanItem, type StagesMap } from '@/types/kanban';
import { Link } from '@inertiajs/react';
import { ListTodo, Pencil } from 'lucide-react';

interface ItemDetailDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    item: KanbanItem | null;
    stages: StagesMap;
    tasksHref?: string;
    onEdit: (item: KanbanItem) => void;
}

export function ItemDetailDialog({ open, onOpenChange, item, stages, tasksHref, onEdit }: ItemDetailDialogProps) {
    if (!item) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="flex max-h-[85vh] max-w-2xl flex-col gap-0 overflow-hidden p-0 sm:max-w-2xl">
                <DialogHeader className="space-y-3 p-6 pb-4">
                    <div className="flex flex-wrap items-start justify-between gap-3 pr-6">
                        <DialogTitle className="text-xl leading-snug">{item.title}</DialogTitle>
                        <Badge variant="secondary">{stages[item.stage]}</Badge>
                    </div>
                    <DialogDescription>Description au format Markdown.</DialogDescription>
                </DialogHeader>

                <Separator />

                <div className="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                    <MarkdownContent content={item.description} />
                </div>

                <Separator />

                <DialogFooter className="flex-row flex-wrap justify-end gap-2 p-6 pt-4 sm:space-x-0">
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Fermer
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={() => {
                            onOpenChange(false);
                            onEdit(item);
                        }}
                    >
                        <Pencil className="size-4" />
                        Modifier
                    </Button>
                    {tasksHref ? (
                        <Button asChild>
                            <Link href={tasksHref} prefetch>
                                <ListTodo className="size-4" />
                                Voir les sous-tâches
                            </Link>
                        </Button>
                    ) : null}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
