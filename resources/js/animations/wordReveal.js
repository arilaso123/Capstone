/**
 * Uyuy Jastip — Word-by-Word Text Reveal Animation
 * 
 * Powered by GSAP Core & ScrollTrigger.
 * Built with native DOM APIs (zero innerHTML injection) for maximum
 * semantic safety, accessibility, and responsive typography preservation.
 */

import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Register ScrollTrigger once on module load
gsap.registerPlugin(ScrollTrigger);

/**
 * Baseline animation configuration matching specification.
 */
const DEFAULT_CONFIG = {
    duration: 0.65,
    stagger: 0.12,
    ease: 'power3.out',
    y: 26,
    start: 'top 85%',
    toggleActions: 'play none none reverse'
};

/**
 * WeakMap registry tracking initialized headings, their GSAP contexts,
 * and original DOM nodes for safe teardown and idempotent execution.
 * @type {WeakMap<HTMLElement, { timeline: gsap.core.Timeline, trigger: ScrollTrigger, originalNodes: Node[] }>}
 */
const headingRegistry = new WeakMap();

/**
 * Check if the user prefers reduced motion.
 * @returns {boolean}
 */
const prefersReducedMotion = () => {
    return typeof window !== 'undefined' &&
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

/**
 * Safely parse numeric data attributes with boundary validation.
 * @param {string | undefined} val 
 * @param {number} min 
 * @param {number} max 
 * @param {number} fallback 
 * @returns {number}
 */
const parseNumericAttr = (val, min, max, fallback) => {
    if (!val) return fallback;
    const parsed = Number.parseFloat(val);
    if (!Number.isFinite(parsed) || parsed < min || parsed > max) {
        return fallback;
    }
    return parsed;
};

/**
 * Extract validated configuration for a specific heading element.
 * @param {HTMLElement} element 
 * @returns {typeof DEFAULT_CONFIG}
 */
const resolveConfig = (element) => {
    const duration = parseNumericAttr(element.dataset.animateDuration, 0.1, 3.0, DEFAULT_CONFIG.duration);
    const stagger = parseNumericAttr(element.dataset.animateStagger, 0.02, 0.6, DEFAULT_CONFIG.stagger);
    const y = parseNumericAttr(element.dataset.animateY, 5, 80, DEFAULT_CONFIG.y);
    const start = element.dataset.animateStart || DEFAULT_CONFIG.start;
    const toggleActions = element.dataset.animateToggleActions || DEFAULT_CONFIG.toggleActions;

    return {
        duration,
        stagger,
        ease: DEFAULT_CONFIG.ease,
        y,
        start,
        toggleActions
    };
};

/**
 * Safely transform heading text into word-wrapped spans using native DOM APIs.
 * Preserves accessibility, natural text wrapping, and avoids string concatenation.
 * 
 * @param {HTMLElement} heading 
 * @returns {{ wordElements: HTMLSpanElement[], originalNodes: Node[] } | null}
 */
const splitHeadingSafely = (heading) => {
    // 1. Guard against nested HTML element children (per Section 8.3)
    const hasElementChildren = Array.from(heading.childNodes).some(
        (node) => node.nodeType === Node.ELEMENT_NODE
    );

    if (hasElementChildren) {
        console.warn('[wordReveal] Skipped heading with nested HTML elements for DOM safety:', heading);
        return null;
    }

    // 2. Extract and sanitize text content
    const rawText = heading.textContent || '';
    const normalizedText = rawText.trim().replace(/\s+/g, ' ');
    if (!normalizedText) {
        return null;
    }

    // 3. Save clone of original child nodes for safe rollback
    const originalNodes = Array.from(heading.childNodes).map((node) => node.cloneNode(true));

    // 4. Tokenize into individual words
    const words = normalizedText.split(' ').filter(Boolean);
    if (words.length === 0) {
        return null;
    }

    // 5. Construct screen reader accessible label (if not already labeled)
    const hasAccessibleName = heading.hasAttribute('aria-label') || heading.hasAttribute('aria-labelledby');
    let srElement = null;
    if (!hasAccessibleName) {
        srElement = document.createElement('span');
        srElement.className = 'visually-hidden word-reveal__sr';
        srElement.textContent = normalizedText;
    }

    // 6. Construct visual container with aria-hidden="true"
    const visualContainer = document.createElement('span');
    visualContainer.className = 'word-reveal__visual';
    visualContainer.setAttribute('aria-hidden', 'true');

    const wordElements = [];

    words.forEach((wordText, index) => {
        if (index > 0) {
            // Native whitespace text node ensures natural word wrapping
            visualContainer.appendChild(document.createTextNode(' '));
        }

        const wordSpan = document.createElement('span');
        wordSpan.className = 'word-reveal__word';
        wordSpan.textContent = wordText;

        visualContainer.appendChild(wordSpan);
        wordElements.push(wordSpan);
    });

    // 7. Atomic DOM swap
    heading.replaceChildren();
    if (srElement) {
        heading.appendChild(srElement);
    }
    heading.appendChild(visualContainer);

    return { wordElements, originalNodes };
};

/**
 * Animate a single heading element with GSAP Timeline and ScrollTrigger.
 * @param {HTMLElement} heading 
 */
const initHeading = (heading) => {
    if (!(heading instanceof HTMLElement)) {
        return;
    }

    // Idempotent guard: never initialize the same element twice
    if (headingRegistry.has(heading)) {
        return;
    }

    // If user prefers reduced motion, leave heading in default natural visible state
    if (prefersReducedMotion()) {
        return;
    }

    const config = resolveConfig(heading);
    const splitResult = splitHeadingSafely(heading);
    if (!splitResult) {
        return;
    }

    const { wordElements, originalNodes } = splitResult;

    // Fail-safe: Set initial state with GSAP (never global CSS opacity: 0)
    gsap.set(wordElements, {
        opacity: 0,
        y: config.y
    });

    // Create trigger-based timeline
    const timeline = gsap.timeline({
        scrollTrigger: {
            trigger: heading,
            start: config.start,
            toggleActions: config.toggleActions,
            fastScrollEnd: true,
            invalidateOnRefresh: true
        }
    });

    timeline.to(wordElements, {
        opacity: 1,
        y: 0,
        duration: config.duration,
        stagger: config.stagger,
        ease: config.ease,
        clearProps: 'willChange'
    });

    // Record in WeakMap registry for safe lifecycle management
    headingRegistry.set(heading, {
        timeline,
        trigger: timeline.scrollTrigger,
        originalNodes
    });
};

/**
 * Initialize all target headings within the given root element.
 * Targets only elements matching [data-animate="words"].
 * 
 * @param {Document | HTMLElement} root 
 */
export const initWordReveal = (root = document) => {
    try {
        const targets = root.querySelectorAll('[data-animate="words"]');
        targets.forEach((heading) => {
            if (heading instanceof HTMLElement) {
                initHeading(heading);
            }
        });
    } catch (error) {
        console.error('[wordReveal] Error during animation initialization:', error);
    }
};

/**
 * Safely clean up and teardown animations on target elements or entire container.
 * Kills only the timelines and ScrollTriggers managed by this module,
 * and restores original DOM nodes.
 * 
 * @param {Document | HTMLElement} root 
 */
export const cleanupWordReveal = (root = document) => {
    try {
        const targets = root.querySelectorAll('[data-animate="words"]');
        targets.forEach((heading) => {
            if (heading instanceof HTMLElement && headingRegistry.has(heading)) {
                const entry = headingRegistry.get(heading);
                if (entry) {
                    if (entry.timeline) entry.timeline.kill();
                    if (entry.trigger) entry.trigger.kill();
                    if (entry.originalNodes && entry.originalNodes.length > 0) {
                        heading.replaceChildren(...entry.originalNodes);
                    }
                }
                headingRegistry.delete(heading);
            }
        });
    } catch (error) {
        console.error('[wordReveal] Error during animation cleanup:', error);
    }
};

// Listen for OS reduced-motion preference changes
if (typeof window !== 'undefined' && window.matchMedia) {
    const motionMediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const handleMotionChange = (e) => {
        if (e.matches) {
            cleanupWordReveal(document);
        } else {
            initWordReveal(document);
        }
    };

    if (typeof motionMediaQuery.addEventListener === 'function') {
        motionMediaQuery.addEventListener('change', handleMotionChange);
    } else if (typeof motionMediaQuery.addListener === 'function') {
        motionMediaQuery.addListener(handleMotionChange);
    }
}
