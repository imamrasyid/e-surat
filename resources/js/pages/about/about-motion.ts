import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import { createDock } from './motion/dock';
import { createHero } from './motion/hero';
import { createMarquee } from './motion/marquee';
import { createReveals } from './motion/reveals';
import type { MotionScope } from './motion/shared';
import { createStack } from './motion/stack';
import { createStatement } from './motion/statement';

gsap.registerPlugin(ScrollTrigger, SplitText);

/**
 * About page motion orchestrator.
 *
 * Each module owns one behaviour and scopes every selector through the page
 * root, so nothing leaks to other Inertia pages. `revert()` is the only public
 * teardown: Index.vue keeps calling it on unmount.
 */
export function mountAboutMotion(root: HTMLElement) {
    const mm = gsap.matchMedia();

    const ctx = gsap.context(() => {
        const scope: MotionScope = { root, q: gsap.utils.selector(root), mm };

        createHero(scope);
        createDock(scope);
        createMarquee(scope);
        createStatement(scope);
        createStack(scope);
        createReveals(scope);
    }, root);

    let alive = true;

    // Web fonts change section heights; recompute trigger positions once they land.
    void document.fonts.ready.then(() => {
        if (alive) {
            ScrollTrigger.refresh();
        }
    });

    return {
        revert() {
            alive = false;
            mm.revert();
            ctx.revert();
        },
    };
}
