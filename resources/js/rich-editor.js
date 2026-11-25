import { Editor, Extension, Mark, Node, mergeAttributes } from '@tiptap/core';
import { Plugin } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from '@tiptap/pm/view';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import { FontSize, TextStyle } from '@tiptap/extension-text-style';

// Sizes offered in the toolbar, in px like the text of the page (the base size of a text card is 14).
const FONT_SIZES = [10, 11, 12, 14, 16, 18, 20, 24, 28, 32, 36, 48, 64];

// Fixed image widths (px) offered in the toolbar: the same preset gives the same size in every notice and card.
const IMAGE_SIZES = { image_small: ['P', 80], image_medium: ['M', 140], image_large: ['G', 220] };

// Fills a grid with a button per icon of the system (their SVGs come from the icon picker of the card form).
const fillIconGrid = (grid) => document.querySelectorAll('[data-icon-library] [data-icon]').forEach((source) => {
    const item = Object.assign(document.createElement('button'), { type: 'button', title: source.dataset.icon, innerHTML: source.innerHTML });

    item.dataset.icon = source.dataset.icon;
    item.className = 'flex size-9 items-center justify-center rounded-md text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600 [&>svg]:size-5';
    grid.append(item);
});

const filterIconGrid = (grid, text) => grid.childNodes.forEach((item) => {
    item.hidden = !item.dataset.icon.includes(text.trim().toLowerCase());
});

// A quote can be a callout made with the callout panel: class "callout", its own colour (one of the card palette), icon
// (and icon colour), title and size. Everything is saved on the <blockquote>; the colours are styled by app.css.
const DEFAULT_COLOR = 'blue'; // a key of Card::COLORS
const keyOf = (value) => (/^[a-z]+$/.test(value ?? '') ? value : null);

const CalloutQuote = Extension.create({
    name: 'calloutQuote',
    addGlobalAttributes() {
        return [{
            types: ['blockquote'],
            attributes: {
                class: {
                    default: null,
                    parseHTML: (element) => (element.classList.contains('callout') ? 'callout' : null),
                    renderHTML: (attributes) => (attributes.class ? { class: attributes.class } : {}),
                },
                calloutColor: {
                    default: null,
                    parseHTML: (element) => keyOf(element.getAttribute('data-callout-color')),
                    renderHTML: (attributes) => (attributes.calloutColor ? { 'data-callout-color': attributes.calloutColor } : {}),
                },
                calloutIconColor: {
                    default: null,
                    parseHTML: (element) => keyOf(element.getAttribute('data-callout-icon-color')),
                    renderHTML: (attributes) => (attributes.calloutIconColor ? { 'data-callout-icon-color': attributes.calloutIconColor } : {}),
                },
                calloutIcon: {
                    default: null,
                    parseHTML: (element) => element.getAttribute('data-callout-icon'),
                    renderHTML: (attributes) => (attributes.calloutIcon ? { 'data-callout-icon': attributes.calloutIcon } : {}),
                },
                calloutTitle: {
                    default: null,
                    parseHTML: (element) => element.getAttribute('data-callout-title'),
                    renderHTML: (attributes) => (attributes.calloutTitle ? { 'data-callout-title': attributes.calloutTitle } : {}),
                },
                // Size of the whole callout (text, title and icon): saved as font-size on the quote.
                fontSize: {
                    default: null,
                    parseHTML: (element) => element.style.fontSize || null,
                    renderHTML: (attributes) => (attributes.fontSize ? { style: `font-size:${attributes.fontSize}` } : {}),
                },
            },
        }];
    },
    // The icon of a callout is drawn inside the editor as a widget at the start of the quote (the server adds it when it shows the card).
    addProseMirrorPlugins() {
        return [new Plugin({
            props: {
                decorations: ({ doc }) => {
                    const widgets = [];

                    doc.descendants((node, pos) => {
                        if (node.type.name === 'blockquote' && node.attrs.calloutIcon) {
                            widgets.push(Decoration.widget(pos + 1, () => {
                                const icon = Object.assign(document.createElement('span'), { className: 'callout-icon', contentEditable: 'false', innerHTML: iconSvg(node.attrs.calloutIcon) });

                                if (node.attrs.calloutIconColor) {
                                    icon.dataset.calloutColor = node.attrs.calloutIconColor;
                                }

                                return icon;
                            }, { side: -1, ignoreSelection: true }));
                        }
                    });

                    return DecorationSet.create(doc, widgets);
                },
            },
        })];
    },
});

