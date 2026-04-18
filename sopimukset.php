<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sopimukset - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div id="mainView">
            <header class="page-header">
                <h1>Sopimukset</h1>
            </header>

            <div class="top-actions">
                <button class="button button--primary" onclick="addAgreement()">Uusi sopimus</button>
                <div class="filter-field">
                    <label for="agreementFilter">Suodata</label>
                    <input type="text" id="agreementFilter" placeholder="Etsi asiakkaan nimellä" oninput="filterAgreements()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="agreementTable">
                    <thead>
                        <tr>
                            <th>Luontipäivämäärä</th>
                            <th>Työkohde</th>
                            <th>Asiakas</th>
                            <th>Summa</th>
                            <th>Laskutettu</th>
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
                <h1 id="detailsTitle">Sopimus</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="agreementInfoView">
                    <h3>Sopimuksen tiedot</h3>
                    <div class="details-row"><span>Tyyppi</span><span id="viewType"></span></div>
                    <div class="details-row"><span>Osia laskussa</span><span id="viewInstallments"></span></div>
                    <div class="details-row"><span>Työkohde</span><span id="viewLocation"></span></div>
                    <div class="details-row"><span>Asiakas</span><span id="viewCustomer"></span></div>
                    <div class="details-row"><span>Summa</span><span id="viewAmount"></span></div>
                    <div class="details-row"><span>Laskutettu</span><span id="viewBilled"></span></div>
                </div>

                <div class="details-card full-width" id="agreementAccessoriesView">
                    <h3>Käytetyt tarvikkeet</h3>
                    <div class="details-row details-field-header">
                        <span>Tarvike</span>
                        <span>Määrä</span>
                        <span>Kerroin</span>
                        <span></span>
                    </div>
                    <div id="viewAccessoriesList"></div>
                </div>

                <div class="details-card full-width" id="agreementWorkView">
                    <h3>Työsuoritus</h3>
                    <div class="details-row details-field-header">
                        <span>Työ</span>
                        <span>Määrä</span>
                        <span>Kerroin</span>
                        <span></span>
                    </div>
                    <div id="viewWorkList"></div>
                </div>

                <div class="details-card full-width edit-panel-wrapper" id="editPanelWrapper">
                    <div class="panel-grid">
                        <div class="panel-section" id="agreementInfoEdit">
                            <h3>Muokkaa sopimusta</h3>
                            <div class="details-row"><label for="editCreated">Luontipäivämäärä</label><input type="date" id="editCreated"></div>
                            <div class="details-row"><label for="editType">Tyyppi</label><select id="editType">
                                <option value="Urakka">Urakka</option>
                                <option value="Tuntihinta">Tuntihinta</option>
                            </select></div>
                            <div class="details-row"><label for="editInstallments">Osia laskussa</label><input type="number" id="editInstallments" min="1" step="1"></div>
                            <div class="details-row"><label for="editLocation">Työkohde</label><input type="text" id="editLocation"></div>
                            <div class="details-row"><label for="editCustomer">Asiakas</label><input type="text" id="editCustomer"></div>
                            <div class="details-row"><label for="editAmount">Summa</label><input type="number" id="editAmount" min="0" step="0.01"></div>
                            <div class="details-row"><label for="editBilled">Laskutettu</label><select id="editBilled">
                                <option value="false">Ei</option>
                                <option value="true">Kyllä</option>
                            </select></div>
                        </div>

                        <div class="panel-row">
                            <div class="panel-section" id="accessoryPanel">
                                <h3>Käytetyt tarvikkeet</h3>
                                <div class="details-row details-field-header">
                                    <span>Tarvike</span>
                                    <span>Määrä</span>
                                    <span>Kerroin</span>
                                    <span></span>
                                </div>
                                <div id="accessoryRows"></div>
                                <button class="button button--secondary" onclick="addAccessoryRow()">Lisää rivi</button>
                            </div>

                            <div class="panel-section" id="workPanel">
                                <h3>Työsuoritus</h3>
                                <div class="details-row details-field-header">
                                    <span>Työ</span>
                                    <span>Määrä</span>
                                    <span>Kerroin</span>
                                    <span></span>
                                </div>
                                <div id="workRows"></div>
                                <button class="button button--secondary" onclick="addWorkRow()">Lisää rivi</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary" id="createInvoiceBtn" class="hidden" onclick="createInvoice()">Luo lasku</button>
                    <button class="button button--primary" id="saveAgreementBtn" onclick="saveAgreement()" style="display: none;">Tallenna</button>
                    <button class="button button--ghost" onclick="backToMain()">Peruuta</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let agreements = [];
        let customers = [];
        let locations = [];
        let activeAgreementId = null;
        let editMode = false;

        async function init() {
            const res = await fetch('methods/sopimukset_methods.php');
            const data = await res.json();

            if (!data.success) return;

            agreements = data.agreements;
            customers = data.customers;
            locations = data.locations;

            renderAgreementRows();
        }

        init();
        
        function formatCurrency(value) {
            return `${parseFloat(value).toFixed(2).replace('.', ',')} €`;
        }

        function renderAgreementRows() {
            const tbody = document.querySelector('#agreementTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#agreementFilter').value.toLowerCase();

            agreements
                .filter(a => a.asiakas_nimi.toLowerCase().includes(filter))
                .forEach(agreement => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${agreement.luotu}</td>
                        <td>${agreement.kohde_nimi}</td>
                        <td>${agreement.asiakas_nimi}</td>
                        <td>${formatCurrency(agreement.kokonaishinta)}</td>
                        <td>${agreement.laskutettu ? 'Kyllä' : 'Ei'}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showAgreement(${agreement.sopimus_id})">Näytä</button>
                            <button class="button button--ghost" onclick="editAgreement(${agreement.sopimus_id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterAgreements() {
            renderAgreementRows();
        }

        function createAccessoryRow(data = { item: '', quantity: 0, factor: 0 }) {
            const row = document.createElement('div');
            row.className = 'details-row accessory-row';
            row.innerHTML = `
                <select class="accessory-item">
                    <option value="" disabled ${data.item === '' ? 'selected' : ''}>Valitse tarvike...</option>
                    <option value="Ruuvimeisseli" ${data.item === 'Ruuvimeisseli' ? 'selected' : ''}>Ruuvimeisseli</option>
                    <option value="Poranterä 10mm" ${data.item === 'Poranterä 10mm' ? 'selected' : ''}>Poranterä 10mm</option>
                    <option value="Työkalupakki" ${data.item === 'Työkalupakki' ? 'selected' : ''}>Työkalupakki</option>
                </select>
                <input type="number" class="accessory-quantity" min="0" step="1" value="${data.quantity}">
                <input type="number" class="accessory-factor" min="0" step="0.1" value="${data.factor}">
                <button type="button" class="button button--ghost" onclick="removeRow(this)">Poista</button>
            `;
            return row;
        }

        function createWorkRow(data = { type: '', quantity: 0, factor: 0 }) {
            const row = document.createElement('div');
            row.className = 'details-row work-row';
            row.innerHTML = `
                <select class="work-type">
                    <option value="" disabled ${data.type === '' ? 'selected' : ''}>Valitse työ...</option>
                    <option value="Suunnittelu" ${data.type === 'Suunnittelu' ? 'selected' : ''}>Suunnittelu</option>
                    <option value="Työ" ${data.type === 'Työ' ? 'selected' : ''}>Työ</option>
                    <option value="Aputyö" ${data.type === 'Aputyö' ? 'selected' : ''}>Aputyö</option>
                </select>
                <input type="number" class="work-quantity" min="0" step="1" value="${data.quantity}">
                <input type="number" class="work-factor" min="0" step="0.1" value="${data.factor}">
                <button type="button" class="button button--ghost" onclick="removeRow(this)">Poista</button>
            `;
            return row;
        }

        function addAccessoryRow(data) {
            document.getElementById('accessoryRows').appendChild(createAccessoryRow(data));
        }

        function addWorkRow(data) {
            document.getElementById('workRows').appendChild(createWorkRow(data));
        }

        function removeRow(button) {
            button.closest('.details-row').remove();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('agreementInfoView').classList.toggle('hidden', editMode);
            document.getElementById('agreementInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('agreementAccessoriesView').classList.toggle('hidden', editMode);
            document.getElementById('agreementWorkView').classList.toggle('hidden', editMode);
            document.getElementById('accessoryPanel').classList.toggle('hidden', !editMode);
            document.getElementById('workPanel').classList.toggle('hidden', !editMode);
            document.getElementById('editPanelWrapper').classList.toggle('hidden', !editMode);
            document.getElementById('createInvoiceBtn').classList.toggle('hidden', editMode);
            document.getElementById('saveAgreementBtn').style.display = editMode ? 'inline-flex' : 'none';
        }

        function createInvoice() {
            // TODO: Implement invoice creation
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeAgreementId = null;
        }

        function populateAccessoryRows(items) {
            const container = document.getElementById('accessoryRows');
            container.innerHTML = '';
            if (!items || items.length === 0) {
                addAccessoryRow();
                return;
            }
            items.forEach(item => addAccessoryRow(item));
        }

        function populateWorkRows(items) {
            const container = document.getElementById('workRows');
            container.innerHTML = '';
            if (!items || items.length === 0) {
                addWorkRow();
                return;
            }
            items.forEach(item => addWorkRow(item));
        }

        function renderViewList(containerId, items, itemRenderer) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (!items || items.length === 0) {
                container.innerHTML = '<p>Ei rivejä.</p>';
                return;
            }
            items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'details-row';
                row.innerHTML = itemRenderer(item);
                container.appendChild(row);
            });
        }

        function showAgreement(id) {
            const agreement = agreements.find(item => item.id === id);
            if (!agreement) return;
            activeAgreementId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Sopimus: ${agreement.customer}`;
            document.getElementById('viewType').textContent = agreement.type;
            document.getElementById('viewInstallments').textContent = agreement.installments;
            document.getElementById('viewCreated').textContent = agreement.created;
            document.getElementById('viewLocation').textContent = agreement.location;
            document.getElementById('viewCustomer').textContent = agreement.customer;
            document.getElementById('viewAmount').textContent = formatCurrency(agreement.amount);
            document.getElementById('viewBilled').textContent = agreement.billed ? 'Kyllä' : 'Ei';
            renderViewList('viewAccessoriesList', agreement.accessories, item => `
                <span>${item.item}</span>
                <span>${item.quantity} kpl</span>
                <span>x ${item.factor.toFixed(1)}</span>
            `);
            renderViewList('viewWorkList', agreement.work, item => `
                <span>${item.type}</span>
                <span>${item.quantity}</span>
                <span>x ${item.factor.toFixed(1)}</span>
            `);
        }

        function editAgreement(id) {
            const agreement = agreements.find(item => item.id === id);
            if (!agreement || agreement.billed) return;
            activeAgreementId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa sopimusta: ${agreement.customer}`;
            document.getElementById('editType').value = agreement.type;
            document.getElementById('editInstallments').value = agreement.installments;
            document.getElementById('editCreated').value = agreement.created;
            document.getElementById('editLocation').value = agreement.location;
            document.getElementById('editCustomer').value = agreement.customer;
            document.getElementById('editAmount').value = agreement.amount;
            document.getElementById('editBilled').value = agreement.billed.toString();
            populateAccessoryRows(agreement.accessories);
            populateWorkRows(agreement.work);
        }

        function addAgreement() {
            activeAgreementId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Uusi sopimus';
            document.getElementById('editType').value = 'Urakka';
            document.getElementById('editInstallments').value = 0;
            document.getElementById('editCreated').value = new Date().toISOString().split('T')[0];
            document.getElementById('editLocation').value = '';
            document.getElementById('editCustomer').value = '';
            document.getElementById('editAmount').value = '';
            document.getElementById('editBilled').value = 'false';
            populateAccessoryRows([]);
            populateWorkRows([]);
        }

        function collectAccessoryRows() {
            return Array.from(document.querySelectorAll('#accessoryRows .details-row')).map(row => ({
                item: row.querySelector('.accessory-item').value,
                quantity: parseInt(row.querySelector('.accessory-quantity').value, 10) || 0,
                factor: parseFloat(row.querySelector('.accessory-factor').value) || 1.0
            }));
        }

        function collectWorkRows() {
            return Array.from(document.querySelectorAll('#workRows .details-row')).map(row => ({
                type: row.querySelector('.work-type').value,
                quantity: parseInt(row.querySelector('.work-quantity').value, 10) || 0,
                factor: parseFloat(row.querySelector('.work-factor').value) || 1.0
            }));
        }

        function saveAgreement() {
            const type = document.getElementById('editType').value;
            const installments = parseInt(document.getElementById('editInstallments').value, 10);
            const created = document.getElementById('editCreated').value;
            const location = document.getElementById('editLocation').value.trim();
            const customer = document.getElementById('editCustomer').value.trim();
            const amount = parseFloat(document.getElementById('editAmount').value);
            const billed = document.getElementById('editBilled').value === 'true';
            const accessories = collectAccessoryRows();
            const work = collectWorkRows();

            if (!created || !location || !customer || isNaN(amount) || isNaN(installments) || installments < 1) {
                alert('Täytä kaikki kentät ennen tallentamista.');
                return;
            }

            if (activeAgreementId) {
                const agreement = agreements.find(item => item.id === activeAgreementId);
                if (!agreement) return;
                agreement.type = type;
                agreement.installments = installments;
                agreement.created = created;
                agreement.location = location;
                agreement.customer = customer;
                agreement.amount = amount;
                agreement.billed = billed;
                agreement.accessories = accessories;
                agreement.work = work;
            } else {
                agreements.push({
                    id: Date.now(),
                    type,
                    installments,
                    created,
                    location,
                    customer,
                    amount,
                    billed,
                    accessories,
                    work
                });
            }

            renderAgreementRows();
            backToMain();
        }

    </script>
</body>
</html>