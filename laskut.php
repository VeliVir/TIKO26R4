<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laskut - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div id="mainView">
            <header class="page-header">
                <h1>Laskut</h1>
            </header>

            <div class="top-actions">
                <button class="button button--primary" onclick="createReminders()">Luo muistutuslaskuja</button>
                <div class="filter-field">
                    <label for="invoiceFilter">Suodata</label>
                    <input type="text" id="invoiceFilter" placeholder="Etsi asiakkaan nimellä" oninput="filterInvoices()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="invoiceTable">
                    <thead>
                        <tr>
                            <th>Laskutuspäivämäärä</th>
                            <th>Eräpäivämäärä</th>
                            <th>Asiakas</th>
                            <th>Summa</th>
                            <th>Maksettu</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="detailsView" class="hidden">
            <div class="details-view-header">
                <button class="button button--ghost back-button" onclick="backToMain()">← Takaisin</button>
                <h1 id="detailsTitle">Lasku</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="invoiceInfoView">
                    <h3>Laskun tiedot</h3>
                    <div class="details-row"><span>Laskutuspäivämäärä</span><span id="viewBillingDate"></span></div>
                    <div class="details-row"><span>Eräpäivämäärä</span><span id="viewDueDate"></span></div>
                    <div class="details-row"><span>Asiakas</span><span id="viewCustomer"></span></div>
                    <div class="details-row"><span>Summa</span><span id="viewAmount"></span></div>
                    <div class="details-row"><span>Maksettu</span><span id="viewPaid"></span></div>
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary" id="saveInvoiceBtn" onclick="saveInvoice()" style="display: none;">Tallenna</button>
                    <button class="button button--ghost" onclick="backToMain()">Peruuta</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const invoices = [
            {
                id: 1,
                billingDate: '2026-04-01',
                dueDate: '2026-05-01',
                customer: 'Seppo Tärsky',
                amount: 1290.00,
                paid: false
            },
            {
                id: 2,
                billingDate: '2026-03-15',
                dueDate: '2026-04-15',
                customer: 'Laura Lehtonen',
                amount: 720.50,
                paid: true
            },
            {
                id: 3,
                billingDate: '2026-04-08',
                dueDate: '2026-05-08',
                customer: 'Matti Meikäläinen',
                amount: 450.00,
                paid: false
            }
        ];

        let activeInvoiceId = null;
        let editMode = false;

        function formatCurrency(value) {
            return `${value.toFixed(2).replace('.', ',')} €`;
        }

        function renderInvoiceRows() {
            const tbody = document.querySelector('#invoiceTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#invoiceFilter').value.toLowerCase();

            invoices
                .filter(invoice => invoice.customer.toLowerCase().includes(filter))
                .forEach(invoice => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${invoice.billingDate}</td>
                        <td>${invoice.dueDate}</td>
                        <td>${invoice.customer}</td>
                        <td>${formatCurrency(invoice.amount)}</td>
                        <td>${invoice.paid ? 'Kyllä' : 'Ei'}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showInvoice(${invoice.id})">Näytä</button>
                            ${invoice.paid ? '' : `<button class="button button--ghost" onclick="editInvoice(${invoice.id})">Muokkaa</button>`}
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterInvoices() {
            renderInvoiceRows();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('invoiceInfoView').classList.toggle('hidden', editMode);
            document.getElementById('saveInvoiceBtn').style.display = editMode ? 'inline-flex' : 'none';
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeInvoiceId = null;
        }

        function showInvoice(id) {
            const invoice = invoices.find(item => item.id === id);
            if (!invoice) return;
            activeInvoiceId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Lasku: ${invoice.customer}`;
            document.getElementById('viewBillingDate').textContent = invoice.billingDate;
            document.getElementById('viewDueDate').textContent = invoice.dueDate;
            document.getElementById('viewCustomer').textContent = invoice.customer;
            document.getElementById('viewAmount').textContent = formatCurrency(invoice.amount);
            document.getElementById('viewPaid').textContent = invoice.paid ? 'Kyllä' : 'Ei';
        }

        function editInvoice(id) {
            const invoice = invoices.find(item => item.id === id);
            if (!invoice || invoice.paid) return;
            activeInvoiceId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa laskua: ${invoice.customer}`;
            // TODO: Add edit form fields
        }

        function createReminders() {
            // TODO: Implement reminder invoice creation
        }

        function saveInvoice() {
            // TODO: Implement invoice save
        }

        renderInvoiceRows();
    </script>
</body>
</html>
