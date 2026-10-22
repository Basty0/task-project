<?php

namespace Database\Seeders;

use App\Enums\Stage;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'djaffardaoud0@gmail.com')->first()
            ?? User::factory()->create([
                'name' => 'Admin',
                'email' => 'djaffardaoud0@gmail.com',
                'is_admin' => true,
            ]);

        $tickets = [
            [
                'title' => 'Refonte page d\'accueil',
                'stage' => Stage::Todo,
                'position' => 0,
                'description' => "## Objectif\n\nModerniser la landing avec un hero full-bleed.\n\n### À livrer\n\n- Maquette validée\n- Intégration React\n- **SEO** de base\n",
            ],
            [
                'title' => 'Corriger le login SSO',
                'stage' => Stage::WaitingClarification,
                'position' => 0,
                'description' => "## Blocage\n\nLe fournisseur IdP renvoie une erreur `invalid_grant`.\n\n> En attente du client IdP et du redirect URI exact.\n\n- [ ] Confirmer l'URL de callback\n- [ ] Vérifier les scopes\n",
            ],
            [
                'title' => 'API notifications',
                'stage' => Stage::InProgress,
                'position' => 0,
                'description' => "## Scope\n\nEndpoint `POST /notifications` + file d'attente.\n\n```http\nPOST /api/notifications\nContent-Type: application/json\n```\n",
            ],
            [
                'title' => 'Mise à jour documentation',
                'stage' => Stage::Done,
                'position' => 0,
                'description' => "Documentation publiée sur Confluence.\n\n- Guide d'installation\n- FAQ support\n",
            ],
            [
                'title' => 'Widget chat legacy',
                'stage' => Stage::Abandoned,
                'position' => 0,
                'description' => "## Abandonné\n\nRemplacé par le nouveau canal support.\n\n**Raison** : dette technique trop élevée vs valeur métier.\n",
            ],
        ];

        foreach ($tickets as $ticketData) {
            $ticket = Ticket::query()->create([
                'user_id' => $user->id,
                'title' => $ticketData['title'],
                'description' => $ticketData['description'],
                'stage' => $ticketData['stage'],
                'position' => $ticketData['position'],
            ]);

            Task::factory()->create([
                'ticket_id' => $ticket->id,
                'title' => 'Analyser le besoin',
                'description' => "### Analyse\n\nLister les critères d'acceptation.",
                'stage' => Stage::Todo,
                'position' => 0,
            ]);

            Task::factory()->create([
                'ticket_id' => $ticket->id,
                'title' => 'Clarifier avec le métier',
                'description' => 'Questions ouvertes avant de démarrer.',
                'stage' => Stage::WaitingClarification,
                'position' => 0,
            ]);

            Task::factory()->create([
                'ticket_id' => $ticket->id,
                'title' => 'Implémenter la solution',
                'description' => 'Travail en cours.',
                'stage' => Stage::InProgress,
                'position' => 0,
            ]);

            Task::factory()->create([
                'ticket_id' => $ticket->id,
                'title' => 'Valider en recette',
                'description' => 'Checklist QA terminée.',
                'stage' => Stage::Done,
                'position' => 0,
            ]);
        }
    }
}
