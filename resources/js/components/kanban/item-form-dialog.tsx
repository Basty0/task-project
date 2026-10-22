import InputError from '@/components/input-error';
import { MarkdownContent } from '@/components/kanban/markdown-content';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type KanbanItem, type StageKey, type StagesMap } from '@/types/kanban';
import { FormEvent, useEffect, useState } from 'react';

interface ItemFormDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description: string;
    stages: StagesMap;
    defaultStage?: StageKey;
    item?: KanbanItem | null;
    onSubmit: (data: { title: string; description: string; stage: StageKey }) => void;
    processing?: boolean;
    errors?: Partial<Record<'title' | 'description' | 'stage', string>>;
}

export function ItemFormDialog({
    open,
    onOpenChange,
    title,
    description,
    stages,
    defaultStage = 'todo',
    item = null,
    onSubmit,
    processing = false,
    errors = {},
}: ItemFormDialogProps) {
    const [formTitle, setFormTitle] = useState('');
    const [formDescription, setFormDescription] = useState('');
    const [formStage, setFormStage] = useState<StageKey>(defaultStage);
    const [preview, setPreview] = useState(false);

    useEffect(() => {
        if (!open) {
            return;
        }

        setFormTitle(item?.title ?? '');
        setFormDescription(item?.description ?? '');
        setFormStage(item?.stage ?? defaultStage);
        setPreview(false);
    }, [open, item, defaultStage]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();

        onSubmit({
            title: formTitle,
            description: formDescription,
            stage: formStage,
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-xl">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <DialogHeader>
                        <DialogTitle>{title}</DialogTitle>
                        <DialogDescription>{description}</DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-2">
                        <Label htmlFor="kanban-title">Titre</Label>
                        <Input
                            id="kanban-title"
                            value={formTitle}
                            onChange={(event) => setFormTitle(event.target.value)}
                            required
                            autoFocus
                        />
                        <InputError message={errors.title} />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center justify-between gap-2">
                            <Label htmlFor="kanban-description">Description (Markdown)</Label>
                            <div className="flex gap-1">
                                <Button type="button" size="sm" variant={preview ? 'ghost' : 'secondary'} onClick={() => setPreview(false)}>
                                    Éditer
                                </Button>
                                <Button type="button" size="sm" variant={preview ? 'secondary' : 'ghost'} onClick={() => setPreview(true)}>
                                    Aperçu
                                </Button>
                            </div>
                        </div>

                        {preview ? (
                            <div className="border-input bg-background min-h-[140px] rounded-md border px-3 py-2">
                                <MarkdownContent content={formDescription} />
                            </div>
                        ) : (
                            <textarea
                                id="kanban-description"
                                value={formDescription}
                                onChange={(event) => setFormDescription(event.target.value)}
                                rows={8}
                                placeholder={'## Contexte\n\n- Point 1\n- Point 2\n\n**Important** : détails…'}
                                className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[140px] w-full rounded-md border px-3 py-2 font-mono text-sm focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            />
                        )}
                        <p className="text-muted-foreground text-xs">Supporte titres, listes, gras, liens, code (GFM).</p>
                        <InputError message={errors.description} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="kanban-stage">Étape</Label>
                        <select
                            id="kanban-stage"
                            value={formStage}
                            onChange={(event) => setFormStage(event.target.value as StageKey)}
                            className="border-input bg-background ring-offset-background focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2"
                        >
                            {(Object.entries(stages) as [StageKey, string][]).map(([key, label]) => (
                                <option key={key} value={key}>
                                    {label}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.stage} />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {item ? 'Enregistrer' : 'Créer'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
