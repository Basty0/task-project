import { cn } from '@/lib/utils';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

interface MarkdownContentProps {
    content: string | null | undefined;
    className?: string;
    emptyLabel?: string;
}

export function MarkdownContent({ content, className, emptyLabel = 'Aucune description.' }: MarkdownContentProps) {
    const trimmed = content?.trim();

    if (!trimmed) {
        return <p className={cn('text-muted-foreground text-sm italic', className)}>{emptyLabel}</p>;
    }

    return (
        <div
            className={cn(
                'prose prose-sm dark:prose-invert max-w-none text-sm',
                'prose-headings:mb-2 prose-headings:mt-4 prose-headings:font-semibold prose-headings:tracking-tight',
                'prose-p:my-2 prose-p:leading-relaxed',
                'prose-ul:my-2 prose-ol:my-2 prose-li:my-0.5',
                'prose-pre:bg-muted prose-pre:rounded-md prose-pre:p-3',
                'prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-code:py-0.5 prose-code:before:content-none prose-code:after:content-none',
                'prose-blockquote:border-muted-foreground/30 prose-blockquote:text-muted-foreground',
                'prose-a:text-primary prose-a:underline-offset-2',
                className,
            )}
        >
            <ReactMarkdown remarkPlugins={[remarkGfm]}>{trimmed}</ReactMarkdown>
        </div>
    );
}
