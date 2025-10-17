import InputError from '@/components/input-error';
import ModaleForm from '@/components/ModaleForm';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Asset, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { BadgeAlert, BadgeCheck, Camera, Folder, Loader } from 'lucide-react';
import { FormEventHandler, useEffect, useRef, useState } from 'react';

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

export default function CreateTicketFromQRCode({
    item,
    location_type,
}: {
    item: Asset | TenantSite | TenantBuilding | TenantFloor | TenantRoom;
    location_type: string;
}) {
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

    const fileCameraRef = useRef(null);
    const fileInputRef = useRef(null);

    const [newTicketData, setNewTicketData] = useState<FormDataTicket>(updateTicketData);

    const submitTicket: FormEventHandler = async (e) => {
        setIsProcessing(true);
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
                setIsProcessing(false);
            }
        } catch (error) {
            console.log(error.response.data);
            setErrors(error?.response.data.errors);
            setShowErrorModale(true);
            setIsProcessing(false);
        }
    };

    const [errors, setErrors] = useState<{ [key: string]: string }>();
    const [isProcessing, setIsProcessing] = useState(false);
    const [showSuccessModale, setShowSuccessModale] = useState<boolean>(false);
    const [showErrorModale, setShowErrorModale] = useState<boolean>(false);
    const [previewUrls, setpreviewUrls] = useState(null);

    console.log(newTicketData);

    useEffect(() => {
        setpreviewUrls(Array.from(newTicketData.pictures).map((file) => ({ url: URL.createObjectURL(file), name: file.name })));
    }, [newTicketData.pictures]);

    console.log(previewUrls);

    return (
        <>
            <Head title="Tickets" />
            <div className="bg-accent flex items-center justify-center">
                <div className="border-sidebar-border bg-sidebar mx-auto flex w-10/12 flex-col rounded-md border p-4 shadow-xl md:w-1/2">
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
                            type="email"
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

                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-1">
                            <div
                                className="bg-border space-y-3 rounded-md border-2 p-4 text-center sm:hidden"
                                onClick={() => fileCameraRef.current?.click()}
                            >
                                <p>Take a picture with my camera</p>
                                <Camera className="mx-auto" />
                                <Input
                                    ref={fileCameraRef}
                                    type="file"
                                    accept="image/*"
                                    capture="user"
                                    className="pointer-events-none hidden"
                                    onChange={(e) => {
                                        // const pictures = { pictures: };
                                        setNewTicketData((prev) => ({
                                            ...prev,
                                            pictures: e.target.files,
                                        }));
                                    }}
                                />
                            </div>

                            <div
                                className="bg-border space-y-3 rounded-md border-2 p-4 text-center"
                                onClick={() => {
                                    fileInputRef.current?.click();
                                }}
                            >
                                <p>Upload a picture from my phone</p>
                                <Folder className="mx-auto" />
                                <p className="text-xs italic">Maximum 3 pictures</p>
                                <Input
                                    ref={fileInputRef}
                                    id="pictures"
                                    type="file"
                                    name="pictures"
                                    multiple
                                    className="pointer-events-none hidden"
                                    accept="image/png, image/jpeg, image/jpg"
                                    onChange={(e) => {
                                        // const pictures = { pictures: };
                                        setNewTicketData((prev) => ({
                                            ...prev,
                                            pictures: e.target.files,
                                        }));
                                    }}
                                />
                            </div>
                        </div>
                        <div className="pointer-events-none flex flex-wrap items-center justify-evenly gap-2">
                            {previewUrls &&
                                previewUrls.map((preview, index) => (
                                    <div className="max-w-1/4" key={index}>
                                        <img src={preview.url} alt="Aperçu" className="mx-auto aspect-square max-h-40 rounded object-cover" />
                                    </div>
                                ))}
                        </div>

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
                    <ModaleForm>
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
                    </ModaleForm>
                )}
                {showErrorModale && (
                    <ModaleForm>
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
                    </ModaleForm>
                )}

                {isProcessing && (
                    <ModaleForm>
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">
                                Processing<span>...</span>
                            </p>
                            <p className="mx-auto">Ticket is being submitted...</p>
                        </div>
                    </ModaleForm>
                )}
            </div>
        </>
    );
}
