import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';

// Images carry their position as a class (img-left / img-center / img-right): the server only lets those through.
const PositionedImage = Image.extend({
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

function init() {
    const root = document.getElementById('rich-editor');

    if (!root || root.dataset.ready) {
        return;
    }

    root.dataset.ready = '1';

    const labels = JSON.parse(root.dataset.labels);
    const field = document.getElementById('rich-editor-content');
    const toast = (text, variant) => window.Flux?.toast({ text, variant });

    const editor = new Editor({
        element: root.querySelector('[data-editor]'),
        content: field.value,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
                link: { openOnClick: false, HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' } },
            }),
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            PositionedImage,
        ],
        editorProps: { attributes: { class: 'rich-content min-h-48 px-3 py-2 outline-none' } },
        onUpdate: ({ editor }) => {
            field.value = editor.isEmpty ? '' : editor.getHTML();
        },
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
            editor.chain().focus().setImage({ src: url, alt: file.name }).run();
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
        null,
        ['↶', 'undo', () => editor.chain().focus().undo().run(), () => false],
        ['↷', 'redo', () => editor.chain().focus().redo().run(), () => false],
    ];

    const toolbar = root.querySelector('[data-toolbar]');
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

    const refresh = () => refreshers.forEach((fn) => fn());

    editor.on('transaction', refresh);
    refresh();
}

init();
document.addEventListener('livewire:navigated', init);
