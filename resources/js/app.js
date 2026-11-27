
// Registers the Alpine component of the link previews, so it must be loaded before Alpine starts.
import './link-preview';

// The board designer needs GridStack, so it is only loaded on that page.
if (document.getElementById('board-design')) {
    import('./design');
}

// The text card editor (TipTap) is only needed in the card form.
if (document.querySelector('[data-rich-editor]')) {
    import('./rich-editor');
}
