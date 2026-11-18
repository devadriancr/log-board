import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack.min.css';

function init() {
    const element = document.getElementById('board-design');

    if (!element || element.dataset.ready) {
        return;
    }

    element.dataset.ready = '1';

    const cellHeight = 110;
    element.style.setProperty('--cell-h', `${cellHeight}px`);

    const grid = GridStack.init(
        {
            column: 12,
            cellHeight,
            minRow: 4,
            margin: 8,
            float: false,
            animate: true,
            draggable: { handle: '.gs-handle' },
            resizable: { handles: 'e, se, s, sw, w' },
        },
        element,
    );

    // Show the size (columns × rows) of each card while it is being resized.
    const showSize = (node) => {
        const label = node.el?.querySelector('[data-size]');

        if (label) {
            label.textContent = `${node.w}×${node.h}`;
        }
    };

    grid.on('change', (event, nodes) => nodes?.forEach(showSize));
    grid.on('resizestop', (event, el) => showSize(el.gridstackNode));
    grid.on('dragstop', (event, el) => showSize(el.gridstackNode));

    const toast = (text, variant) => window.Flux?.toast({ text, variant });

    document.getElementById('design-save')?.addEventListener('click', async () => {
        const cards = grid.save(false).map((node) => ({
            id: Number(node.id),
            x: node.x,
            y: node.y,
            w: node.w,
            h: node.h,
        }));

        try {
            const response = await fetch(element.dataset.url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': element.dataset.token,
                },
                body: JSON.stringify({ cards }),
            });

            if (!response.ok) {
                throw new Error(response.statusText);
            }

            toast(element.dataset.saved, 'success');
        } catch (error) {
            toast(element.dataset.error, 'danger');
        }
    });
}

init();
document.addEventListener('livewire:navigated', init);
