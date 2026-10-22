export type StageKey = 'todo' | 'waiting_clarification' | 'in_progress' | 'done' | 'abandoned';

export type StagesMap = Record<StageKey, string>;

export interface KanbanItem {
    id: number;
    title: string;
    description: string | null;
    stage: StageKey;
    position: number;
}

export type ItemsByStage = Record<StageKey, KanbanItem[]>;

export const STAGE_ORDER: StageKey[] = ['todo', 'waiting_clarification', 'in_progress', 'done', 'abandoned'];

export function emptyItemsByStage(): ItemsByStage {
    return {
        todo: [],
        waiting_clarification: [],
        in_progress: [],
        done: [],
        abandoned: [],
    };
}

export function cloneItemsByStage(itemsByStage: ItemsByStage): ItemsByStage {
    return {
        todo: [...itemsByStage.todo],
        waiting_clarification: [...(itemsByStage.waiting_clarification ?? [])],
        in_progress: [...itemsByStage.in_progress],
        done: [...itemsByStage.done],
        abandoned: [...(itemsByStage.abandoned ?? [])],
    };
}
