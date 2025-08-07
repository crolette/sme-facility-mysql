import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Asset, Intervention, TenantBuilding, TenantFloor, TenantRoom, TenantSite, Ticket } from '@/types';
import { Head } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

type FormDataTicket = {
    ticket_id: number | null;
    location_type: string;
    location_code: string;
    description: string;
    reporter_email: string;
    being_notified: boolean;
    pictures: File[];
};

export default function CreateTicket({
    ticket,
    interventions,
    item,
}: {
    ticket?: Ticket;
    interventions: Intervention[];
    item: Asset | TenantSite | TenantBuilding | TenantFloor | TenantRoom;
}) {
    console.log(ticket);

    console.log(interventions);
    console.log(item);

    const updateTicketData = {
        ticket_id: null,
        location_type: '',
        location_code: '',
        being_notified: false,
        description: '',
        reporter_email: '',
        pictures: [],
    };

    const [newTicketData, setNewTicketData] = useState<FormDataTicket>(updateTicketData);

    const submitTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('SUBMIT');
        try {
            alert('Ticket submitted : ' + newTicketData.description);
            // const response = await axios.patch(route('api.tickets.update', newTicketData.ticket_id), newTicketData);
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <>
            <Head title="Tickets" />
            <div>{item.name}</div>
            <div>{item.category}</div>
            <div>{item.description}</div>
            <div>{item.location?.category}</div>
            <form onSubmit={submitTicket} className="flex flex-col gap-4">
                <Input
                    type="text"
                    name="email"
                    value={newTicketData.reporter_email}
                    required
                    placeholder="Reporter email"
                    onChange={(e) =>
                        setNewTicketData((prev) => ({
                            ...prev,
                            reporter_email: e.target.value,
                        }))
                    }
                />
                <Textarea
                    name="description"
                    id="description"
                    required
                    minLength={10}
                    maxLength={250}
                    placeholder="Ticket description"
                    onChange={(e) =>
                        setNewTicketData((prev) => ({
                            ...prev,
                            description: e.target.value,
                        }))
                    }
                    value={newTicketData.description}
                />

                <Input
                    type="file"
                    multiple
                    accept="image/png, image/jpeg, image/jpg"
                    onChange={(e) => {
                        // const pictures = { pictures: };
                        setNewTicketData((prev) => ({
                            ...prev,
                            pictures: e.target.files,
                        }));
                    }}
                />

                <div className="flex items-center gap-4">
                    <Label htmlFor="notified">Do you want to be notified of changes ? </Label>
                    <Checkbox
                        id="notified"
                        checked={newTicketData.being_notified}
                        onClick={() => {
                            setNewTicketData((prev) => ({
                                ...prev,
                                being_notified: !newTicketData.being_notified,
                            }));
                        }}
                    />
                </div>
                <Button>Add new ticket</Button>
            </form>
        </>
    );
}
