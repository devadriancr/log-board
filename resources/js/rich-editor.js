import { Editor, Extension, textblockTypeInputRule } from '@tiptap/core';
import { marked } from 'marked';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import { FontSize, TextStyle } from '@tiptap/extension-text-style';

// Sizes offered in the toolbar, in px like the text of the page (the base size of a text card is 14).
const FONT_SIZES = [10, 11, 12, 14, 16, 18, 20, 24, 28, 32, 36, 48, 64];

// Fixed image widths (px) offered in the toolbar: the same preset gives the same size in every notice and card.
const IMAGE_SIZES = { image_small: ['P', 80], image_medium: ['M', 140], image_large: ['G', 220] };

// Markdown pasted as plain text (a .md file, Notepad, VS Code...) becomes formatted text.
// The editor only has two heading levels, so # and ## are titles and ### or deeper are subtitles.
marked.use({ walkTokens: (token) => { if (token.type === 'heading') token.depth = token.depth <= 2 ? 2 : 3; } });

// GitHub alerts ("> [!NOTE]") have no equivalent in the editor: they become a quote that starts with a bold label.
// shortcut: no colored box, only the quote style; upgrade with a dedicated Tiptap node + class allowed in config/purify.php.
const ALERTS = { NOTE: 'Nota', TIP: 'Consejo', IMPORTANT: 'Importante', WARNING: 'Advertencia', CAUTION: 'Precaución' };

marked.use({
    hooks: {
        preprocess: (text) => text.replace(/^(\s{0,3}>[ \t]*)\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\][ \t]*$/gim, (_, quote, type) => `${quote}**${ALERTS[type.toUpperCase()]}**`),
    },
});

const MARKDOWN = [
    /^\s{0,3}#{1,6}\s+\S/m, // # title
    /^\s*(?:[-*+]|\d+[.)])\s+\S/m, // - list, 1. list
    /^\s{0,3}>\s+\S/m, // > quote
    /^\s{0,3}(?:```|---+\s*$)/m, // code block, rule
    /\*\*[^*\n]+\*\*|~~[^~\n]+~~|`[^`\n]+`/, // **bold**, ~~strike~~, `code`
    /\[[^\]\n]+\]\(https?:\/\/[^)\s]+\)/, // [text](https://...)
];

const looksLikeMarkdown = (text) => MARKDOWN.some((pattern) => pattern.test(text));

// Typing "# " also makes a title (the shortcuts "## " and "### " already come with the editor).
const HashTitle = Extension.create({
    name: 'hashTitle',
    addInputRules() {
        return [textblockTypeInputRule({ find: /^#\s$/, type: this.editor.schema.nodes.heading, getAttributes: { level: 2 } })];
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
            HashTitle,
        ],
        editorProps: {
            attributes: { class: 'rich-content min-h-48 px-3 py-2 outline-none' },
            handlePaste: (view, event) => {
                const data = event.clipboardData;
                const text = data?.getData('text/plain') ?? '';

                // Text copied from a web page or Word already has its own format: only plain text (or VS Code) is read as Markdown.
                const plain = !data?.types.includes('text/html') || data.types.includes('vscode-editor-data');

                if (!plain || !looksLikeMarkdown(text)) {
                    return false;
                }

                editor.commands.insertContent(marked.parse(text, { gfm: true, breaks: true }));

                return true;
            },
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

    // [text, label key, command, is active?, only enabled while an image is selected]
    const buttons = [
        ['B', 'bold', () => editor.chain().focus().toggleBold().run(), () => editor.isActive('bold')],
        ['I', 'italic', () => editor.chain().focus().toggleItalic().run(), () => editor.isActive('italic')],
        ['U', 'underline', () => editor.chain().focus().toggleUnderline().run(), () => editor.isActive('underline')],
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
