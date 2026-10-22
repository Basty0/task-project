<?php

namespace App\Mcp\Tools;

use App\Enums\Stage;
use App\Mcp\Concerns\InteractsWithTicketBoard;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Liste les étapes du parcours Kanban (clé technique + label FR). À appeler avant de créer ou déplacer un ticket/tâche.')]
#[IsReadOnly]
class ListStages extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'list_stages';

    public function handle(Request $request): ResponseFactory
    {
        return $this->jsonResult([
            'stages' => collect(Stage::cases())
                ->map(fn (Stage $stage): array => [
                    'value' => $stage->value,
                    'label' => $stage->label(),
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
