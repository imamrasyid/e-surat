import { MOTION_OK, reveal, type MotionScope } from './shared';

/**
 * Generic scroll reveals for static content. The reduced-motion branch never
 * runs, so nothing here can leave content at `autoAlpha: 0`.
 */
export function createReveals({ q, mm }: MotionScope) {
    mm.add(MOTION_OK, () => {
        q('[data-section-head]').forEach((head) => reveal([head], { trigger: head, y: 22 }));

        q('[data-plate-panel]').forEach((panel) => reveal([panel], { trigger: panel, y: 26, duration: 0.9 }));

        const notes = q('[data-note]');
        reveal(notes, { trigger: notes[0], y: 20, stagger: 0.06 });

        const notifications = q('[data-notify]');
        reveal(notifications, { trigger: notifications[0], y: 22, stagger: 0.1 });

        const roles = q('[data-role-card]');
        reveal(roles, { trigger: roles[0], y: 20, stagger: 0.08 });

        const faqs = q('[data-faq]');
        reveal(faqs, { trigger: faqs[0], y: 18, stagger: 0.06 });

        const closing = q('[data-close-title], [data-close-body], [data-close-cta]');
        reveal(closing, { trigger: closing[0], y: 22, stagger: 0.08 });

        const footer = q('[data-footer]');
        reveal(footer, { trigger: footer[0], y: 16 });
    });
}
