import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const pillVariants = cva(
  "rounded-full bg-sidebar-accent py-1 px-2 text-background dark:text-foreground",
  {
    variants: {
      variant: {
        open:
          "bg-destructive",
        ongoing:
          "bg-emerald-500",
        closed:
          "bg-green-900",
        active:
          "bg-emerald-700",
        expired: "bg-amber-800",
        cancelled: "bg-destructive",
        low: "bg-slate-400  dark:text-background",
        medium: "bg-amber-400 text-foreground dark:text-background",
        high: "bg-red-400 text-foreground  dark:text-background",
        urgent:"bg-destructive uppercase font-bold"

      },
      
    },
   
  }
)

function Pill({
  className,
  variant,
 
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  {
    asChild?: boolean;
    variant?: string;
  }) {
  const Comp = asChild ? Slot : "button"

  return (
    <Comp
      data-slot="button"
      className={cn(pillVariants({ variant: variant as any,  className }))}
      {...props}
    />
  )
}

export { Pill, pillVariants }
