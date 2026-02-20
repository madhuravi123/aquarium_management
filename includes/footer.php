<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
/* Global Tom Select auto-init — runs on every page.
   Initialises all <select class="form-select"> elements that are already in
   the DOM at DOMContentLoaded time. Dynamic rows (sales_new / purchases_new)
   initialise their own selects immediately after inserting the row HTML. */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select.form-select:not([data-no-ts])').forEach(function (el) {
        if (!el.tomselect) {
            new TomSelect(el, { allowEmptyOption: true, create: false });
        }
    });
});
</script>
</body>

</html>