// Coloured text: a colour of the card palette saved as <span data-text-color="teal">; the colour itself comes from app.css.
const TextColor = Mark.create({
    name: 'textColor',
    priority: 101, // before TextStyle, which also reads <span>
    addAttributes() {
        return { color: { default: null, parseHTML: (element) => keyOf(element.getAttribute('data-text-color')), renderHTML: (attributes) => ({ 'data-text-color': attributes.color }) } };
    },
    parseHTML() {
        return [{ tag: 'span[data-text-color]' }];
    },
    renderHTML: ({ HTMLAttributes }) => ['span', HTMLAttributes, 0],
});

// The SVGs of the icons come from the icon picker of the card form (it renders every icon of the system).
const iconSvg = (name) => document.querySelector(`[data-icon-library] [data-icon="${name}"]`)?.innerHTML ?? '';

// An icon inside the text: saved as <span data-icon="name"></span>, the server fills in the SVG when it shows the card.
const InlineIcon = Node.create({
    name: 'icon',
    group: 'inline',
    inline: true,
    atom: true,
    priority: 1001, // before TextStyle, which also reads <span>
    addAttributes() {
        return { name: { default: null, parseHTML: (element) => element.getAttribute('data-icon') } };
    },
    parseHTML() {
        return [{ tag: 'span[data-icon]' }];
    },
    renderHTML: ({ node, HTMLAttributes }) => ['span', mergeAttributes(HTMLAttributes, { 'data-icon': node.attrs.name })],
    addNodeView() {
        return ({ node }) => {
            const dom = Object.assign(document.createElement('span'), { contentEditable: 'false', innerHTML: iconSvg(node.attrs.name) });

            dom.dataset.icon = node.attrs.name;

            return { dom };
        };
    },
});

// Images carry their position as a class (img-left / img-center / img-right): the server only lets those through.
const PositionedImage = Image.extend({
    // Drag the corners of the selected image to resize it (width/height are saved in the HTML).
    addOptions() {
        return {
            ...this.parent?.(),
            resize: { enabled: true, directions: ['top-left', 'top-right', 'bottom-left', 'bottom-right'], minWidth: 40, minHeight: 40, alwaysPreserveAspectRatio: true },
        };
    },
    addAttributes() {
        return {
            ...this.parent?.(),
            class: {
                default: 'img-center',
                parseHTML: (element) => element.getAttribute('class')?.match(/img-(left|center|right)/)?.[0] ?? 'img-center',
                renderHTML: (attributes) => ({ class: attributes.class }),
            },
        };
    },
});

