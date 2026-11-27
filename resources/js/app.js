
// The board designer needs GridStack, so it is only loaded on that page.
if (document.getElementById('board-design')) {
    import('./design');
}

// The text card editor (TipTap) is only needed in the card form.
if (document.querySelector('[data-rich-editor]')) {
    import('./rich-editor');
}
