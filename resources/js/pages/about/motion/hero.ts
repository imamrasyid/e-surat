import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { CLEAR, MOTION_OK, hide, type MotionScope } from './shared';

type IntroVars = {
    y?: number;
    duration?: number;
    stagger?: number;
};

/**
 * Hero: one entry timeline plus the looping Open → Disposisi → Close badge.
 * The loop only advances while the hero card is on screen.
 */
export function createHero({ q, mm }: MotionScope) {
    mm.add(MOTION_OK, () => {
        const intro = gsap.timeline({ defaults: { ease: 'power3.out' } });

        const fade = (targets: Element[], position: number, { y = 18, duration = 0.7, stagger = 0.06 }: IntroVars = {}) => {
            if (!targets.length) {
                return;
            }

            hide(targets, { autoAlpha: 0, y });

            intro.to(targets, { autoAlpha: 1, y: 0, duration, stagger, clearProps: CLEAR }, position);
        };

        fade(q('[data-load]'), 0, { y: 10, duration: 0.55 });
        fade(q('[data-hero-line]'), 0.1, { y: 24, duration: 0.85, stagger: 0.08 });
        fade(q('[data-hero-lead]'), 0.3, { stagger: 0 });
        fade(q('[data-hero-cta]'), 0.4, { stagger: 0 });
        fade(q('[data-hero-facts]'), 0.5, { y: 16, stagger: 0 });
        fade(q('[data-hero-panel]'), 0.6, { y: 22, stagger: 0 });

        const card = q('[data-hero-card]')[0];
        const layers = q('[data-hero-status-layer]');

        if (!card || layers.length !== 3) {
            return;
        }

        gsap.set(layers.slice(1), { autoAlpha: 0, y: 6 });

        const loop = gsap.timeline({ repeat: -1, defaults: { ease: 'power2.inOut' }, repeatDelay: 1.4 });

        loop.to(layers[0], { autoAlpha: 0, y: -6, duration: 0.3 }, 1.6)
            .to(layers[1], { autoAlpha: 1, y: 0, duration: 0.3 }, '<')
            .to(layers[1], { autoAlpha: 0, y: -6, duration: 0.3 }, '+=1.4')
            .to(layers[2], { autoAlpha: 1, y: 0, duration: 0.3 }, '<');

        ScrollTrigger.create({
            trigger: card,
            start: 'top bottom',
            end: 'bottom top',
            onToggle: (self) => {
                if (self.isActive) {
                    loop.play();
                } else {
                    loop.pause();
                }
            },
        });
    });
}