// Builds the editor of one notice (slide): its toolbar, its editor and its hidden field.
function initSlide(slide, root, labels) {
    const field = slide.querySelector('textarea');
    const toast = (text, variant) => window.Flux?.toast({ text, variant });

    const editor = new Editor({
        element: slide.querySelector('[data-editor]'),
        content: field.value,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
                link: { openOnClick: false, HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' } },
            }),
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            TextStyle,
            FontSize,
            PositionedImage,
            CalloutQuote,
            InlineIcon,
            TextColor,
        ],
        editorProps: {
            attributes: { class: 'rich-content min-h-48 px-3 py-2 outline-none' },
        },
        onUpdate: ({ editor }) => {
            field.value = editor.isEmpty ? '' : editor.getHTML();
        },
    });

    slide.editor = editor;

    // While an image is being resized, a small label under it shows its size in px.
    // Inline styles on purpose: this label does not depend on the CSS of the page.
    const sizeTag = Object.assign(document.createElement('div'), { hidden: true });

    sizeTag.style.cssText = 'position:fixed;z-index:9999;transform:translateX(-50%);pointer-events:none;padding:2px 8px;border-radius:6px;background:#27272a;color:#fff;font:12px/1.5 ui-monospace,monospace;box-shadow:0 2px 6px rgb(0 0 0/.3)';

    document.body.append(sizeTag);

    const watchResize = new MutationObserver(() => {
        const image = editor.view.dom.querySelector('[data-resize-container][data-resize-state="true"] img');

        if (!image) {
            sizeTag.hidden = true;

            return;
        }

        const box = image.getBoundingClientRect();

        sizeTag.textContent = `${Math.round(box.width)} × ${Math.round(box.height)} px`;
        sizeTag.style.left = `${box.left + box.width / 2}px`;
        sizeTag.style.top = `${box.bottom + 8}px`;
        sizeTag.hidden = false;
    });

    watchResize.observe(editor.view.dom, { subtree: true, attributes: true, attributeFilter: ['style', 'data-resize-state'] });

    editor.on('destroy', () => {
        watchResize.disconnect();
        sizeTag.remove();
    });

    const upload = async (file) => {
        const body = new FormData();
        body.append('image', file);

        try {
            const response = await fetch(root.dataset.upload, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': root.dataset.token },
                body,
            });

            if (!response.ok) {
                throw new Error(response.statusText);
            }

            const { url } = await response.json();
            // A new image starts small, on the left, with the text wrapping around it.
            editor.chain().focus().setImage({ src: url, alt: file.name, width: IMAGE_SIZES.image_medium[1], class: 'img-left' }).run();
        } catch (error) {
            toast(labels.upload_error, 'danger');
        }
    };

    const picker = Object.assign(document.createElement('input'), { type: 'file', accept: 'image/png,image/jpeg,image/gif,image/webp' });

    picker.addEventListener('change', () => {
        if (picker.files[0]) {
            upload(picker.files[0]);
        }

        picker.value = '';
    });

    const imageClass = (name) => () => editor.chain().focus().updateAttributes('image', { class: name }).run();

    // The resize wrapper sets the width inline when it creates the image and does not follow the attribute afterwards,
    // so besides saving it the width is also set on the image shown in the editor.
    const imageWidth = (width) => () => {
        editor.chain().focus().updateAttributes('image', { width, height: null }).run();

        const img = editor.view.nodeDOM(editor.state.selection.from)?.querySelector?.('img');

        img?.style.setProperty('width', `${width}px`);
        img?.style.setProperty('height', 'auto');
    };

    const askLink = () => {
        const url = window.prompt(labels.link_prompt, editor.getAttributes('link').href ?? 'https://');

        if (url === null) {
            return;
        }

        if (url === '') {
            editor.chain().focus().unsetLink().run();
        } else {
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        }
    };

    // Icon picker: a small panel under the toolbar with a search box and the icons of the card form.
    const iconPanel = document.createElement('div');

    iconPanel.hidden = true;
    iconPanel.className = 'absolute start-2 top-full z-20 mt-1 flex w-80 max-w-[calc(100%-1rem)] flex-col gap-2 rounded-lg border border-zinc-200 bg-white p-2 shadow-md dark:border-zinc-600 dark:bg-zinc-700';
    iconPanel.innerHTML = `<input type="search" placeholder="${labels.icon_search}" class="w-full rounded-md border border-zinc-200 bg-white px-2 py-1 text-sm dark:border-zinc-500 dark:bg-zinc-800 dark:text-zinc-200"><div class="grid max-h-56 grid-cols-7 gap-1 overflow-y-auto"></div>`;

    const iconGrid = iconPanel.lastElementChild;
    const iconSearch = iconPanel.firstElementChild;

    fillIconGrid(iconGrid);
    iconSearch.addEventListener('input', () => filterIconGrid(iconGrid, iconSearch.value));

    iconGrid.addEventListener('click', (event) => {
        const name = event.target.closest('[data-icon]')?.dataset.icon;

        if (name) {
            iconPanel.hidden = true;
            editor.chain().focus().insertContent({ type: 'icon', attrs: { name } }).run();
        }
    });

    slide.classList.add('relative');
    slide.append(iconPanel);
    document.addEventListener('click', (event) => {
        if (!iconPanel.hidden && !iconPanel.contains(event.target) && !event.target.closest('[data-icon-button]')) {
            iconPanel.hidden = true;
        }
    });

    const toggleIcons = () => {
        iconPanel.hidden = !iconPanel.hidden;

        if (!iconPanel.hidden) {
            iconSearch.focus();
        }
    };

    // Text colour: a panel with the colours of the card palette (the same as the callouts).
    const colors = JSON.parse(root.dataset.colors);
    const colorPanel = document.createElement('div');

    colorPanel.hidden = true;
    colorPanel.className = iconPanel.className;
    colorPanel.innerHTML = `<div class="flex flex-wrap items-center gap-1.5"><button type="button" data-key="" class="h-6 rounded-md border border-zinc-300 px-2 text-xs text-zinc-700 dark:border-zinc-500 dark:text-zinc-200">${labels.text_color_none}</button>${Object.entries(colors).map(([key, name]) => `<button type="button" data-key="${key}" data-card-color="${key}" title="${name}" aria-label="${name}" class="card-swatch size-6 rounded-md"></button>`).join('')}</div>`;
    colorPanel.addEventListener('click', (event) => {
        const key = event.target.closest('[data-key]')?.dataset.key;

        if (key === undefined) {
            return;
        }

        colorPanel.hidden = true;
        (key ? editor.chain().focus().setMark('textColor', { color: key }) : editor.chain().focus().unsetMark('textColor')).run();
    });
    slide.append(colorPanel);
    document.addEventListener('click', (event) => {
        if (!colorPanel.hidden && !colorPanel.contains(event.target) && !event.target.closest('[data-text-color-button]')) {
            colorPanel.hidden = true;
        }
    });

    // [text, label key, command, is active?, only enabled while an image is selected]
    const buttons = [
        ['B', 'bold', () => editor.chain().focus().toggleBold().run(), () => editor.isActive('bold')],
        ['I', 'italic', () => editor.chain().focus().toggleItalic().run(), () => editor.isActive('italic')],
        ['U', 'underline', () => editor.chain().focus().toggleUnderline().run(), () => editor.isActive('underline')],
        ['A', 'text_color', () => { colorPanel.hidden = !colorPanel.hidden; }, () => editor.isActive('textColor')],
        ['S', 'strike', () => editor.chain().focus().toggleStrike().run(), () => editor.isActive('strike')],
        ['</>', 'code', () => editor.chain().focus().toggleCode().run(), () => editor.isActive('code')],
        ['{ }', 'code_block', () => editor.chain().focus().toggleCodeBlock().run(), () => editor.isActive('codeBlock')],
        null,
        ['H2', 'h2', () => editor.chain().focus().toggleHeading({ level: 2 }).run(), () => editor.isActive('heading', { level: 2 })],
        ['H3', 'h3', () => editor.chain().focus().toggleHeading({ level: 3 }).run(), () => editor.isActive('heading', { level: 3 })],
        ['•', 'bullet_list', () => editor.chain().focus().toggleBulletList().run(), () => editor.isActive('bulletList')],
        ['1.', 'ordered_list', () => editor.chain().focus().toggleOrderedList().run(), () => editor.isActive('orderedList')],
        ['❝', 'quote', () => editor.chain().focus().toggleBlockquote().run(), () => editor.isActive('blockquote')],
        ['―', 'rule', () => editor.chain().focus().setHorizontalRule().run(), () => false],
        null,
        ['⇤', 'align_left', () => editor.chain().focus().setTextAlign('left').run(), () => editor.isActive({ textAlign: 'left' })],
        ['↔', 'align_center', () => editor.chain().focus().setTextAlign('center').run(), () => editor.isActive({ textAlign: 'center' })],
        ['⇥', 'align_right', () => editor.chain().focus().setTextAlign('right').run(), () => editor.isActive({ textAlign: 'right' })],
        null,
        ['🔗', 'link', askLink, () => editor.isActive('link')],
        ['★', 'icon', toggleIcons, () => false],
        ['🖼', 'image', () => picker.click(), () => false],
        ['◧', 'image_left', imageClass('img-left'), () => editor.isActive('image', { class: 'img-left' }), true],
        ['▣', 'image_center', imageClass('img-center'), () => editor.isActive('image', { class: 'img-center' }), true],
        ['◨', 'image_right', imageClass('img-right'), () => editor.isActive('image', { class: 'img-right' }), true],
        ...Object.entries(IMAGE_SIZES).map(([key, [text, width]]) => [text, key, imageWidth(width), () => Number(editor.getAttributes('image').width) === width, true]),
        null,
        ['↶', 'undo', () => editor.chain().focus().undo().run(), () => false],
        ['↷', 'redo', () => editor.chain().focus().redo().run(), () => false],
    ];

    const toolbar = slide.querySelector('[data-toolbar]');
    const refreshers = [];
    let quoteButton;

    buttons.forEach((definition) => {
        if (!definition) {
            toolbar.insertAdjacentHTML('beforeend', '<span class="mx-1 h-5 w-px bg-zinc-300 dark:bg-zinc-600"></span>');

            return;
        }

        const [text, key, run, active, imageOnly] = definition;

        const button = document.createElement('button');

        button.type = 'button';
        button.textContent = text;
        button.title = labels[key];
        button.setAttribute('aria-label', labels[key]);
        button.className = 'min-w-8 rounded-md px-2 py-1 text-sm text-zinc-700 hover:bg-zinc-100 disabled:opacity-30 dark:text-zinc-200 dark:hover:bg-zinc-600';
        button.addEventListener('click', run);
        button.toggleAttribute('data-icon-button', key === 'icon');
        button.toggleAttribute('data-text-color-button', key === 'text_color');

        if (key === 'quote') {
            quoteButton = button;
        }
        toolbar.append(button);

        refreshers.push(() => {
            button.classList.toggle('bg-zinc-200', active());
            button.classList.toggle('dark:bg-zinc-500', active());

            if (imageOnly) {
                button.disabled = !editor.isActive('image');
            }
        });
    });

    // Font size, like the box in Word: 14 is the normal size of the text, so choosing it removes the size.
    const size = document.createElement('select');

    size.title = labels.font_size;
    size.setAttribute('aria-label', labels.font_size);
    size.className = 'mr-1 rounded-md border border-zinc-200 bg-white px-1 py-1 text-sm text-zinc-700 dark:border-zinc-500 dark:bg-zinc-700 dark:text-zinc-200';
    size.innerHTML = FONT_SIZES.map((n) => `<option value="${n}">${n}</option>`).join('');
    size.addEventListener('change', () => {
        const chain = editor.chain().focus();

        (Number(size.value) === 14 ? chain.unsetFontSize() : chain.setFontSize(`${size.value}px`)).run();
    });
    toolbar.prepend(size);
    refreshers.push(() => {
        size.value = parseInt(editor.getAttributes('textStyle').fontSize, 10) || 14;
    });

    // Callout panel: a quote with the colour, icon, title and size chosen here (they are saved on the quote itself).
    const inputClass = 'w-full rounded-md border border-zinc-200 bg-white px-2 py-1 text-sm dark:border-zinc-500 dark:bg-zinc-800 dark:text-zinc-200';
    const swatches = (kind) => Object.entries(colors).map(([key, name]) => `<button type="button" data-pick="${kind}" data-key="${key}" data-card-color="${key}" title="${name}" aria-label="${name}" class="card-swatch size-6 rounded-md"></button>`).join('');
    const panel = document.createElement('div');

    panel.hidden = true;
    panel.className = iconPanel.className;
    panel.innerHTML = `
        <input data-title type="text" maxlength="40" placeholder="${labels.callout_title}" class="${inputClass}">
        <div class="text-xs text-zinc-500 dark:text-zinc-300">${labels.callout_color}<div class="mt-1 flex flex-wrap gap-1.5">${swatches('color')}</div></div>
        <div class="text-xs text-zinc-500 dark:text-zinc-300">${labels.callout_icon_color}<div class="mt-1 flex flex-wrap items-center gap-1.5"><button type="button" data-pick="icon-color" data-key="" class="h-6 rounded-md border border-zinc-300 px-2 text-xs text-zinc-700 dark:border-zinc-500 dark:text-zinc-200">${labels.callout_same_color}</button>${swatches('icon-color')}</div></div>
        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">${labels.callout_size}<select data-size class="${inputClass} !w-auto">${FONT_SIZES.map((n) => `<option value="${n}">${n}</option>`).join('')}</select></label>
        <input data-search type="search" placeholder="${labels.icon_search}" class="${inputClass}">
        <div data-icons class="grid max-h-40 grid-cols-7 gap-1 overflow-y-auto"></div>
        <div class="flex justify-between text-sm">
            <button type="button" data-no-icon class="rounded-md px-2 py-1 text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600">${labels.callout_no_icon}</button>
            <button type="button" data-remove class="rounded-md px-2 py-1 text-red-600 hover:bg-zinc-100 dark:text-red-400 dark:hover:bg-zinc-600">${labels.callout_remove}</button>
        </div>`;

    const q = (selector) => panel.querySelector(selector);
    const set = (attributes) => editor.chain().updateAttributes('blockquote', attributes).run();

    fillIconGrid(q('[data-icons]'));
    slide.append(panel);

    q('[data-title]').addEventListener('input', (event) => set({ calloutTitle: event.target.value.trim() || null }));
    q('[data-size]').addEventListener('change', (event) => set({ fontSize: Number(event.target.value) === 14 ? null : `${event.target.value}px` }));
    q('[data-search]').addEventListener('input', (event) => filterIconGrid(q('[data-icons]'), event.target.value));
    q('[data-no-icon]').addEventListener('click', () => set({ calloutIcon: null }));
    q('[data-remove]').addEventListener('click', () => {
        panel.hidden = true;
        editor.chain().focus().toggleBlockquote().run();
    });
    panel.addEventListener('click', (event) => {
        const icon = event.target.closest('[data-icons] [data-icon]')?.dataset.icon;
        const pick = event.target.closest('[data-pick]');

        if (pick) {
            set(pick.dataset.pick === 'color' ? { calloutColor: pick.dataset.key } : { calloutIconColor: pick.dataset.key || null });
        } else if (icon) {
            set({ calloutIcon: icon });
        }
    });

    // Shows the values of the quote under the cursor (the title is left alone while it is being typed).
    const syncPanel = () => {
        const quote = editor.getAttributes('blockquote');

        if (document.activeElement !== q('[data-title]')) {
            q('[data-title]').value = quote.calloutTitle ?? '';
        }

        q('[data-size]').value = parseInt(quote.fontSize, 10) || 14;
        panel.querySelectorAll('[data-pick]').forEach((swatch) => {
            const current = (swatch.dataset.pick === 'color' ? quote.calloutColor : quote.calloutIconColor) ?? '';

            ['ring-2', 'ring-zinc-500', 'ring-offset-1'].forEach((name) => swatch.classList.toggle(name, current === swatch.dataset.key));
        });
    };

    const calloutButton = Object.assign(document.createElement('button'), { type: 'button', textContent: '🗨', title: labels.callout });

    calloutButton.setAttribute('aria-label', labels.callout);
    calloutButton.setAttribute('data-callout-button', '');
    calloutButton.className = 'min-w-8 rounded-md px-2 py-1 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-600';
    calloutButton.addEventListener('click', () => {
        if (!panel.hidden) {
            panel.hidden = true;

            return;
        }

        // A normal paragraph becomes a callout with a default colour, icon and title; an existing quote keeps what it has.
        if (!editor.isActive('blockquote')) {
            editor.chain().focus().toggleBlockquote().run();
            set({ class: 'callout', calloutColor: DEFAULT_COLOR, calloutIcon: 'information-circle', calloutTitle: labels.callout_default });
        } else if (!editor.getAttributes('blockquote').class) {
            set({ class: 'callout', calloutColor: DEFAULT_COLOR });
        }

        iconPanel.hidden = true;
        panel.hidden = false;
        syncPanel();
    });
    quoteButton.after(calloutButton);

    document.addEventListener('click', (event) => {
        if (!panel.hidden && !panel.contains(event.target) && !event.target.closest('[data-callout-button]')) {
            panel.hidden = true;
        }
    });
    refreshers.push(() => !panel.hidden && syncPanel());

    const refresh = () => refreshers.forEach((fn) => fn());

    editor.on('transaction', refresh);
    refresh();
}

