import { LayoutGrid, TableIcon } from "lucide-react"
import { useGridTableLayoutContext } from "../tenant/gridTableLayoutContext"
import { cn } from "@/lib/utils";

export default function DisplayGridTableIndex() {

    const { setLayout, layout } = useGridTableLayoutContext();

    return (
        <div className="flex gap-2 items-center">
            <p className="text-xs">Layout: </p>
        <div className="flex gap-2">
                <div className={cn(layout === 'grid' ? "bg-border text-background dark:text-foreground": "bg-sidebar"," hover:bg-sidebar-accent cursor-pointer rounded-md p-2")} onClick={() => setLayout('grid')}>
                <LayoutGrid size={20} />
                </div>
                
            <div className={cn(layout === 'table' ? "bg-border text-background dark:text-foreground": "bg-sidebar"," hover:bg-sidebar-accent cursor-pointer rounded-md p-2")} onClick={() => setLayout('table')}>
                <TableIcon size={20} />
            </div>
            </div>
            </div>
    );
}