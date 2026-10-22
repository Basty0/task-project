import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';

interface McpSettingsProps {
    hasToken: boolean;
    tokenPrefix: string | null;
    tokenCreatedAt: string | null;
    mcpEndpoint: string;
    plainTextToken?: string | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'MCP Agent',
        href: '/settings/mcp',
    },
];

export default function McpSettings({
    hasToken,
    tokenPrefix,
    tokenCreatedAt,
    mcpEndpoint,
    plainTextToken = null,
}: McpSettingsProps) {
    const generateForm = useForm({});
    const revokeForm = useForm({});
    const [copied, setCopied] = useState<'token' | 'config' | null>(null);

    const agentConfig = useMemo(() => {
        const token = plainTextToken ?? '<VOTRE_TOKEN_MCP>';

        return JSON.stringify(
            {
                mcpServers: {
                    tickets: {
                        url: mcpEndpoint,
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    },
                },
            },
            null,
            2,
        );
    }, [mcpEndpoint, plainTextToken]);

    const copyText = async (value: string, kind: 'token' | 'config') => {
        await navigator.clipboard.writeText(value);
        setCopied(kind);
        window.setTimeout(() => setCopied(null), 2000);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="MCP Agent" />

            <SettingsLayout>
                <div className="space-y-8">
                    <HeadingSmall
                        title="Token MCP personnel"
                        description="Générez un token pour votre agent IA. Il ne pourra gérer que vos tickets et sous-tâches."
                    />

                    <div className="space-y-3 rounded-lg border p-4">
                        <p className="text-sm">
                            {hasToken ? (
                                <>
                                    Token actif : <code className="bg-muted rounded px-1.5 py-0.5 text-xs">{tokenPrefix}…</code>
                                    {tokenCreatedAt ? (
                                        <span className="text-muted-foreground"> — créé le {new Date(tokenCreatedAt).toLocaleString()}</span>
                                    ) : null}
                                </>
                            ) : (
                                <span className="text-muted-foreground">Aucun token généré pour le moment.</span>
                            )}
                        </p>

                        <div className="flex flex-wrap gap-2">
                            <Button
                                type="button"
                                disabled={generateForm.processing}
                                onClick={() => {
                                    if (hasToken && !window.confirm('Régénérer le token ? L’ancien ne fonctionnera plus.')) {
                                        return;
                                    }

                                    generateForm.post(route('mcp.store'), { preserveScroll: true });
                                }}
                            >
                                {hasToken ? 'Régénérer le token' : 'Générer mon token'}
                            </Button>

                            {hasToken ? (
                                <Button
                                    type="button"
                                    variant="destructive"
                                    disabled={revokeForm.processing}
                                    onClick={() => {
                                        if (!window.confirm('Révoquer le token MCP ?')) {
                                            return;
                                        }

                                        revokeForm.delete(route('mcp.destroy'), { preserveScroll: true });
                                    }}
                                >
                                    Révoquer
                                </Button>
                            ) : null}
                        </div>
                    </div>

                    {plainTextToken ? (
                        <div className="space-y-3 rounded-lg border border-amber-500/40 bg-amber-500/5 p-4">
                            <p className="text-sm font-medium text-amber-700 dark:text-amber-300">
                                Copiez ce token maintenant — il ne sera plus réaffiché.
                            </p>
                            <div className="grid gap-2">
                                <Label htmlFor="mcp-token">Token</Label>
                                <div className="flex gap-2">
                                    <Input id="mcp-token" readOnly value={plainTextToken} className="font-mono text-xs" />
                                    <Button type="button" variant="secondary" onClick={() => copyText(plainTextToken, 'token')}>
                                        {copied === 'token' ? 'Copié' : 'Copier'}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    ) : null}

                    <div className="space-y-3 rounded-lg border p-4">
                        <HeadingSmall
                            title="Configuration agent"
                            description="Collez ceci dans la config MCP de votre agent (Cursor, Claude, etc.)."
                        />

                        <div className="grid gap-2">
                            <Label>Endpoint</Label>
                            <Input readOnly value={mcpEndpoint} className="font-mono text-xs" />
                        </div>

                        <div className="grid gap-2">
                            <Label>JSON MCP</Label>
                            <pre className="bg-muted overflow-x-auto rounded-md p-3 font-mono text-xs whitespace-pre-wrap">{agentConfig}</pre>
                            <Button type="button" variant="outline" className="w-fit" onClick={() => copyText(agentConfig, 'config')}>
                                {copied === 'config' ? 'Config copiée' : 'Copier la config'}
                            </Button>
                        </div>

                        <p className="text-muted-foreground text-xs leading-relaxed">
                            Alternative locale (stdio) : <code>php artisan mcp:start tickets</code> avec{' '}
                            <code>MCP_TOKEN=&lt;votre_token&gt;</code> dans l’environnement de l’agent.
                        </p>
                    </div>

                    <Button type="button" variant="ghost" onClick={() => router.visit(route('dashboard'))}>
                        Retour au dashboard
                    </Button>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
