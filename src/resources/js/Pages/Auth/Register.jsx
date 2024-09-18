import { useEffect } from "react";
import GuestLayout from "@/Layouts/GuestLayout";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { Head, Link, useForm, usePage, router } from "@inertiajs/react";
import DismissibleBanner from "@/Components/DismissibleBanner";

export default function Register() {
    const { props } = usePage();
    const { amount } = props;
    console.log("amount on register", amount);
    const { data, setData, post, processing, errors, reset, redirect } =
        useForm({
            first_name: "",
            last_name: "",
            phone_number: "",
            email: "",
            password: "",
            password_confirmation: "",
            plan: "",
            amount: amount,
        });

    useEffect(() => {
        return () => {
            reset("password", "password_confirmation");
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route("register"), {
            onSuccess: () => {
                if (amount) {
                    route("payment", { amount: amount });
                }
            },
        });


    };

    return (
        <GuestLayout>
            <Head title="Register" />
            <DismissibleBanner text="Register for a FREE account to check your passport status" bgColor={'bg-indigo-600'}/>
            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="first_name" value="First Name" />

                    <TextInput
                        id="first_name"
                        name="first_name"
                        value={data.first_name}
                        className="mt-1 block w-full"
                        autoComplete="first_name"
                        isFocused={true}
                        onChange={(e) => setData("first_name", e.target.value)}
                        required
                    />

                    <InputError message={errors.first_name} className="mt-2" />
                </div>
                <div>
                    <InputLabel htmlFor="last_name" value="Last Name" />

                    <TextInput
                        id="last_name"
                        name="last_name"
                        value={data.last_name}
                        className="mt-1 block w-full"
                        autoComplete="last_name"
                        isFocused={true}
                        onChange={(e) => setData("last_name", e.target.value)}
                        required
                    />

                    <InputError message={errors.last_name} className="mt-2" />
                </div>
                <div>
                    <InputLabel htmlFor="phone_number" value="Phone Number" />

                    <TextInput
                        id="phone_number"
                        name="phone_number"
                        value={data.phone_number}
                        type="number"
                        className="mt-1 block w-full"
                        autoComplete="phone_number"
                        isFocused={true}
                        onChange={(e) =>
                            setData("phone_number", e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.phone_number}
                        className="mt-2"
                    />
                </div>
                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData("email", e.target.value)}
                        required
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData("password", e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) =>
                            setData("password_confirmation", e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                {/* will be uncomented wehen we start premeum plan */}
                {/* <div>
                    <InputLabel htmlFor="premium" value="Premium Plan:" />

                    <TextInput
                        type="checkbox"
                        name="plan"
                        className="mt-1 "
                        autoComplete="plan"
                        checked={data.plan === "premium"} // Assuming 'premium' is the value you want to set when checked
                        onChange={(e) =>
                            setData(
                                "plan",
                                e.target.checked ? "premium" : "free"
                            )
                        }
                        id="premium"
                    />

                    <InputError message={errors.plan} className="mt-2" />
                </div> */}

                <div className="flex items-center justify-end mt-4">
                    <Link
                        href={route("login")}
                        className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    >
                        Already registered?
                    </Link>


                    <PrimaryButton className="ms-4" disabled={processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
