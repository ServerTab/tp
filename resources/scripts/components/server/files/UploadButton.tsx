import axios from 'axios';
import getFileUploadUrl from '@/api/server/files/getFileUploadUrl';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import React, { useEffect, useRef, useState } from 'react';
import styled from 'styled-components/macro';
import { ModalMask } from '@/components/elements/Modal';
import Fade from '@/components/elements/Fade';
import useEventListener from '@/plugins/useEventListener';
import { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { ServerContext } from '@/state/server';
import { WithClassname } from '@/components/types';
import Portal from '@/components/elements/Portal';

const InnerContainer = styled.div`
    max-width: 600px;
    ${tw`bg-black w-full border-4 border-primary-500 border-dashed rounded p-10 mx-10`}
`;

function isFileOrDirectory(event: DragEvent): boolean {
    if (!event.dataTransfer?.types) {
        return false;
    }

    return event.dataTransfer.types.some((value) => value.toLowerCase() === 'files');
}

export default ({ className }: WithClassname) => {
    const fileUploadInput = useRef<HTMLInputElement>(null);
    const [timeouts, setTimeouts] = useState<NodeJS.Timeout[]>([]);
    const [visible, setVisible] = useState(false);
    const { mutate } = useFileManagerSwr();
    const { addError, clearAndAddHttpError } = useFlashKey('files');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const appendFileUpload = ServerContext.useStoreActions((actions) => actions.files.appendFileUpload);
    const removeFileUpload = ServerContext.useStoreActions((actions) => actions.files.removeFileUpload);

    useEventListener(
        'dragenter',
        (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (isFileOrDirectory(e)) {
                return setVisible(true);
            }
        },
        { capture: true }
    );

    useEventListener('dragexit', () => setVisible(false), { capture: true });

    useEffect(() => {
        if (!visible) return;

        const hide = () => setVisible(false);

        window.addEventListener('keydown', hide);
        return () => {
            window.removeEventListener('keydown', hide);
        };
    }, [visible]);

    useEffect(() => {
        return () => timeouts.forEach(clearTimeout);
    }, []);

    const onFileSubmission = (files: FileList) => {
        const formData: FormData[] = [];

        clearAndAddHttpError();
        const list = Array.from(files);
        if (list.some((file) => !file.type && file.size % 4096 === 0)) {
            return addError('Folder uploads are not supported at this time.', 'Error');
        }

        Array.from(files).forEach((file) => {
            const form = new FormData();
            form.append('files', file);
            formData.push(form);
        });

        if (formData.length === 0) {
            return;
        }

        Promise.all(
            Array.from(formData).map((f) =>
                getFileUploadUrl(uuid).then((url) =>
                    axios.post(`${url}&directory=${directory}`, f, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        onUploadProgress: (data: ProgressEvent) => {
                            // @ts-expect-error this is valid
                            const name = f.getAll('files')[0].name;

                            appendFileUpload({ name: name, loaded: data.loaded, total: data.total });

                            if (data.loaded === data.total) {
                                const timeout = setTimeout(() => removeFileUpload(name), 500);
                                setTimeouts((t) => [...t, timeout]);
                            }
                        },
                    })
                )
            )
        )
            .then(() => mutate())
            .catch(clearAndAddHttpError);
    };

    return (
        <>
            <Portal>
                <Fade appear in={visible} timeout={75} key={'upload_modal_mask'} unmountOnExit>
                    <ModalMask
                        onClick={() => setVisible(false)}
                        onDragOver={(e) => e.preventDefault()}
                        onDrop={(e) => {
                            e.preventDefault();
                            e.stopPropagation();

                            setVisible(false);
                            if (!e.dataTransfer?.files.length) return;

                            onFileSubmission(e.dataTransfer.files);
                        }}
                    >
                        <div css={tw`w-full flex items-center justify-center pointer-events-none`}>
                            <InnerContainer>
                                <p css={tw`text-lg text-neutral-200 text-center`}>Drag and drop files to upload.</p>
                            </InnerContainer>
                        </div>
                    </ModalMask>
                </Fade>
            </Portal>
            <input
                type={'file'}
                ref={fileUploadInput}
                css={tw`hidden`}
                onChange={(e) => {
                    if (!e.currentTarget.files) return;

                    onFileSubmission(e.currentTarget.files);
                    if (fileUploadInput.current) {
                        fileUploadInput.current.files = null;
                    }
                }}
            />
            <Button className={className} onClick={() => fileUploadInput.current && fileUploadInput.current.click()}>
                Upload
            </Button>
        </>
    );
};
