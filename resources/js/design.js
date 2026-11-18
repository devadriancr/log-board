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
            float: true, // cards stay where they are dropped, no gravity pulling them up
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

    // Row numbers (like Excel) and two empty rows below the last card so cards can be dropped further down.
    const rowsElement = document.getElementById('board-rows');

    const syncRows = () => {
        const bottom = Math.max(0, ...grid.engine.nodes.map((node) => node.y + node.h));
        const rows = Math.max(4, bottom + 2);

        grid.updateOptions({ minRow: rows });

        rowsElement.innerHTML = Array.from(
            { length: rows },
            (_, index) => `<div class="flex items-center justify-center" style="height:${cellHeight}px">${index + 1}</div>`,
        ).join('');
    };

    syncRows();
    grid.on('change', (event, nodes) => {
        nodes?.forEach(showSize);
        syncRows();
    });
    grid.on('resizestop', (event, el) => showSize(el.gridstackNode));
    grid.on('dragstop', (event, el) => {
        showSize(el.gridstackNode);
        syncRows();
    });

    // Reorder the links of a card by dragging them (native drag and drop, links stay in their card).
    // Reordered lists are renumbered 1..n and sent with the next save.
    const reordered = new Set();
    let dragged = null;

    element.addEventListener('dragstart', (event) => {
        dragged = event.target.closest?.('li[data-link]') ?? null;

        if (dragged) {
            event.dataTransfer.effectAllowed = 'move';
            dragged.classList.add('opacity-40');
        }
    });

    element.addEventListener('dragover', (event) => {
        const list = event.target.closest?.('[data-links]');

        if (!dragged || list !== dragged.parentElement) {
            return;
        }

        event.preventDefault();

        const next = [...list.querySelectorAll('li[data-link]')]
            .filter((item) => item !== dragged)
            .find((item) => event.clientY < item.getBoundingClientRect().top + item.offsetHeight / 2);

        list.insertBefore(dragged, next ?? null);
    });

    element.addEventListener('dragend', () => {
        if (!dragged) {
            return;
        }

        const list = dragged.parentElement;

        dragged.classList.remove('opacity-40');
        dragged = null;
        reordered.add(list);

        list.querySelectorAll('li[data-link]').forEach((item, index) => {
            item.querySelector('[data-order]').textContent = index + 1;
        });
    });

    const toast =(text, variant) => window.Flux?.toast({ text, variant });

    document.getElementById('design-save')?.addEventListener('click', async () => {
        const cards = grid.save(false).map((node) => ({
            id: Number(node.id),
            x: node.x,
            y: node.y,
            w: node.w,
            h: node.h,
        }));

        const links = [...reordered].flatMap((list) =>
            [...list.querySelectorAll('li[data-link]')].map((item, index) => ({
                id: Number(item.dataset.link),
                sort_order: index + 1,
            })),
        );

        try {
            const response = await fetch(element.dataset.url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': element.dataset.token,
                },
                body: JSON.stringify({ cards, links }),
            });

            if (!response.ok) {
                throw new Error(response.statusText);
            }

            reordered.clear();

            toast(element.dataset.saved, 'success');
        } catch (error) {
            toast(element.dataset.error, 'danger');
        }
    });
}

init();
document.addEventListener('livewire:navigated', init);
