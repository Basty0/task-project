import { KanbanBoard } from '@/components/kanban/kanban-board';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type ItemsByStage, type KanbanItem, type StageKey, type StagesMap } from '@/types/kanban';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface TicketSummary {
    id: number;
    title: string;
    description: string | null;
    stage: StageKey;
}

interface TicketsShowProps {
    ticket: TicketSummary;
    tasksByStage: ItemsByStage;
    stages: StagesMap;
}

export default function TicketsShow({ ticket, tasksByStage, stages }: TicketsShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: ticket.title,
            href: `/tickets/${ticket.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={ticket.title} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-col gap-3">
                    <Link href={route('dashboard')} className="text-muted-foreground hover:text-foreground inline-flex w-fit items-center gap-1 text-sm">
                        <ArrowLeft className="size-4" />
                        Retour au dashboard
                    </Link>

                    <div className="flex flex-wrap items-center gap-3">
                        <Heading title={ticket.title} description={ticket.description ?? 'Sous-tâches organisées par les mêmes étapes.'} />
                        <Badge variant="secondary">{stages[ticket.stage]}</Badge>
                    </div>
                </div>

                <KanbanBoard
                    itemsByStage={tasksByStage}
                    stages={stages}
                    storeUrl={route('tasks.store', ticket.id)}
                    updateUrl={(item: KanbanItem) => route('tasks.update', [ticket.id, item.id])}
                    destroyUrl={(item: KanbanItem) => route('tasks.destroy', [ticket.id, item.id])}
                    createTitle="Nouvelle sous-tâche"
                    createDescription="Ajoutez une sous-tâche à ce ticket."
                    editTitle="Modifier la sous-tâche"
                    editDescription="Mettez à jour le titre, la description ou l'étape."
                />
            </div>
        </AppLayout>
    );
}
