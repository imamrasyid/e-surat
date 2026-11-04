import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import { MOTION_OK, type MotionScope } from './shared';

/**
 * Statement: words fade in one by one while the section scrolls. Only opacity
 * changes, so light and dark themes behave the same.
 */
export function createStatement({ q, mm }: MotionScope) {
    const visual = q('[data-statement-visual]')[0];
    const section = visual?.closest('section');

    if (!visual || !section) {
        return;
    }

    mm.add(MOTION_OK, () => {
        let split: SplitText | null = null;
        let tween: gsap.core.Tween | null = null;
        let trigger: ScrollTrigger | null = null;
        let lastWidth = 0;
        let frame = 0;
        let disposed = false;

        const teardown = () => {
            trigger?.kill();
            trigger = null;
            tween?.kill();
            tween = null;
            split?.revert();
            split = null;
        };

        const build = () => {
            teardown();

            lastWidth = visual.clientWidth;
            split = SplitText.create(visual, { type: 'words', aria: 'hidden' });

            tween = gsap.fromTo(split.words, { autoAlpha: 0.25 }, { autoAlpha: 1, ease: 'none', stagger: 0.12 });

            trigger = ScrollTrigger.create({
                trigger: section,
                start: 'top 80%',
                end: 'bottom 45%',
                scrub: true,
                animation: tween,
            });
        };

        const onResize = () => {
            if (visual.clientWidth === lastWidth) {
                return;
            }

            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(() => {
                if (disposed) {
                    return;
                }

                build();
                ScrollTrigger.refresh();
            });
        };

        void document.fonts.ready.then(() => {
            if (disposed) {
                return;
            }

            build();
            window.addEventListener('resize', onResize);
        });

        return () => {
            disposed = true;
            cancelAnimationFrame(frame);
            window.removeEventListener('resize', onResize);
            teardown();
        };
    });
}
