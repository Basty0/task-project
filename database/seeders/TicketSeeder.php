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
        $user = User::query()->where('email', 'test@example.com')->first()
            ?? User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $tickets = [
            ['title' => 'Refonte page d\'accueil', 'stage' => Stage::Todo, 'position' => 0],
            ['title' => 'Corriger le login SSO', 'stage' => Stage::Todo, 'position' => 1],
            ['title' => 'API notifications', 'stage' => Stage::InProgress, 'position' => 0],
            ['title' => 'Mise à jour documentation', 'stage' => Stage::Done, 'position' => 0],
        ];

        foreach ($tickets as $ticketData) {
            $ticket = Ticket::query()->create([
                'user_id' => $user->id,
                'title' => $ticketData['title'],
                'description' => 'Ticket de démonstration pour le board Kanban.',
                'stage' => $ticketData['stage'],
                'position' => $ticketData['position'],
            ]);

            Task::factory()->create([
                'ticket_id' => $ticket->id,
                'title' => 'Analyser le besoin',
                'stage' => Stage::Todo,
                'position' => 0,
            ]);

            Task::factory()->create([
                'ticket_id' => $ticket->id,
                'title' => 'Implémenter la solution',
                'stage' => Stage::InProgress,
                'position' => 0,
            ]);

            Task::factory()->create([
                'ticket_id' => $ticket->id,
                'title' => 'Valider en recette',
                'stage' => Stage::Done,
                'position' => 0,
            ]);
        }
    }
}
