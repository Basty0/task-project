import Heading from '@/components/heading';
import { BoardRefreshControls } from '@/components/kanban/board-refresh-controls';
import { KanbanBoard } from '@/components/kanban/kanban-board';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type ItemsByStage, type KanbanItem, type StagesMap } from '@/types/kanban';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface DashboardProps {
    ticketsByStage: ItemsByStage;
    stages: StagesMap;
}

export default function Dashboard({ ticketsByStage, stages }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title="Dashboard"
                        description="Cliquez sur un ticket pour lire sa description Markdown. Glissez-le entre les étapes du parcours."
                    />
                    <BoardRefreshControls only={['ticketsByStage', 'stages']} />
                </div>

                <KanbanBoard
                    itemsByStage={ticketsByStage}
                    stages={stages}
                    storeUrl={route('tickets.store')}
                    updateUrl={(item: KanbanItem) => route('tickets.update', item.id)}
                    destroyUrl={(item: KanbanItem) => route('tickets.destroy', item.id)}
                    getItemHref={(item: KanbanItem) => route('tickets.show', item.id)}
                    createTitle="Nouveau ticket"
                    createDescription="Créez un ticket à placer sur le board."
                    editTitle="Modifier le ticket"
                    editDescription="Mettez à jour le titre, la description ou l'étape (Markdown supporté)."
                />
            </div>
        </AppLayout>
    );
}
