import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';

import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors',
    {
        variants: {
            variant: {
                default: 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-900 dark:bg-brand-950 dark:text-brand-300',
                success: 'border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-900 dark:bg-teal-950 dark:text-teal-300',
                warning: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
                danger: 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
                outline: 'border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-300',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

export interface BadgeProps
    extends React.HTMLAttributes<HTMLDivElement>,
        VariantProps<typeof badgeVariants> {}

function Badge({ className, variant, ...props }: BadgeProps) {
    return <div className={cn(badgeVariants({ variant }), className)} {...props} />;
}

export { Badge, badgeVariants };
