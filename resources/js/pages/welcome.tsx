import { Button } from '@/components/ui/button';

export default function Welcome() {
    return (
        <div className="bg-logo flex h-screen w-screen flex-col items-center justify-center">
            <img src="images/logo.png" alt="" className="w-96" />
            <a href="login">
                <Button>Login</Button>
            </a>
        </div>
    );
}
