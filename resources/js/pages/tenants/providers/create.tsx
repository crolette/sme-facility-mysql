import { User } from '@/types';

export default function UserCreateUpdate({ user }: { user?: User }) {
    console.log(user ?? null);
    return;
}
