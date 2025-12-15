<footer class="site-footer mt-auto py-4 bg-dark text-white">
        <div class="container">
            <div class="d-flex flex-column align-items-center">
                <p class="mb-2 text-center">&copy; <?php echo date("Y"); ?> Saprasts. Visas tiesības aizsargātas.</p>

                <div class="footer-links d-flex flex-column align-items-center">
                    <a href="privacy.php" class="text-white text-decoration-none">Privātuma politika</a>
                    <a href="#" class="text-white text-decoration-none" data-bs-toggle="modal" data-bs-target="#contactModal">Sazināties</a>
                </div>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Sazināties ar mums</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Jūsu E-pasts</label>
                            <input type="email" class="form-control" placeholder="vards@piemers.lv">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ziņojums</label>
                            <textarea class="form-control" rows="3" placeholder="Kā mēs varam palīdzēt?"></textarea>
                        </div>
                        <button type="button" class="btn btn-success w-100" data-bs-dismiss="modal" onclick="alert('Ziņa nosūtīta veiksmīgi!')">Nosūtīt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>