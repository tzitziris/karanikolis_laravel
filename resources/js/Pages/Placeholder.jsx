export default function Placeholder({ message }) {
    return (
        <main className="min-h-screen px-6 py-10">
            <h1 className="text-2xl font-semibold text-neutral-950">
                Προσωρινή σελίδα
            </h1>
            <p className="mt-3 text-base text-neutral-700">{message}</p>
        </main>
    );
}
