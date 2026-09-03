import { Link, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { animateMobileMenuOpen } from '../animation/pageAnimation';
import SiteImage from './SiteImage';

const focusableSelector =
    'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

const links = [
    { href: '/', label: 'Αρχική' },
    { href: '/coaches', label: 'Ομάδα' },
    { href: '/schedule', label: 'Πρόγραμμα' },
    { href: '/news', label: 'Νέα' },
    { href: '/about', label: 'Σχετικά' },
];

function isActivePath(currentPath, href) {
    return href === '/' ? currentPath === href : currentPath.startsWith(href);
}

function visibleFocusableElements(root) {
    return Array.from(root.querySelectorAll(focusableSelector)).filter(
        (element) => !element.hasAttribute('disabled'),
    );
}

export default function Navbar() {
    const { url } = usePage();
    const currentPath = new URL(url, window.location.origin).pathname;
    const [isOpen, setIsOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);
    const menuButtonRef = useRef(null);
    const menuPanelRef = useRef(null);

    useEffect(() => {
        function onScroll() {
            setScrolled(window.scrollY > 8);
        }

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        if (!isOpen) {
            return undefined;
        }

        const appRoot = document.getElementById('app');
        const previousBodyOverflow = document.body.style.overflow;
        const previousHtmlOverflow = document.documentElement.style.overflow;
        const opener = menuButtonRef.current;
        let cleanupAnimation = () => {};

        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';

        const focusPanel = () => {
            const panel = menuPanelRef.current;
            const firstFocusable = panel
                ? visibleFocusableElements(panel)[0]
                : null;

            (firstFocusable ?? panel)?.focus({ preventScroll: true });
        };

        focusPanel();
        appRoot?.setAttribute('inert', '');
        appRoot?.setAttribute('aria-hidden', 'true');
        const focusFrame = window.requestAnimationFrame(focusPanel);

        try {
            cleanupAnimation = animateMobileMenuOpen(menuPanelRef.current);
        } catch (error) {
            console.error('Η κίνηση του μενού δεν ξεκίνησε.', error);
        }

        function onKeyDown(event) {
            const panel = menuPanelRef.current;

            if (event.key === 'Escape') {
                event.preventDefault();
                setIsOpen(false);

                return;
            }

            if (event.key !== 'Tab' || !panel) {
                return;
            }

            const focusable = visibleFocusableElements(panel);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (!first || !last) {
                event.preventDefault();
                panel.focus({ preventScroll: true });

                return;
            }

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        document.addEventListener('keydown', onKeyDown);

        return () => {
            window.cancelAnimationFrame(focusFrame);
            document.removeEventListener('keydown', onKeyDown);
            cleanupAnimation();
            document.body.style.overflow = previousBodyOverflow;
            document.documentElement.style.overflow = previousHtmlOverflow;
            appRoot?.removeAttribute('inert');
            appRoot?.removeAttribute('aria-hidden');
            opener?.focus({ preventScroll: true });
        };
    }, [isOpen]);

    return (
        <header
            className={`sticky top-0 z-50 transition-colors duration-500 ${
                scrolled ? 'bg-ink-0/94 backdrop-blur-sm' : 'bg-ink-0'
            }`}
            data-site-header
        >
            <nav
                aria-label="Κύρια πλοήγηση"
                className="mx-auto flex h-20 max-w-[1500px] items-center justify-between px-5 sm:px-8 lg:px-12"
            >
                <Link
                    className="group flex min-h-11 min-w-11 items-center justify-center gap-3 sm:justify-start"
                    href="/"
                    onClick={() => setIsOpen(false)}
                    prefetch={['mount', 'hover']}
                >
                    <SiteImage
                        alt="Μαχητές Ελευθερούπολης"
                        className="h-12 w-auto object-contain"
                        image="site-logo"
                        slot="logo"
                    />
                    <span className="hidden font-display text-xl font-black uppercase text-bone sm:block">
                        ΜΑΧΗΤΕΣ <span className="text-blood">/</span>{' '}
                        ΕΛΕΥΘΕΡΟΥΠΟΛΗΣ
                    </span>
                </Link>

                <div className="hidden items-center gap-1 lg:flex">
                    {links.map((link) => {
                        const active = isActivePath(currentPath, link.href);

                        return (
                            <Link
                                aria-current={active ? 'page' : undefined}
                                className="group relative flex min-h-11 items-center px-4 py-2"
                                href={link.href}
                                key={link.href}
                                prefetch={['mount', 'hover']}
                            >
                                <span
                                    className={`text-[11px] font-medium uppercase transition-colors ${
                                        active
                                            ? 'text-bone'
                                            : 'text-bone-dim group-hover:text-bone'
                                    }`}
                                >
                                    {link.label}
                                </span>
                                <span
                                    className={`absolute bottom-0 left-4 right-4 h-px origin-left bg-blood transition-transform duration-300 ${
                                        active
                                            ? 'scale-x-100'
                                            : 'scale-x-0 group-hover:scale-x-100'
                                    }`}
                                />
                            </Link>
                        );
                    })}
                </div>

                <button
                    aria-controls="mobile-navigation"
                    aria-expanded={isOpen}
                    aria-label={isOpen ? 'Κλείσιμο μενού' : 'Άνοιγμα μενού'}
                    className="relative flex h-11 w-11 flex-col items-center justify-center gap-1.5 border border-line-strong text-bone transition-colors hover:border-blood focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood lg:hidden"
                    onClick={() => setIsOpen((open) => !open)}
                    ref={menuButtonRef}
                    type="button"
                >
                    <span
                        className={`block h-px w-5 origin-center bg-current transition-transform duration-300 ${
                            isOpen ? 'translate-y-[7px] rotate-45' : ''
                        }`}
                    />
                    <span
                        className={`block h-px w-5 bg-current transition-opacity duration-200 ${
                            isOpen ? 'opacity-0' : 'opacity-100'
                        }`}
                    />
                    <span
                        className={`block h-px w-5 origin-center bg-current transition-transform duration-300 ${
                            isOpen ? '-translate-y-[7px] -rotate-45' : ''
                        }`}
                    />
                </button>
            </nav>

            {isOpen
                ? createPortal(
                      <div
                          aria-label="Κύρια πλοήγηση"
                          aria-modal="true"
                          className="fixed inset-0 z-[1000] overflow-y-auto bg-[#050505] lg:hidden"
                          id="mobile-navigation"
                          ref={menuPanelRef}
                          role="dialog"
                          tabIndex={-1}
                      >
                          <div className="mx-auto flex min-h-[100dvh] max-w-[1500px] flex-col px-5 pb-10 pt-5 sm:px-8 sm:pb-12">
                              <div className="flex h-15 shrink-0 items-center justify-between border-b border-line">
                                  <span className="font-mono text-[10px] font-bold uppercase text-blood">
                                      Πλοήγηση
                                  </span>
                                  <button
                                      aria-label="Κλείσιμο μενού"
                                      className="flex h-11 w-11 cursor-pointer items-center justify-center border border-line-strong text-bone transition-colors hover:border-blood hover:text-blood focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood"
                                      onClick={() => setIsOpen(false)}
                                      type="button"
                                  >
                                      <span
                                          aria-hidden="true"
                                          className="font-display text-3xl leading-none"
                                      >
                                          ×
                                      </span>
                                  </button>
                              </div>

                              <ul className="mt-6 flex flex-col gap-1">
                                  {links.map((link) => {
                                      const active = isActivePath(
                                          currentPath,
                                          link.href,
                                      );

                                      return (
                                          <li key={link.href}>
                                              <Link
                                                  aria-current={
                                                      active
                                                          ? 'page'
                                                          : undefined
                                                  }
                                                  className="group flex min-h-16 items-baseline gap-4 border-b border-line py-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood"
                                                  data-mobile-menu-link
                                                  href={link.href}
                                                  onClick={() =>
                                                      setIsOpen(false)
                                                  }
                                                  prefetch={['mount', 'hover']}
                                              >
                                                  <span
                                                      className={`font-display text-[clamp(2.8rem,13vw,4.5rem)] font-black uppercase leading-none transition-colors ${
                                                          active
                                                              ? 'text-bone'
                                                              : 'text-bone-dim group-hover:text-bone'
                                                      }`}
                                                  >
                                                      {link.label}
                                                  </span>
                                                  {active ? (
                                                      <span className="ml-auto h-1.5 w-1.5 bg-blood" />
                                                  ) : null}
                                              </Link>
                                          </li>
                                      );
                                  })}
                              </ul>

                              <div
                                  className="mt-auto pt-8 font-mono text-[10px] font-bold uppercase leading-5 text-pewter-dim"
                                  data-mobile-menu-footer
                              >
                                  <p>Ελευθερούπολη · Καβάλα</p>
                                  <p>info@maxites-eleftheroupolis.gr</p>
                              </div>
                          </div>
                      </div>,
                      document.body,
                  )
                : null}
        </header>
    );
}
