import { Head } from "@inertiajs/react";

export default function AppHead({ title, children }) {
    return (
        <Head>
            <title>{title ? `${title} | Passport.ET` : `Passport.ET`} </title>
            {children}
        </Head>
    );
}
