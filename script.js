document.addEventListener('DOMContentLoaded', () => {
    // --- Light/Dark Mode loģika ---
    const themeToggle = document.getElementById('theme-toggle');
    const darkModeText = document.getElementById('dark-mode-text');

    // Pārbaudīt saglabāto tēmu
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        themeToggle.checked = true;
        darkModeText.textContent = 'Light Mode';
    } else {
        darkModeText.textContent = 'Dark Mode';
    }

    // Tēmas pārslēgšana
    themeToggle.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
            darkModeText.textContent = 'Light Mode';
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
            darkModeText.textContent = 'Dark Mode';
        }
    });

    // --- Psihologa Modālā loga loģika ---
    const detailButtons = document.querySelectorAll('.details-btn');
    const modalTitle = document.getElementById('psychologistModalLabel');
    const modalBody = document.getElementById('modalBodyContent');

    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Ielasa datus no pogas (data-atribūtiem)
            const vards = this.getAttribute('data-vards');
            const spec = this.getAttribute('data-spec');
            const pieredze = this.getAttribute('data-pieredze');
            const apraksts = this.getAttribute('data-apraksts');
            const cena = this.getAttribute('data-cena');
            const attels = this.getAttribute('data-attels');
            
            modalTitle.textContent = vards;
            
            // Ģenerē Modālā loga saturu
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
        });
    });
});