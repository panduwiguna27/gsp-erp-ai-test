@push('scripts')
<script>
    (function () {
        const tbody = document.getElementById('lines');
        const tpl = document.getElementById('line-template');
        if (!tbody || !tpl) return;

        let index = 0;

        const money = (value) =>
            new Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });

        function recalc() {
            let total = 0;
            tbody.querySelectorAll('.line-row').forEach(function (row) {
                const qty = parseFloat(row.querySelector('[data-qty]').value) || 0;
                const price = parseFloat(row.querySelector('[data-price]').value) || 0;
                const subtotal = qty * price;
                row.querySelector('[data-subtotal]').textContent = money(subtotal);
                total += subtotal;
            });
            document.getElementById('grand-total').textContent = money(total);
        }

        function addLine() {
            tbody.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__I__/g, index));
            index++;
            recalc();
        }

        tbody.addEventListener('input', function (event) {
            const row = event.target.closest('.line-row');
            if (!row) return;

            if (event.target.hasAttribute('data-price-source')) {
                const chosen = event.target.selectedOptions[0];
                if (chosen && chosen.dataset.price) {
                    row.querySelector('[data-price]').value = chosen.dataset.price;
                }
            }
            recalc();
        });

        tbody.addEventListener('click', function (event) {
            if (event.target.hasAttribute('data-remove')) {
                event.target.closest('.line-row').remove();
                recalc();
            }
        });

        document.getElementById('add-line').addEventListener('click', addLine);
        addLine();
    })();
</script>
@endpush