// A text card is a list of notices: each one has its own editor, and they can be added or removed.
function init() {
    const root = document.getElementById('rich-editor');

    if (!root || root.dataset.ready) {
        return;
    }

    root.dataset.ready = '1';

    const labels = JSON.parse(root.dataset.labels);
    const list = root.querySelector('[data-slides]');
    const template = root.querySelector('[data-slide-template]');
    const slides = () => [...list.querySelectorAll('[data-slide]')];

    // Numbers the notices again and keeps the last one from being removed.
    const renumber = () => slides().forEach((slide, index, all) => {
        slide.querySelector('[data-slide-number]').textContent = index + 1;
        slide.querySelector('[data-remove-slide]').disabled = all.length === 1;
    });

    slides().forEach((slide) => initSlide(slide, root, labels));
    renumber();

    root.querySelector('[data-add-slide]').addEventListener('click', () => {
        const slide = template.content.firstElementChild.cloneNode(true);

        list.append(slide);
        initSlide(slide, root, labels);
        renumber();
        slide.editor.commands.focus();
    });

    list.addEventListener('click', (event) => {
        const slide = event.target.closest('[data-remove-slide]')?.closest('[data-slide]');

        if (slide && slides().length > 1) {
            slide.editor.destroy();
            slide.remove();
            renumber();
        }
    });
}

init();
document.addEventListener('livewire:navigated', init);
