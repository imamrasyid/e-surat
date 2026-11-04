import { gsap } from 'gsap';
import { MOBILE_QUERY, STACK_QUERY, reveal, type MotionScope } from './shared';

const STICKY_TOPS = [96, 116, 136, 156];

/**
 * Stack: cards are plain `position: sticky` surfaces, not ScrollTrigger pins.
 * ScrollTrigger only scrubs the recession (scale + scrim) of a card while the
 * next one docks, and builds each card's content in.
 */
export function createStack({ q, mm }: MotionScope) {
    const stack = q('[data-flow-stack]')[0];
    const cards = q('[data-flow-card]');

    if (!stack || !cards.length) {
        return;
    }

    mm.add(STACK_QUERY, () => {
        // Sticky items are clamped to the flex container's content box, so the
        // last card needs a real element — not padding — to travel into.
        const spacer = q('[data-flow-spacer]')[0];

        if (spacer) {
            gsap.set(spacer, { display: 'block', height: '34vh' });
        }

        cards.forEach((card, index) => {
            const surface = card.querySelector('[data-flow-surface]');
            const scrim = card.querySelector('[data-flow-scrim]');
            const content = card.querySelector('[data-flow-content]');
            const top = STICKY_TOPS[index] ?? STICKY_TOPS[STICKY_TOPS.length - 1];

            gsap.set(card, { position: 'sticky', top, zIndex: index + 1 });

            if (surface) {
                gsap.set(surface, { minHeight: '32rem', transformOrigin: '50% 0%' });
            }

            if (content) {
                gsap.fromTo(
                    content,
                    { y: 28, autoAlpha: 0 },
                    {
                        y: 0,
                        autoAlpha: 1,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: card,
                            start: 'top 92%',
                            end: `top ${top}px`,
                            scrub: true,
                        },
                    },
                );
            }

            const next = cards[index + 1];

            if (!next || !surface) {
                return;
            }

            const nextTop = STICKY_TOPS[index + 1] ?? top;

            gsap.fromTo(
                surface,
                { scale: 1 },
                {
                    scale: 0.94,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: next,
                        start: 'top bottom',
                        end: `top ${nextTop}px`,
                        scrub: true,
                    },
                },
            );

            if (scrim) {
                gsap.fromTo(
                    scrim,
                    { autoAlpha: 0 },
                    {
                        autoAlpha: 0.35,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: next,
                            start: 'top bottom',
                            end: `top ${nextTop}px`,
                            scrub: true,
                        },
                    },
                );
            }
        });
    });

    // Narrow viewports keep the one-column flow, with a single reveal per card.
    mm.add(MOBILE_QUERY, () => {
        cards.forEach((card) => {
            const content = card.querySelector('[data-flow-content]');

            if (content) {
                reveal([content], { trigger: card, y: 20 });
            }
        });
    });
}
