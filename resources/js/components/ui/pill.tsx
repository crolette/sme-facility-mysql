import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const pillVariants = cva(
  "rounded-full w-fit",
  {
    variants: {
      variant: {
        default: "bg-sidebar-accent",
        draft: "bg-sidebar-accent",

        'waiting for parts':"bg-chart-3 text-background ",
        open: "bg-destructive",
        ongoing: "bg-emerald-500",
        planned: "bg-ring",
        'in progress': "bg-emerald-300 dark:text-background",
        closed: "bg-green-900",
        completed: "bg-green-900",
        active:
          "bg-emerald-700",
        expired: "bg-amber-800",
        cancelled: "bg-destructive text-background dark:text-foreground",
        low: "bg-slate-400  dark:text-background",
        medium: "bg-amber-400 text-foreground dark:text-background",
        high: "bg-red-400 text-foreground  dark:text-background",
        urgent:"bg-destructive uppercase font-bold  text-background dark:text-foreground"
      },
      size: {
        xs: "py-1 px-2 text-xs",
        sm: "py-2 px-3 text-sm",
        lg: "py-3 px-4 text-lg",
      },
    },
     defaultVariants: {
      variant: "default",
      size: "xs",
    },
   
  }
)

function Pill({
  className,
  variant,
 size,
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  VariantProps<typeof pillVariants> &
  {
    asChild?: boolean;
    variant?: string;
    size?: string;
  }) {
  const Comp = asChild ? Slot : "button"

  return (
    <Comp
      data-slot="button"
      className={cn(pillVariants({ variant, size, className }))}
      {...props}
    />
  )
}

export { Pill, pillVariants }
