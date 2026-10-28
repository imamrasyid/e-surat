import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const MOTION_OK = '(prefers-reduced-motion: no-preference)';
const DESKTOP_HOW = `${MOTION_OK} and (min-width: 900px)`;
const PHONE_HOW = `${MOTION_OK} and (max-width: 899.98px)`;
const DESKTOP_PLATE = `${MOTION_OK} and (min-width: 1200px)`;
const PHONE_PLATE = `${MOTION_OK} and (max-width: 1199.98px)`;

type RevealVars = {
    trigger?: Element;
    start?: string;
    y?: number;
    duration?: number;
    stagger?: number;
};

/**
 * About page motion. One scoped context owns the hero timeline and every
 * scroll-driven timeline, so Inertia navigation reverts pins and tweens.
 *
 * Scroll reveals use immediateRender:false, so content stays visible until its
 * trigger fires — deep links and mid-page reloads can never leave it hidden.
 */
export function mountAboutMotion(root: HTMLElement) {
    return gsap.context(() => {
        const mm = gsap.matchMedia();
        const q = gsap.utils.selector(root);

        const reveal = (targets: Element[], { trigger, start = 'top 88%', y = 30, duration = 0.8, stagger = 0.06 }: RevealVars = {}) => {
            if (!targets.length) return;

            gsap.from(targets, {
                autoAlpha: 0,
                y,
                duration,
                stagger,
                ease: 'power3.out',
                immediateRender: false,
                scrollTrigger: {
                    trigger: trigger ?? targets[0],
                    start,
                    once: true,
                },
            });
        };

        mm.add(MOTION_OK, () => {
            // ---------- Hero: one labelled timeline ----------
            const intro = gsap.timeline({ defaults: { ease: 'power3.out' } });

            intro
                .addLabel('chrome')
                .from(q('.wl-top > *'), { y: -18, autoAlpha: 0, duration: 0.7, stagger: 0.07 }, 'chrome')
                .addLabel('board', 0.16)
                .fromTo(
                    q('.wl-tile-name'),
                    { clipPath: 'inset(0% 0% 100% 0%)' },
                    { clipPath: 'inset(0% 0% 0% 0%)', duration: 1, ease: 'power4.inOut' },
                    'board',
                )
                .from(q('.wl-name'), { yPercent: 108, duration: 1.1, ease: 'power4.out' }, 'board+=0.1')
                .from(q('.wl-lead'), { autoAlpha: 0, y: 18, duration: 0.8 }, 'board+=0.4')
                .fromTo(
                    q('.wl-tile-media'),
                    { clipPath: 'inset(0% 0% 100% 0%)' },
                    { clipPath: 'inset(0% 0% 0% 0%)', duration: 0.9, stagger: 0.12, ease: 'power4.inOut' },
                    'board+=0.18',
                )
                .fromTo(
                    q('.wl-shutter'),
                    { clipPath: 'inset(0% 0% 0% 100%)' },
                    { clipPath: 'inset(0% 0% 0% 0%)', duration: 0.9, ease: 'power4.inOut' },
                    'board+=0.42',
                )
                .from(q('.wl-inbox-row'), { autoAlpha: 0, y: 12, duration: 0.35, stagger: 0.06 }, 'board+=0.62')
                .from(q('.wl-tile-archive .wl-frag-wrap > *'), { autoAlpha: 0, y: 16, duration: 0.7 }, 'board+=0.45')
                .from(q('.wl-split .wl-tile'), { autoAlpha: 0, y: 26, duration: 0.8, stagger: 0.1 }, 'board+=0.35')
                .from(q('.wl-agenda-card'), { autoAlpha: 0, scale: 0.94, duration: 0.8, ease: 'power2.out' }, 'board+=0.6');

            // ---------- Showcase ----------
            const showcaseHead = q('.wl-showcase-head')[0];

            reveal(q('.wl-showcase-head .wl-h2'), { trigger: showcaseHead, y: 36, start: 'top 85%' });

            q('.wl-copy-row').forEach((row) => {
                reveal([row], { trigger: row, y: 44, start: 'top 88%' });
            });

            // ---------- How it works ----------
            const how = q('.wl-how')[0];

            reveal(q('.wl-how-title'), { trigger: how, y: 28, start: 'top 80%' });

            // ---------- FAQ ----------
            const faq = q('.wl-faq-grid')[0];

            reveal(q('.wl-faq-q, .wl-faq-a'), { trigger: faq, y: 26, stagger: 0.05, duration: 0.75 });

            // ---------- Closing ----------
            const closing = q('.wl-close')[0];
            const footer = q('.wl-footer')[0];

            reveal(q('.wl-close-title'), { trigger: closing, y: 44, duration: 1, start: 'top 72%' });
            reveal(q('.wl-close-bottom p, .wl-close-action'), { trigger: closing, y: 18, start: 'top 72%' });
            reveal(q('.wl-footer-name, .wl-footer-meta'), { trigger: footer, y: 24, stagger: 0.12, start: 'top 92%' });
        });

        // Pinned "four steps" chapter: the step copy lights up while the
        // section holds, then the closing note arrives.
        mm.add(DESKTOP_HOW, () => {
            const inner = q('.wl-how-inner')[0];

            if (inner.getBoundingClientRect().top <= window.innerHeight) {
                return;
            }

            gsap.set(q('.wl-step'), { opacity: 0.22 });

            const tl = gsap.timeline({
                scrollTrigger: {
                    trigger: inner,
                    start: 'top 96px',
                    end: '+=1000',
                    pin: true,
                    scrub: 0.7,
                    anticipatePin: 1,
                },
            });

            tl.to(q('.wl-step'), { opacity: 1, duration: 1, stagger: 0.9, ease: 'none' }).from(
                q('.wl-how-note')[0],
                { autoAlpha: 0, y: 16, duration: 0.9, ease: 'power2.out' },
                '>-0.1',
            );
        });

        mm.add(PHONE_HOW, () => {
            reveal(q('.wl-step'), { trigger: q('.wl-steps')[0], y: 18, stagger: 0.08, duration: 0.7, start: 'top 85%' });
            reveal(q('.wl-how-note'), { trigger: q('.wl-how-note')[0], y: 16, start: 'top 92%' });
        });

        // Pinned annotation plate: the letter holds while each note arrives.
        mm.add(DESKTOP_PLATE, () => {
            const plate = q('.wl-plate')[0];
            const notes = q('.wl-note');

            if (plate.getBoundingClientRect().top <= window.innerHeight) {
                return;
            }

            gsap.set(notes, { autoAlpha: 0, y: 22 });

            gsap.timeline({
                scrollTrigger: {
                    trigger: plate,
                    start: 'top 80px',
                    end: '+=1100',
                    pin: true,
                    scrub: 0.6,
                    anticipatePin: 1,
                },
            }).to(notes, { autoAlpha: 1, y: 0, duration: 0.8, stagger: 0.7, ease: 'power2.out' });
        });

        mm.add(PHONE_PLATE, () => {
            reveal(q('.wl-plate-panel'), { trigger: q('.wl-plate-panel')[0], y: 30, duration: 0.9, start: 'top 88%' });
            reveal(q('.wl-note-mobile'), { trigger: q('.wl-notes-mobile')[0], y: 24, stagger: 0.08, duration: 0.7, start: 'top 88%' });
        });

        // Web fonts change section heights; recompute pin distances after they land.
        let alive = true;
        void document.fonts?.ready.then(() => {
            if (alive) {
                ScrollTrigger.refresh();
            }
        });

        return () => {
            alive = false;
        };
    }, root);
}
