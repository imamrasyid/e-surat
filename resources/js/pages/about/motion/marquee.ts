import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { MARQUEE_QUERY, type MotionScope } from './shared';

/**
 * Marquee: the track holds two identical copies and travels exactly one copy
 * width, so the loop is seamless. The second copy only exists for the loop and
 * is revealed here, never in the static markup.
 */
export function createMarquee({ q, mm }: MotionScope) {
    mm.add(MARQUEE_QUERY, () => {
        const viewport = q('[data-marquee-viewport]')[0];
        const track = q('[data-marquee-track]')[0];
        const secondCopy = q('[data-marquee-copy="2"]')[0];

        if (!viewport || !track || !secondCopy) {
            return;
        }

        gsap.set(secondCopy, { display: 'flex' });
        gsap.set(viewport, { overflowX: 'hidden' });

        const tween = gsap.to(track, { xPercent: -50, duration: 30, ease: 'none', repeat: -1 });

        let hovered = false;
        let focused = false;
        let onScreen = true;

        const sync = () => {
            if (hovered || focused || !onScreen) {
                tween.pause();
            } else {
                tween.resume();
            }
        };

        const onEnter = () => {
            hovered = true;
            sync();
        };

        const onLeave = () => {
            hovered = false;
            sync();
        };

        const onFocusIn = () => {
            focused = true;
            sync();
        };

        const onFocusOut = () => {
            focused = false;
            sync();
        };

        viewport.addEventListener('mouseenter', onEnter);
        viewport.addEventListener('mouseleave', onLeave);
        viewport.addEventListener('focusin', onFocusIn);
        viewport.addEventListener('focusout', onFocusOut);

        const trigger = ScrollTrigger.create({
            trigger: viewport,
            start: 'top bottom',
            end: 'bottom top',
            onToggle: (self) => {
                onScreen = self.isActive;
                sync();
            },
        });

        return () => {
            viewport.removeEventListener('mouseenter', onEnter);
            viewport.removeEventListener('mouseleave', onLeave);
            viewport.removeEventListener('focusin', onFocusIn);
            viewport.removeEventListener('focusout', onFocusOut);
            trigger.kill();
        };
    });
}
