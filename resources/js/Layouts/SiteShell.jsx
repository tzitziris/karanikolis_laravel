import Footer from '../Components/Footer';
import Navbar from '../Components/Navbar';

export default function SiteShell({ children }) {
    return (
        <div className="min-h-screen bg-ink-1 text-bone" data-site-shell>
            <Navbar />
            <main id="site-content">{children}</main>
            <Footer />
        </div>
    );
}
