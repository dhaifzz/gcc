function showPage(pageId) {
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });

    const page = document.getElementById(pageId);
    page.classList.add('active');

    window.scrollTo(0, 0);
}
