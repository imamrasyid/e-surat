import { gsap } from 'gsap';
import { DrawSVGPlugin } from 'gsap/DrawSVGPlugin';
import { MorphSVGPlugin } from 'gsap/MorphSVGPlugin';
import { MotionPathPlugin } from 'gsap/MotionPathPlugin';

gsap.registerPlugin(DrawSVGPlugin, MorphSVGPlugin, MotionPathPlugin);

const ORBIT_DURATION = 18;
const NODE_PULSE_DURATION = 1.3;

/**
 * Welcome gate motion: entrance reveals plus the ambient agenda scene
 * (DrawSVG strokes, MorphSVG seal, MotionPath letter).
 * Every tween lives in one scoped context so unmount reverts it all.
 */
export function mountWelcomeGate(root: HTMLElement) {
    return gsap.context(() => {
        gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', () => {
            const intro = gsap.timeline({ defaults: { ease: 'power3.out' } });

            // The chrome links are Button-styled, so they carry `transition-all`. A CSS
            // transition on an animated property makes GSAP read a lagging computed opacity as
            // the tween's end value, which left links frozen at a partial opacity. `set` first
            // (with transitions off), then `to`, keeps the reveal deterministic; clearProps
            // hands every animated property back to CSS once it finishes.
            const chrome = gsap.utils.toArray<HTMLElement>('[data-load]');
            const reveal = gsap.utils.toArray<HTMLElement>('[data-gate-reveal]');

            gsap.set(chrome, { autoAlpha: 0, y: 12, transition: 'none' });
            gsap.set(reveal, { autoAlpha: 0, y: 18, transition: 'none' });

            intro
                .to(chrome, {
                    autoAlpha: 1,
                    y: 0,
                    duration: 0.6,
                    stagger: 0.07,
                    clearProps: 'opacity,visibility,transform,transition',
                })
                .to(
                    reveal,
                    {
                        autoAlpha: 1,
                        y: 0,
                        duration: 0.7,
                        stagger: 0.09,
                        clearProps: 'opacity,visibility,transform,transition',
                    },
                    0.12,
                )
                .fromTo(
                    '.wl-gate-accent',
                    { clipPath: 'inset(0% 0% 100% 0%)' },
                    { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.1, ease: 'power4.inOut', clearProps: 'clipPath' },
                    0.1,
                )
                .from('.wl-scene', { scale: 1.04, duration: 1.4, ease: 'power2.out' }, 0.1)
                .from('.wl-trail', { drawSVG: 0, duration: 1.5, ease: 'power2.inOut' }, 0.35)
                .from('.wl-ground', { drawSVG: 0, duration: 1, ease: 'power2.out' }, 0.5)
                .from('.wl-doc', { autoAlpha: 0, y: 24, duration: 0.85, ease: 'power3.out' }, 0.5)
                .from('.wl-doc-line', { drawSVG: 0, duration: 0.45, stagger: 0.08 }, 1.05)
                .from('.wl-doc-stamp', { autoAlpha: 0, y: 6, duration: 0.4 }, 1.3)
                .from('.wl-node', { autoAlpha: 0, scale: 0.4, transformOrigin: 'center', duration: 0.35, stagger: 0.1 }, 1.4)
                .from('.wl-plane', { autoAlpha: 0, scale: 0.4, duration: 0.5, ease: 'back.out(1.6)' }, 1.6)
                .from('.wl-seal', { autoAlpha: 0, scale: 0.4, transformOrigin: 'center', duration: 0.6, ease: 'back.out(1.7)' }, 1.65)
                .from('.wl-check', { drawSVG: 0, duration: 0.4, ease: 'power3.inOut' }, 2.15);

            const ambient = intro.duration();

            gsap.to('.wl-plane', {
                duration: ORBIT_DURATION,
                delay: ambient,
                ease: 'none',
                repeat: -1,
                motionPath: {
                    path: '#wl-agenda-orbit',
                    align: '#wl-agenda-orbit',
                    alignOrigin: [0.5, 0.5],
                    autoRotate: true,
                },
            });

            gsap.to('.wl-seal-layer', {
                duration: 3.6,
                delay: ambient,
                yoyo: true,
                repeat: -1,
                ease: 'sine.inOut',
                morphSVG: '#wl-seal-alt',
            });

            gsap.to('.wl-doc', {
                y: -8,
                duration: 4.5,
                delay: ambient,
                yoyo: true,
                repeat: -1,
                ease: 'sine.inOut',
            });

            root.querySelectorAll<SVGCircleElement>('.wl-node-pulse').forEach((pulse, index) => {
                gsap.fromTo(
                    pulse,
                    { scale: 1, autoAlpha: 0.85 },
                    {
                        scale: 3.4,
                        autoAlpha: 0,
                        duration: NODE_PULSE_DURATION,
                        delay: ambient + (ORBIT_DURATION / 3) * index,
                        repeat: -1,
                        repeatDelay: ORBIT_DURATION - NODE_PULSE_DURATION,
                        ease: 'power2.out',
                        transformOrigin: 'center',
                    },
                );
            });
        });
    }, root);
}
