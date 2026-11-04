import { gsap } from 'gsap';

export const MOTION_OK = '(prefers-reduced-motion: no-preference)';

export const REDUCED = '(prefers-reduced-motion: reduce)';

/**
 * The stack only pins when there is real vertical room: a short landscape
 * viewport would leave the cards permanently clipped.
 */
export const STACK_QUERY = `(min-width: 768px) and (min-height: 704px) and ${MOTION_OK}`;

export const MOBILE_QUERY = `${MOTION_OK} and (max-width: 767px)`;

/**
 * The running marquee needs room for two copies; below that the strip stays a
 * hand-scrollable row.
 */
export const MARQUEE_QUERY = `${MOTION_OK} and (min-width: 768px)`;

export const CLEAR = 'opacity,visibility,transform,transition';

export type MotionScope = {
    root: HTMLElement;
    q: gsap.utils.SelectorFunc;
    mm: gsap.MatchMedia;
};

type RevealOptions = {
    trigger?: Element;
    start?: string;
    y?: number;
    duration?: number;
    stagger?: number;
};

export function hide(targets: Element[], vars: { autoAlpha?: number; y?: number } = {}) {
    if (targets.length) {
        gsap.set(targets, { ...vars, transition: 'none' });
    }
}

/**
 * One-shot reveal. Every animated property is set up front with transitions
 * disabled and cleared afterwards, so a CSS transition can never be read back
 * as the tween's end state.
 */
export function reveal(targets: Element[], { trigger, start = 'top 88%', y = 24, duration = 0.8, stagger = 0.08 }: RevealOptions = {}) {
    if (!targets.length) {
        return;
    }

    hide(targets, { autoAlpha: 0, y });

    gsap.to(targets, {
        autoAlpha: 1,
        y: 0,
        duration,
        stagger,
        ease: 'power3.out',
        clearProps: CLEAR,
        scrollTrigger: {
            trigger: trigger ?? targets[0],
            start,
            once: true,
        },
    });
}
