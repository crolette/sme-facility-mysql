import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Documents } from '@/types';
import { BiSolidFilePdf } from 'react-icons/bi';
import { Button } from '../ui/button';

interface DocumentManagerProps {
    itemCodeId?: number | string;
    getDocumentsUrl?: string;
    uploadRoute?: string;
    editRoute?: string;
    documents: Documents[];
    deleteRoute?: string;
    showRoute?: string;
    canAdd?: boolean;
}


export const DocumentsList = ({
    itemCodeId,
    getDocumentsUrl,
    editRoute,
    documents,
    uploadRoute,
    deleteRoute,
    showRoute,
    canAdd = true,
}: DocumentManagerProps) => {


    return (
   
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>File</TableHeadData>
                            <TableHeadData>Size</TableHeadData>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Description</TableHeadData>
                            <TableHeadData>Category</TableHeadData>
                            <TableHeadData>Filename</TableHeadData>
                            <TableHeadData>Created at</TableHeadData>
                            <TableHeadData></TableHeadData>
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {documents.map((document, index) => {
                            const isImage = document.mime_type.startsWith('image/');
                            const isPdf = document.mime_type === 'application/pdf';
                            return (
                                <TableBodyRow key={index}>
                                    <TableBodyData>
                                        <a href={route(showRoute, document.id)}>
                                            {isImage && (
                                                <img
                                                    src={route(showRoute, document.id)}
                                                    alt="preview"
                                                    className="mx-auto h-10 w-10 rounded object-cover"
                                                />
                                            )}
                                            {isPdf && <BiSolidFilePdf size={'40px'} className="mx-auto" />}
                                        </a>
                                    </TableBodyData>

                                    <TableBodyData>{document.sizeMo} Mo</TableBodyData>
                                    <TableBodyData>{document.name}</TableBodyData>
                                    <TableBodyData>{document.description}</TableBodyData>
                                    <TableBodyData>{document.category}</TableBodyData>
                                    <TableBodyData>{document.filename}</TableBodyData>
                                    <TableBodyData>{document.created_at}</TableBodyData>
                                    {/* <TableBodyData>
                                        <Button variant={'destructive'} onClick={() => deleteDocument(document.id)}>
                                            Delete
                                        </Button>
                                        <Button onClick={() => editFile(document.id)}>Edit</Button>
                                    </TableBodyData> */}
                                </TableBodyRow>
                            );
                        })}
                    </TableBody>
                </Table>
            
    );
};
