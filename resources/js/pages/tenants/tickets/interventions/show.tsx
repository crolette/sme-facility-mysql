import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Intervention } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowIntervention({ intervention }: { intervention: Intervention }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Show intervention`,
            href: `/interventions/${intervention}`,
        },
    ];

    // const [ticketItem, setTicketItem] = useState(ticket);

    // const fetchTicket = async () => {
    //     try {
    //         const response = await axios.get(route(`api.tickets.get`, ticket.id));
    //         setTicketItem(response.data.data);
    //     } catch (error) {
    //         console.error('Erreur lors de la recherche :', error);
    //     }
    // };

    // const closeTicket = async (id: number) => {
    //     try {
    //         await axios.patch(route('api.tickets.close', id));
    //         fetchTicket();
    //     } catch (error) {
    //         console.error('Erreur lors de la suppression', error);
    //     }
    // };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Intervention" />
            {/* {ticket.status !== 'closed' && (
                <Button variant={'destructive'} onClick={() => closeTicket(ticket.id)}>
                    Close
                </Button>
            )} */}
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <p>Status: {intervention.status}</p>
                    <p>Description : {intervention.description}</p>
                    {/* <p>Reporter : {intervention.reporter ? ticketItem.reporter.full_name : ticketItem.reporter_email}</p> */}
                    {/* <p>Closer: {intervention.closer?.full_name}</p> */}
                </div>
                {/* <div className="flex gap-4">
                    {ticketItem.pictures &&
                        ticketItem.pictures.map((picture) => {
                            return (
                                <img
                                    key={picture.id}
                                    src={route('api.pictures.show', picture.id)}
                                    alt=""
                                    className="aspect-square h-64 object-cover"
                                />
                            );
                        })}
                </div> */}
            </div>
        </AppLayout>
    );
}
