document.addEventListener('DOMContentLoaded', () => {
    // --- Light/Dark Mode loģika paliek tā pati ---
    const themeToggle = document.getElementById('theme-toggle');
    const darkModeText = document.getElementById('dark-mode-text');

    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        if(themeToggle) themeToggle.checked = true;
        if(darkModeText) darkModeText.textContent = 'Light Mode';
    }

    if(themeToggle) {
        themeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
                if(darkModeText) darkModeText.textContent = 'Light Mode';
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
                if(darkModeText) darkModeText.textContent = 'Dark Mode';
            }
        });
    }

    // --- ASINHRONĀ MEKLĒŠANA UN PAGINĀCIJA ---
    
    const searchInput = document.getElementById('searchInput');
    const container = document.getElementById('psychologistsContainer');
    const paginationControls = document.getElementById('paginationControls');
    let currentPage = 1;

    // Funkcija datu ielādei
    function loadPsychologists(page = 1, query = '') {
        const url = `fetch_psychologists.php?page=${page}&search=${encodeURIComponent(query)}`;
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Sadalām atbildi (HTML saturs vs Paginācijas dati)
                // Paginācijas dati ir paslēptā divā beigās
                container.innerHTML = html;
                
                // Atrodam paginācijas info no saņemtā HTML
                const metaData = document.getElementById('pagination-data');
                if (metaData) {
                    const totalPages = parseInt(metaData.getAttribute('data-total-pages'));
                    renderPagination(totalPages, page);
                } else {
                    paginationControls.innerHTML = ''; // Nav ko paginēt
                }
            })
            .catch(err => console.error('Kļūda ielādējot datus:', err));
    }

    // Funkcija paginācijas pogu zīmēšanai
    function renderPagination(totalPages, currentPage) {
        let buttons = '';
        
        // Iepriekšējā lapa
        buttons += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Atpakaļ</a>
        </li>`;

        // Cipari
        for (let i = 1; i <= totalPages; i++) {
            buttons += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>`;
        }

        // Nākamā lapa
        buttons += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Tālāk</a>
        </li>`;

        paginationControls.innerHTML = buttons;
    }

    // Globāla funkcija, lai HTML onclick varētu to izsaukt
    window.changePage = function(page) {
        if (page < 1) return;
        currentPage = page;
        loadPsychologists(currentPage, searchInput.value);
    };

    // Klausāmies uz meklēšanas lauka izmaiņām
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentPage = 1; // Meklējot atgriežamies uz 1. lapu
            loadPsychologists(1, e.target.value);
        });
    }

    // Ielādējam datus pirmo reizi
    if (container) {
        loadPsychologists(1);
    }

    // --- MODĀLĀ LOGA LOĢIKA (Event Delegation) ---
    // Mēs klausāmies uz visu 'document', jo pogas tiek ielādētas dinamiski
    document.addEventListener('click', function(e) {
        // Pārbaudām vai uzklikšķināts uz elementa ar klasi 'details-btn'
        if (e.target && e.target.classList.contains('details-btn')) {
            const btn = e.target;
            const modalTitle = document.getElementById('psychologistModalLabel');
            const modalBody = document.getElementById('modalBodyContent');

            const vards = btn.getAttribute('data-vards');
            const spec = btn.getAttribute('data-spec');
            const pieredze = btn.getAttribute('data-pieredze');
            const apraksts = btn.getAttribute('data-apraksts');
            const cena = btn.getAttribute('data-cena');
            const attels = btn.getAttribute('data-attels');
            
            modalTitle.textContent = vards;
            
            modalBody.innerHTML = `
                <img src="${attels}" class="img-fluid rounded-circle mb-3 border border-dark border-3" style="width: 150px; height: 150px; object-fit: cover;" alt="${vards}">
                <p><strong>Specializācija:</strong> ${spec}</p>
                <p><strong>Pieredze:</strong> ${pieredze} gadi</p>
                <p><strong>Cena (1h):</strong> ${cena} EUR</p>
                <p class="text-start mt-3"><strong>Apraksts:</strong> ${apraksts}</p>
                
                <form action="checkout.php" method="POST">
                    <input type="hidden" name="psihologs_vards" value="${vards}">
                    <input type="hidden" name="cena" value="${parseFloat(cena.replace(',', '.'))}">
                    <button type="submit" class="btn btn-success mt-3 w-100">
                        Pieteikties un Maksāt (${cena} EUR)
                    </button>
                </form>
            `;
        }
    });
});