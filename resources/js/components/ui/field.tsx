import { Label } from "./label";


export default function field({ label, text }: { label: string; text: string | number | Element }) {
    
    return (
        <div className="flex gap-4 items-center">
            <Label className="first-letter:uppercase">{label}</Label>
            <p className="border-input flex w-fit rounded-md border bg-secondary px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none  md:text-sm">{text}</p>
            </div>
    )
}