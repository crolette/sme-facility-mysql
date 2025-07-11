import { Table, TableBody, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Document } from '@/types';

export const DocumentsList = ({ document }: { document: Document }) => {
    return (
        <>
            <Table>
                <TableHead>
                    <TableHeadRow>
                        <TableHeadData>File</TableHeadData>
                        <TableHeadData>Size</TableHeadData>
                        <TableHeadData>Filename</TableHeadData>
                        <TableHeadData>Name</TableHeadData>
                        <TableHeadData>Description</TableHeadData>
                        <TableHeadData>Created at</TableHeadData>
                        <TableHeadData>Category</TableHeadData>
                        <TableHeadData>Category</TableHeadData>
                    </TableHeadRow>
                </TableHead>
                <TableBody>
                    {documents.map((document, index) => {
                        const isImage = document.mime_type.startsWith('image/');
                        const isPdf = document.mime_type === 'application/pdf';
                        return (
                            <TableBodyRow key={index}>
                                <TableHeadData>
                                    <a href={route('documents.show', document.id)}>
                                        {isImage && (
                                            <img
                                                src={route('documents.show', document.id)}
                                                alt="preview"
                                                className="mx-auto h-20 w-20 rounded object-cover"
                                            />
                                        )}
                                        {isPdf && <BiSolidFilePdf size={'80px'} className="mx-auto" />}
                                    </a>
                                </TableHeadData>

                                <TableHeadData>{document.sizeMo} Mo</TableHeadData>
                                <TableHeadData>{document.filename}</TableHeadData>
                                <TableHeadData>{document.name}</TableHeadData>
                                <TableHeadData>{document.description}</TableHeadData>
                                <TableHeadData>{document.created_at}</TableHeadData>
                                <TableHeadData>{document.category}</TableHeadData>
                                <TableHeadData>
                                    <Button variant={'destructive'} onClick={() => deleteDocument(document.id)}>
                                        Delete
                                    </Button>
                                    <Button onClick={() => editFile(document.id)}>Edit</Button>
                                </TableHeadData>
                            </TableBodyRow>
                        );
                    })}
                </TableBody>
            </Table>
        </>
    );
};
