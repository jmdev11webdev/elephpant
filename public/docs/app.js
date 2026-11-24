document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('doc-search');
    const sections = Array.from(document.querySelectorAll('.doc-section'));
    const navLinks = Array.from(
        document.querySelectorAll('#section-nav .nav-item'),
    );
    const copyButtons = Array.from(document.querySelectorAll('.copy-btn'));

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();

            sections.forEach((section) => {
                const keywords = (
                    section.getAttribute('data-keywords') || ''
                ).toLowerCase();
                const text = (section.textContent || '').toLowerCase();
                const matches =
                    query === '' ||
                    keywords.includes(query) ||
                    text.includes(query);
                section.classList.toggle('hidden', !matches);
            });
        });
    }

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            navLinks.forEach((item) => item.classList.remove('active'));
            link.classList.add('active');
        });
    });

    copyButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const text = button.getAttribute('data-copy') || '';
            if (!text) {
                return;
            }

            try {
                await navigator.clipboard.writeText(text);
                const original = button.textContent;
                button.textContent = 'Copied!';
                window.setTimeout(() => {
                    button.textContent = original || 'Copy';
                }, 1400);
            } catch {
                button.textContent = 'Select & Copy';
            }
        });
    });
});
