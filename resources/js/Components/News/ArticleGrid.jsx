import ArticleCard from './ArticleCard';

export default function ArticleGrid({ articles, emptyMessage, revealAttribute }) {
    if (!articles.length) {
        return (
            <div className="border border-line-strong bg-ink-2 px-5 py-10 sm:px-8 sm:py-14">
                <p className="font-display text-3xl font-black uppercase text-bone sm:text-4xl">
                    {emptyMessage ?? 'Δεν υπάρχουν νέα ακόμα.'}
                </p>
                <p className="mx-auto mt-4 max-w-2xl text-sm leading-6 text-bone-dim">
                    Όταν δημοσιευτούν οι πρώτες ανακοινώσεις της σχολής, θα
                    εμφανιστούν εδώ με τη σειρά της ημερομηνίας τους.
                </p>
            </div>
        );
    }

    return (
        <div className="grid items-stretch gap-px sm:grid-cols-2 xl:grid-cols-3">
            {articles.map((article, index) => (
                <ArticleCard
                    article={article}
                    index={index}
                    key={article.id}
                    revealAttribute={revealAttribute}
                />
            ))}
        </div>
    );
}
