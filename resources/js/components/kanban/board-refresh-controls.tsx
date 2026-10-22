import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { router, usePoll } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import { useEffect, useState } from 'react';

const STORAGE_KEY = 'kanban-auto-refresh';

interface BoardRefreshControlsProps {
    only: string[];
}

export function BoardRefreshControls({ only }: BoardRefreshControlsProps) {
    const [autoRefresh, setAutoRefresh] = useState(() => {
        if (typeof window === 'undefined') {
            return false;
        }

        return window.localStorage.getItem(STORAGE_KEY) === '1';
    });
    const [refreshing, setRefreshing] = useState(false);

    const { start, stop } = usePoll(
        60_000,
        {
            only,
            preserveScroll: true,
            preserveState: true,
        },
        {
            autoStart: false,
        },
    );

    useEffect(() => {
        window.localStorage.setItem(STORAGE_KEY, autoRefresh ? '1' : '0');

        if (autoRefresh) {
            start();
        } else {
            stop();
        }

        return () => stop();
    }, [autoRefresh, start, stop]);

    const refreshNow = () => {
        setRefreshing(true);

        router.reload({
            only,
            preserveScroll: true,
            preserveState: true,
            onFinish: () => setRefreshing(false),
        });
    };

    return (
        <div className="flex flex-wrap items-center gap-3">
            <Button type="button" variant="outline" size="sm" onClick={refreshNow} disabled={refreshing}>
                <RefreshCw className={`size-4 ${refreshing ? 'animate-spin' : ''}`} />
                Rafraîchir
            </Button>

            <label className="text-muted-foreground flex cursor-pointer items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    className="border-input text-primary focus-visible:ring-ring size-4 rounded border"
                    checked={autoRefresh}
                    onChange={(event) => setAutoRefresh(event.target.checked)}
                />
                <Label className="cursor-pointer font-normal">Auto (1 min)</Label>
            </label>
        </div>
    );
}
