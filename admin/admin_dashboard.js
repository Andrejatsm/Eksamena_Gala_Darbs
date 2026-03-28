document.addEventListener('DOMContentLoaded', () => {
    const testPreviewModal = document.getElementById('testPreviewModal');
    const testPreviewFrame = document.getElementById('testPreviewFrame');
    const closeTestPreviewTop = document.getElementById('closeTestPreviewTop');
    const closeTestPreviewBottom = document.getElementById('closeTestPreviewBottom');
    const testPreviewBackdrop = document.getElementById('testPreviewBackdrop');
    const psychModal = document.getElementById('psychModal');
    const psychModalBackdrop = document.getElementById('psychModalBackdrop');
    const closePsychModalBtn = document.getElementById('closePsychModalBtn');
    const articleModal = document.getElementById('articleModal');
    const articleModalBackdrop = document.getElementById('articleModalBackdrop');
    const closeArticleModalBtn = document.getElementById('closeArticleModalBtn');

    const closeTestPreviewModal = () => {
        if (!testPreviewModal || !testPreviewFrame) return;
        testPreviewModal.classList.add('hidden');
        testPreviewFrame.src = '';
    };

    const closePsychModal = () => {
        if (psychModal) {
            psychModal.classList.add('hidden');
        }
    };

    const closeArticleModal = () => {
        if (articleModal) {
            articleModal.classList.add('hidden');
        }
    };

    document.querySelectorAll('.view-test-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!testPreviewModal || !testPreviewFrame) return;
            testPreviewFrame.src = btn.dataset.testUrl || '';
            testPreviewModal.classList.remove('hidden');
        });
    });

    if (closeTestPreviewTop) {
        closeTestPreviewTop.addEventListener('click', closeTestPreviewModal);
    }
    if (closeTestPreviewBottom) {
        closeTestPreviewBottom.addEventListener('click', closeTestPreviewModal);
    }
    if (testPreviewBackdrop) {
        testPreviewBackdrop.addEventListener('click', closeTestPreviewModal);
    }
    if (psychModalBackdrop) {
        psychModalBackdrop.addEventListener('click', closePsychModal);
    }
    if (closePsychModalBtn) {
        closePsychModalBtn.addEventListener('click', closePsychModal);
    }
    if (articleModalBackdrop) {
        articleModalBackdrop.addEventListener('click', closeArticleModal);
    }
    if (closeArticleModalBtn) {
        closeArticleModalBtn.addEventListener('click', closeArticleModal);
    }

    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            tabBtns.forEach((b) => b.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white', 'shadow-sm'));
            tabBtns.forEach((b) => b.classList.add('text-gray-600', 'dark:text-gray-400'));
            tabBtns.forEach((b) => b.classList.remove('text-gray-900', 'dark:text-white'));
            btn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white', 'shadow-sm');
            btn.classList.remove('text-gray-600', 'dark:text-gray-400');
            document.querySelectorAll('.tab-content').forEach((t) => t.classList.add('hidden'));
            const activeTab = document.getElementById(btn.dataset.tab);
            if (activeTab) {
                activeTab.classList.remove('hidden');
            }
        });
    });

    document.querySelectorAll('.view-psych-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const psychModalName = document.getElementById('psychModalName');
            const psychModalSpec = document.getElementById('psychModalSpec');
            const psychModalExp = document.getElementById('psychModalExp');
            const psychModalEmail = document.getElementById('psychModalEmail');
            const psychModalPhone = document.getElementById('psychModalPhone');
            const psychModalDesc = document.getElementById('psychModalDesc');
            const psychModalApproveId = document.getElementById('psychModalApproveId');
            const psychModalRejectId = document.getElementById('psychModalRejectId');
            const certContainer = document.getElementById('psychModalCertContainer');

            if (!psychModalName || !psychModalSpec || !psychModalExp || !psychModalEmail || !psychModalPhone || !psychModalDesc || !psychModalApproveId || !psychModalRejectId || !psychModal || !certContainer) {
                return;
            }

            psychModalName.textContent = btn.dataset.name || '';
            psychModalSpec.textContent = btn.dataset.spec || '';
            psychModalExp.textContent = btn.dataset.exp || '';
            psychModalEmail.textContent = btn.dataset.email || '';
            psychModalPhone.textContent = btn.dataset.phone || '';
            psychModalDesc.textContent = btn.dataset.desc || 'Apraksts nav sniegts.';
            psychModalApproveId.value = btn.dataset.id || '';
            psychModalRejectId.value = btn.dataset.id || '';

            if (btn.dataset.cert) {
                certContainer.innerHTML = `<a href="${btn.dataset.cert}" target="_blank" class="text-blue-500 hover:underline"><i class="fas fa-file-pdf mr-2"></i>Apskatīt failu</a>`;
            } else {
                certContainer.innerHTML = '<span class="text-red-500">Fails nav pievienots.</span>';
            }
            psychModal.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.view-article-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const articleModalTitle = document.getElementById('articleModalTitle');
            const articleModalAuthor = document.getElementById('articleModalAuthor');
            const articleModalContent = document.getElementById('articleModalContent');
            const articleModalApproveId = document.getElementById('articleModalApproveId');
            const articleModalRejectId = document.getElementById('articleModalRejectId');
            const articleModalAccId = document.getElementById('articleModalAccId');

            if (!articleModalTitle || !articleModalAuthor || !articleModalContent || !articleModalApproveId || !articleModalRejectId || !articleModalAccId || !articleModal) {
                return;
            }

            articleModalTitle.textContent = btn.dataset.title || '';
            articleModalAuthor.textContent = 'Autors: ' + (btn.dataset.author || '');
            articleModalContent.textContent = btn.dataset.content || '';
            articleModalApproveId.value = btn.dataset.id || '';
            articleModalRejectId.value = btn.dataset.id || '';
            articleModalAccId.value = btn.dataset.acc || '';
            articleModal.classList.remove('hidden');
        });
    });
});
