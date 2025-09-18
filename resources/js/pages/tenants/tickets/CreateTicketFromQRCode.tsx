import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Asset, TenantBuilding, TenantFloor, TenantRoom, TenantSite, Ticket } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { BadgeAlert, BadgeCheck } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

type FormDataTicket = {
    ticket_id: number | null;
    location_type: string;
    location_code: string;
    description: string;
    reporter_email: string;
    being_notified: boolean;
    pictures: File[];
    website: string;
};

export default function CreateTicketFromQRCode({ item, location_type }: { item: Asset | TenantSite | TenantBuilding | TenantFloor | TenantRoom; location_type: string }) {

    const updateTicketData = {
        website: '',
        ticket_id: null,
        location_type: location_type,
        location_code: item.reference_code,
        being_notified: false,
        description: '',
        reporter_email: '',
        pictures: [],
    };

    const [newTicketData, setNewTicketData] = useState<FormDataTicket>(updateTicketData);

    const submitTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post(route('api.tickets.store'), newTicketData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                setShowSuccessModale(true);
                setNewTicketData(updateTicketData);
            }
        } catch (error) {
            console.log(error.response.data);
            setErrors(error?.response.data.errors);
            setShowErrorModale(true);
        }
    };
    const [errors, setErrors] = useState<{ [key: string]: string }>();
    const [showSuccessModale, setShowSuccessModale] = useState<boolean>(false);
    const [showErrorModale, setShowErrorModale] = useState<boolean>(false);

    return (
        <>
            <Head title="Tickets" />
            <div className="bg-accent flex h-svh items-center justify-center">
                <div className="border-sidebar-border bg-sidebar mx-auto flex w-1/2 flex-col rounded-md border p-4 shadow-xl">
                    <h1>Create new ticket</h1>
                    <div className="my-4">
                        <h3>{item.name}</h3>
                        <p>{item.description}</p>
                        <p>{item.category}</p>
                    </div>
                    <form onSubmit={submitTicket} className="flex flex-col gap-4">
                        <Input
                            name="website"
                            value={newTicketData.website}
                            tabIndex={-1}
                            autoComplete="off"
                            style={{ display: 'none' }}
                            onChange={(e) =>
                                setNewTicketData((prev) => ({
                                    ...prev,
                                    website: e.target.value,
                                }))
                            }
                        />
                        <Label htmlFor="reporter_email">E-mail address</Label>
                        <Input
                            id="reporter_email"
                            type="text"
                            name="reporter_email"
                            value={newTicketData.reporter_email}
                            required
                            autoComplete="on"
                            placeholder="Reporter email"
                            onChange={(e) =>
                                setNewTicketData((prev) => ({
                                    ...prev,
                                    reporter_email: e.target.value,
                                }))
                            }
                        />
                        <InputError message={errors?.reporter_email} />
                        <Label htmlFor="description">Description of the problem</Label>
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
                        <InputError message={errors?.description} />
                        <Label htmlFor="pictures">Pictures</Label>
                        <Input
                            id="pictures"
                            type="file"
                            name="pictures"
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
                        <InputError message={errors?.pictures} />
                        <InputError message={errors ? Object.getOwnPropertyDescriptor(errors, 'pictures.0')?.value : ''} />
                        <InputError message={errors ? Object.getOwnPropertyDescriptor(errors, 'pictures.1')?.value : ''} />
                        <InputError message={errors ? Object.getOwnPropertyDescriptor(errors, 'pictures.2')?.value : ''} />

                        <div className="flex items-center gap-4">
                            <Label htmlFor="notified">Do you want to be notified when the ticket is closed ? </Label>
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
                </div>
                {showSuccessModale && (
                    <div className="bg-background/50 fixed inset-0 z-50">
                        <div className="bg-background/20 flex h-dvh items-center justify-center">
                            <div className="bg-background flex items-center justify-center p-4 text-center md:w-1/3">
                                <div className="flex flex-col items-center gap-4">
                                    <BadgeCheck size={48} className="text-chart-2" />
                                    <p className="text-chart-2 mx-auto text-3xl font-bold">Thank you</p>
                                    <p className="mx-auto">Ticket submitted</p>
                                    <p className="mx-auto">You can now close this window.</p>
                                    <div className="mx-auto flex gap-4">
                                        {/* <Button variant={'secondary'} onClick={onCancel}>
                                        Close
                                    </Button> */}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
                {showErrorModale && (
                    <div className="bg-background/50 fixed inset-0 z-50">
                        <div className="bg-background/20 flex h-dvh items-center justify-center">
                            <div className="bg-background flex items-center justify-center p-4 text-center md:w-1/3">
                                <div className="flex flex-col items-center gap-4">
                                    <BadgeAlert size={48} className="text-destructive" />
                                    <p className="text-destructive mx-auto text-3xl font-bold">Error</p>
                                    <p className="mx-auto">Error while submitting. Try again</p>
                                    <div className="mx-auto flex gap-4">
                                        <Button variant={'secondary'} onClick={() => setShowErrorModale(false)}>
                                            Close
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}
