export default function Placeholder({ message }) {
    return (
        <main className="min-h-screen px-6 py-10">
            <h1 className="font-display text-3xl font-black text-bone">
                Προσωρινή σελίδα
            </h1>
            <p className="mt-3 text-base text-bone-dim">{message}</p>
        </main>
    );
}
