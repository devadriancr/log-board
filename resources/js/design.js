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

    // Links live in a 12-column grid inside each card (like the cards): drag and resize them by cells, at least 4 wide so 3 fit per row.
    // Initialised after the card grid, which they are nested in. Cards of the page: one sub-grid each.
    const linkGrids = [...element.querySelectorAll('.link-grid')].map((el) =>
        GridStack.init(
            {
                column: 12,
                cellHeight: 34,
                margin: 3,
                float: false,
                animate: true,
                resizable: { handles: 'e, w' },
            },
            el,
        ),
    );

    // The order number is the place in the card: top to bottom, left to right.
    const renumber = (linkGrid) =>
        [...linkGrid.engine.nodes]
            .sort((a, b) => a.y - b.y || a.x - b.x)
            .forEach((node, index) => {
                node.el.querySelector('[data-order]').textContent = index + 1;
            });

    linkGrids.forEach((linkGrid) => {
        linkGrid.on('change', () => renumber(linkGrid));
        linkGrid.on('dragstop', () => renumber(linkGrid));
    });

    const toast =(text, variant) => window.Flux?.toast({ text, variant });

    document.getElementById('design-save')?.addEventListener('click', async () => {
        // Not grid.save(): it drops w/h when they equal 1, and the server requires both.
        const cards = grid.engine.nodes.map((node) => ({
            id: Number(node.id),
            x: node.x,
            y: node.y,
            w: node.w,
            h: node.h,
        }));

        const links = linkGrids.flatMap((linkGrid) =>
            linkGrid.engine.nodes.map((node) => ({ id: Number(node.id), x: node.x, y: node.y, w: node.w })),
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

            toast(element.dataset.saved, 'success');
        } catch (error) {
            toast(element.dataset.error, 'danger');
        }
    });
}

// Board tabs: drag to reorder. The home tab is fixed at the start, so a tab can never be dropped before it.
// The new order is saved right away (it is not part of the "Save" button of the cards).
function initTabs() {
    const nav = document.getElementById('board-tabs');

    if (!nav || nav.dataset.ready) {
        return;
    }

    nav.dataset.ready = '1';

    let dragged = null;

    nav.addEventListener('dragstart', (event) => {
        dragged = event.target.closest?.('a[data-board]:not([data-home])') ?? null;

        if (dragged) {
            event.dataTransfer.effectAllowed = 'move';
            dragged.classList.add('opacity-40');
        }
    });

    nav.addEventListener('dragover', (event) => {
        if (!dragged) {
            return;
        }

        event.preventDefault();

        const next = [...nav.querySelectorAll('a[data-board]:not([data-home])')]
            .filter((tab) => tab !== dragged)
            .find((tab) => {
                const box = tab.getBoundingClientRect();

                // Tabs can wrap into several lines: compare the row first, then the horizontal position.
                return event.clientY < box.top || (event.clientY <= box.bottom && event.clientX < box.left + box.width / 2);
            });

        nav.insertBefore(dragged, next ?? nav.querySelector('[data-add]')); // the + button always stays last
    });

    nav.addEventListener('dragend', async () => {
        if (!dragged) {
            return;
        }

        dragged.classList.remove('opacity-40');
        dragged = null;

        const ids = [...nav.querySelectorAll('a[data-board]:not([data-home])')].map((tab) => Number(tab.dataset.board));

        try {
            const response = await fetch(nav.dataset.url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': nav.dataset.token,
                },
                body: JSON.stringify({ ids }),
            });

            if (!response.ok) {
                throw new Error(response.statusText);
            }
        } catch (error) {
            window.Flux?.toast({ text: nav.dataset.error, variant: 'danger' });
        }
    });
}

init();
initTabs();
document.addEventListener('livewire:navigated', () => {
    init();
    initTabs();
});
