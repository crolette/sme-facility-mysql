import { Provider } from '@/types';

export default function ProviderCreateUpdate({ provider }: { provider?: Provider }) {
    console.log(provider ?? null);
    return;
}
