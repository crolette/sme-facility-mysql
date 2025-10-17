// Props pour Modal
interface ModaleProps {
    title?: string;
    children: React.ReactNode;
}

export default function ModaleForm({ title, children }: ModaleProps) {
    return (
        <>
            <div className="bg-background/50 fixed inset-0 z-50 overflow-y-auto">
                <div className="bg-background/20 flex min-h-dvh items-center justify-center">
                    <div className="bg-background max-h-[90vh] overflow-y-auto p-10">
                        <h4>{title}</h4>
                        {children}
                    </div>
                </div>
            </div>
        </>
    );
}
