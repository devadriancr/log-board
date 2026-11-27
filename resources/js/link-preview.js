// Thumbnails of linked pages: each one loads when it comes on screen, two at a time, so the board stays light.
const PAGE_WIDTH = 1280; // the page is drawn as on a desktop screen and then scaled to the width of the card
const MAX_LOADING = 2;
const TIMEOUT_MS = 20000;
// A page that loads again and again is stuck in a reload loop (usually it cannot read its session inside the frame)
const MAX_LOADS = 3;
const LOOP_WINDOW_MS = 10000;

const queue = [];
let loading = 0;

function next() {
    while (loading < MAX_LOADING && queue.length) {
        loading++;
        queue.shift()(() => {
            loading--;
            next();
        });
    }
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('linkPreview', () => {
        let box, frame, timer, resize, visible, change, job;
        let release = () => {};

        const fit = () => {
            const scale = box.clientWidth / PAGE_WIDTH;

            if (frame && scale) {
                frame.style.height = `${box.clientHeight / scale}px`;
                frame.style.transform = `scale(${scale})`;
            }
        };

        // Takes the page out and gives its turn back
        const drop = () => {
            clearTimeout(timer);
            release();
            frame?.remove();
            frame = null;
        };

        const enqueue = () => {
            if (! queue.includes(job)) {
                queue.push(job);
                next();
            }
        };

        return {
            state: 'loading',

            init() {
                box = this.$el;
                job = (done) => this.load(done);
                resize = new ResizeObserver(fit);
                resize.observe(box);

                visible = new IntersectionObserver((entries) => {
                    if (entries.some((entry) => entry.isIntersecting)) {
                        visible.disconnect();
                        enqueue();
                    }
                });
                visible.observe(box);

                // A link with options changes its address when another one is chosen. The frame is replaced
                // (changing its src would add a step to the browser history).
                change = new MutationObserver(() => {
                    if (frame && frame.getAttribute('src') !== box.dataset.url) {
                        drop();
                        this.state = 'loading';
                        enqueue();
                    }
                });
                change.observe(box, { attributeFilter: ['data-url'] });
            },

            load(done) {
                // A page that takes too long gives its turn to the next one; if it arrives later it is still shown.
                timer = setTimeout(() => {
                    this.state = 'failed';
                    release();
                }, TIMEOUT_MS);

                release = () => {
                    release = () => {};
                    done();
                };

                frame = document.createElement('iframe');
                frame.title = box.dataset.title;
                frame.tabIndex = -1;
                // no allow-top-navigation: the page inside cannot take the visitor away from the board
                frame.setAttribute('sandbox', 'allow-scripts allow-same-origin');
                const loads = [];
                frame.addEventListener('load', () => {
                    const now = Date.now();
                    loads.push(now);

                    // It is taken out so it stops hammering the server of that page
                    if (loads.filter((time) => now - time < LOOP_WINDOW_MS).length > MAX_LOADS) {
                        drop();
                        this.state = 'failed';

                        return;
                    }

                    clearTimeout(timer);
                    this.state = 'ready';
                    release();
                });
                frame.setAttribute('src', box.dataset.url);
                box.querySelector('[data-frame]').append(frame);
                fit();
            },

            destroy() {
                resize.disconnect();
                visible.disconnect();
                change.disconnect();

                if (queue.includes(job)) {
                    queue.splice(queue.indexOf(job), 1);
                }

                drop();
            },
        };
    });
});
