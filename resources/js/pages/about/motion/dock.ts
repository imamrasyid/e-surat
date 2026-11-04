import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { MOTION_OK, REDUCED, type MotionScope } from './shared';

/**
 * Dock: hidden while the in-flow header is on screen, revealed once the header
 * bottom passes the viewport top, hidden again when scrolling back up.
 */
export function createDock({ q, mm }: MotionScope) {
    const dock = q('[data-site-dock]')[0];
    const header = q('[data-site-header]')[0];

    if (!dock || !header) {
        return;
    }

    const bind = (animated: boolean) => {
        gsap.set(dock, { autoAlpha: 0, ...(animated ? { y: -12, scale: 0.96 } : {}) });

        const show = (visible: boolean) => {
            if (!animated) {
                gsap.set(dock, { autoAlpha: visible ? 1 : 0 });

                return;
            }

            gsap.to(dock, {
                autoAlpha: visible ? 1 : 0,
                y: visible ? 0 : -12,
                scale: visible ? 1 : 0.96,
                duration: 0.28,
                ease: visible ? 'power2.out' : 'power2.in',
                overwrite: true,
            });
        };

        ScrollTrigger.create({
            trigger: header,
            start: 'bottom top',
            onEnter: () => show(true),
            onLeaveBack: () => show(false),
        });
    };

    mm.add(MOTION_OK, () => bind(true));
    mm.add(REDUCED, () => bind(false));
}
