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
                            <th>Summa (Ilman ALV)</th>
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
                    <div class="details-row"><span>Luontipäivämäärä</span><span id="viewCreated"></span></div>
                    <div class="details-row"><span>Viimeksi muokattu</span><span id="viewUpdated"></span></div>
                    <div class="details-row"><span>Työkohde</span><span id="viewLocation"></span></div>
                    <div class="details-row"><span>Asiakas</span><span id="viewCustomer"></span></div>
                    <div class="details-row"><span>Summa (Ilman ALV)</span><span id="viewAmount"></span></div>
                    <div class="details-row"><span>Summa (ALV)</span><span id="viewAmountALV"></span></div>
                    <div class="details-row"><span>Laskutettu</span><span id="viewBilled"></span></div>
                </div>

                <div class="details-card full-width" id="agreementAccessoriesView">
                    <h3>Käytetyt tarvikkeet</h3>
                    <div class="details-row details-field-header">
                        <span>Tarvike</span>
                        <span>Määrä</span>
                        <span>Alennus %</span>
                        <span>Ostohinta</span>
                        <span>Myyntihinta</span>
                        <span>Summa (Ilman ALV)</span>
                        <span>ALV</span>
                        <span>Loppuhinta</span>
                    </div>
                    <div class="details-table-row" id="viewAccessoriesList"></div>
                </div>

                <div class="details-card full-width" id="agreementWorkView">
                    <h3>Työsuoritus</h3>
                    <div class="details-row details-field-header">
                        <span>Työ</span>
                        <span>Määrä</span>
                        <span>Alennus %</span>
                        <span>Hinta (Ilman ALV ja alennuksia)</span>
                        <span>Hinta (Ilman ALV)</span>
                        <span>ALV</span>
                        <span>Loppuhinta</span>
                    </div>
                    <div class="details-table-row" id="viewWorkList"></div>
                </div>

                <div class="details-card full-width" id="agreementUrakkaWorkView">
                    <h3>Työsuoritus</h3>
                    <div class="details-row details-field-header">
                        <span>Työ</span>
                        <span>Hinta (Ilman ALV)</span>
                        <span>ALV</span>
                        <span>Loppuhinta</span>
                    </div>
                    <div class="details-table-row" id="viewUrakkaWorkList"></div>
                </div>

                <div class="details-card full-width edit-panel-wrapper" id="editPanelWrapper">
                    <div class="panel-grid">
                        <div class="panel-section" id="agreementInfoEdit">
                            <h3 id="agreementPrompt"></h3>
                            <div class="details-row">
                                <label>Työkohde</label>
                                    <span id="viewLocationEdit"></span>
                                    <select id="editLocation" class="hidden" onchange="onLocationSelect(this.value)"></select>
                            </div>
                            <div id="locationInfo" class="hidden">
                                <div class="details-row"><span>Asiakas</span><span id="locationCustomerName"></span></div>
                                <div class="details-row"><span>Osoite</span><span id="locationAddress"></span></div>
                            </div>
                            <div class="details-row" id="customerEditRow">
                                <label>Asiakas</label>
                                <span id="viewCustomerEdit" class="details-value"></span>
                            </div>
                            <div class="details-row"><label for="editType">Tyyppi</label>
                                <select id="editType" onchange="toggleWorkRow(this.value)">
                                    <option value="Urakka">Urakka</option>
                                    <option value="Tuntihinta">Tuntihinta</option>
                                </select>
                            </div>
                            <div class="details-row"><label for="editInstallments">Osia laskussa</label><input type="number" id="editInstallments" min="1" step="1"></div>
                            </select></div>
                        </div>

                        <div class="panel-row">
                            <div class="panel-section" id="accessoryPanel">
                                <h3>Käytetyt tarvikkeet</h3>
                                <div class="details-row details-field-header">
                                    <span></span>
                                    <span>Tarvike</span>
                                    <span>Määrä</span>
                                    <span>Alennus %</span>
                                </div>
                                <div id="accessoryRows"></div>
                                <button class="button button--secondary" onclick="addAccessoryRow()">Lisää rivi</button>
                            </div>

                            <div class="panel-section" id="workPanel">
                                <h3>Työsuoritus</h3>
                                <div class="details-row details-field-header">
                                    <span></span>
                                    <span>Työ</span>
                                    <span>Määrä</span>
                                    <span>Alennus %</span>
                                </div>
                                <div id="workRows"></div>
                                <button id="addWorkRowBtn" class="button button--secondary" onclick="addWorkRow()">Lisää rivi</button>
                            </div>
                            <div class="panel-section" id="urakkaWorkPanel">
                                <h3>Työsuoritus</h3>
                                <div class="details-row details-field-header">
                                    <span></span>
                                    <span>Työ</span>
                                    <span>Määrä</span>
                                </div>
                                <div id="urakkaWorkRows"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="details-card full-width hidden" id="createInvoicePanel" style="margin-top: 24px;">
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary button--large" id="createInvoiceBtn" onclick="createInvoice()">Luo lasku</button>
                    <button class="button button--primary" id="saveAgreementBtn" onclick="saveAgreement()" style="display: none;">Tallenna</button>
                    <button class="button button--danger" id="deleteAgreementBtn" onclick="deleteAgreement()" style="display: none;">Poista</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let agreements = [];
        let customers = [];
        let locations = [];
        let accessories = [];
        let work = [];

        let activeAgreementId = null;
        let editMode = false;
        let isUrakka = true;

        async function init() {
            const res = await fetch('methods/sopimukset_methods.php');
            const data = await res.json();

            if (!data.success) return;

            agreements = data.agreements;
            customers = data.customers;
            locations = data.locations;
            accessories = data.accessories;
            work = data.work;
            uniqueAccessories = data.uniqueAccessories;
            uniqueWorkTypes = data.uniqueWorkTypes;

            renderAgreementRows();
        }

        init();
        
        function formatCurrency(value) {
            return `${parseFloat(value).toFixed(2).replace('.', ',')} €`;
        }

        function toFinnishDate(isoDate) {
            if (!isoDate) return '-';
            const [y, m, d] = isoDate.split('T')[0].split('-');
            return `${parseInt(d)}.${parseInt(m)}.${y}`;
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
                        <td>${toFinnishDate(agreement.luotu)}</td>
                        <td>${agreement.kohde_nimi}</td>
                        <td>${agreement.asiakas_nimi}</td>
                        <td>${formatCurrency(agreement.kokonaishinta || 0)}</td>
                        <td>${Number(agreement.laskutettu) ? 'Kyllä' : 'Ei'}</td>
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

        function createAccessoryRow(data = { nimi: '', maara: 0, hintatekija: 0 }) {
            const row = document.createElement('div');
            row.className = 'details-row accessory-row';
            row.innerHTML = `
                <select id="accessory-item">
                    <option value="">Valitse tarvike</option>
                </select>
                <input type="number" class="accessory-quantity" min="0" step="1" value="${Number(data.maara).toFixed(0)}">
                <input type="number" class="accessory-factor" min="0" step="1" value="${data.hintatekija ? ((1 - data.hintatekija) * 100).toFixed(0) : 0}">
                <button type="button" class="button button--ghost" onclick="removeRow(this)">Poista</button>
            `;

            const select = row.querySelector('#accessory-item');
            populateAccessoryDropdown(select, data.nimi);

            return row;
        } 

        function createWorkRow(data = { nimi: '', tyomaara_tunneilla: 1, hintatekija: 0 }) {
            const row = document.createElement('div');
            row.className = 'details-row work-row';
            
            row.innerHTML = `
                <select class="work-type" style="flex: 1;">
                    <option value="">Valitse työ</option>
                </select>
                <input type="number" class="work-quantity hourly-field" placeholder="Määrä" 
                    style="display: ${isUrakka ? 'none' : 'block'}; flex: 1;" 
                    value="${Number(data.tyomaara_tunneilla || 0)}">
                <input type="number" class="work-factor hourly-field" placeholder="Alennus %" 
                    style="display: ${isUrakka ? 'none' : 'block'}; flex: 1;" 
                    value="${data.hintatekija ? ((1 - data.hintatekija) * 100).toFixed(0) : 0}">
                <input type="number" class="work-price urakka-field" placeholder="Summa (ALV 0%)" 
                    style="display: ${isUrakka ? 'block' : 'none'}; flex: 1;" 
                    value="${data.urakka_hinta || 0}">
                <button type="button" class="button button--ghost" onclick="removeRow(this)">Poista</button>
            `;
            
            const select = row.querySelector('.work-type')
            const workTypeHint = isUrakka ? 'Urakka' : data.nimi;
            populateWorkDropdown(select, workTypeHint);

            return row;
        }

        function addAccessoryRow(data) {
            document.getElementById('accessoryRows').appendChild(createAccessoryRow(data));
        }

        function addWorkRow(data) {
            const containerId = isUrakka ? 'urakkaWorkRows' : 'workRows';
            document.getElementById(containerId).appendChild(createWorkRow(data));
        }

        function removeRow(button) {
            const row = button.closest('.details-row');
            const container = row.parentElement;
            const allRows = container.querySelectorAll('.details-row');

            if (allRows.length > 1) {
                row.remove();
            } else {
                const inputs = row.querySelectorAll('input');
                inputs.forEach(input => input.value = '');
                const select = row.querySelector('select');
                if (select) select.selectedIndex = 0;
            }
        }

        function populateAccessoryDropdown(select, selectedAccessory) {
            select.innerHTML = '<option value="">Valitse tarvike</option>';
            uniqueAccessories.forEach(accessory => {
                const option = document.createElement('option');
                option.value = accessory.tarvike_id;
                option.textContent = accessory.nimi;
                if (accessory.nimi === selectedAccessory) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function populateWorkDropdown(select, selectedWork) {
            select.innerHTML = '<option value="">Valitse työ</option>';
            let itemsToDisplay;
            
            if (selectedWork === 'Urakka') {
                itemsToDisplay = uniqueWorkTypes.filter(w => w.nimi === 'Urakka');
            } else {
                itemsToDisplay = uniqueWorkTypes.filter(w => w.nimi !== 'Urakka');
            }

            itemsToDisplay.forEach(workType => {
                const option = document.createElement('option');
                option.value = workType.suoritus_id;
                option.textContent = workType.nimi;

                if (workType.nimi === selectedWork) {
                    option.selected = true;
                }
                
                select.appendChild(option);
            });
        }

        function populateCustomerDropdown(selectedCustomer) {
            const select = document.getElementById('editCustomer');
            select.innerHTML = '<option value="">Valitse asiakas</option>';
            customers.forEach(customer => {
                const option = document.createElement('option');
                option.value = customer.asiakas_id;
                option.textContent = customer.nimi;
                if (customer.nimi === selectedCustomer) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function populateLocationDropdown(selectedLocation) {
            const select = document.getElementById('editLocation');
            select.innerHTML = '<option value="">Valitse kohde</option>';
            locations.forEach(location => {
                const option = document.createElement('option');
                option.value = location.kohde_id;
                option.textContent = location.nimi;
                if (location.nimi === selectedLocation) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('agreementInfoView').classList.toggle('hidden', editMode);
            document.getElementById('agreementInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('agreementAccessoriesView').classList.toggle('hidden', editMode);
            document.getElementById('agreementWorkView').classList.toggle('hidden', editMode);
            document.getElementById('agreementUrakkaWorkView').classList.toggle('hidden', editMode);
            document.getElementById('accessoryPanel').classList.toggle('hidden', !editMode);
            document.getElementById('workPanel').classList.toggle('hidden', !editMode);
            document.getElementById('editPanelWrapper').classList.toggle('hidden', !editMode);
            document.getElementById('createInvoiceBtn').classList.toggle('hidden', editMode);
            document.getElementById('saveAgreementBtn').style.display = editMode ? 'inline-flex' : 'none';
            document.getElementById('deleteAgreementBtn').style.display = (editMode && activeAgreementId) ? 'inline-flex' : 'none';
            document.getElementById('createInvoicePanel').classList.add('hidden');
        }

        function onLocationSelect(kohdeId) {
            const info = document.getElementById('locationInfo');
            if (!kohdeId) {
                info.classList.add('hidden');
                return;
            }

            const location = locations.find(l => l.kohde_id == kohdeId);
            if (!location) return;

            const customer = customers.find(c => c.asiakas_id == location.asiakas_id);

            document.getElementById('locationCustomerName').textContent = customer ? customer.nimi : '-';
            document.getElementById('locationAddress').textContent = location.osoite || '-';
            info.classList.remove('hidden');
        }

        // Poistaa mahdollisuuden lisätä työrivejä jos työtyyppi on urakka ja muokkaa otsikoita.
        function toggleWorkRow(selectedWork) {
            isUrakka = (selectedWork === 'Urakka');

            const urakkaWorkView = document.getElementById('agreementUrakkaWorkView');
            const workView = document.getElementById('agreementWorkView');
            const urakkaWorkPanel = document.getElementById('urakkaWorkPanel');
            const workPanel = document.getElementById('workPanel');

            workView.style.display = 'none';
            urakkaWorkView.style.display = 'none';
            urakkaWorkPanel.style.display = 'none';
            workPanel.style.display = 'none';

            if (isUrakka) {
                urakkaWorkView.style.display = 'block';
                if (editMode) urakkaWorkPanel.style.display = 'block';
            } else {
                workView.style.display = 'block';
                if (editMode) workPanel.style.display = 'block';
            }
            ensureAtLeastOneWorkRow();
            refreshWorkDropdowns(selectedWork);
        }

        function refreshWorkDropdowns(type) {
            const rows = document.querySelectorAll('#workRows .work-row');

            rows.forEach(row => {
                const select = row.querySelector('.work-type');
                const currentValue = select.value;

                const currentText = select.options[select.selectedIndex]?.text;

                populateWorkDropdown(select, type === 'Urakka' ? 'Urakka' : currentText);

                select.value = "";
            });
        }

        function ensureAtLeastOneWorkRow() {
            const containerId = isUrakka ? 'urakkaWorkRows' : 'workRows';
            const container = document.getElementById(containerId);

            if (!container) return;

            if (container.children.length === 0) {
                addWorkRow();
            }
        }

        function createInvoice() {
            const agreement = agreements.find(a => a.sopimus_id == activeAgreementId);
            const n = parseInt(agreement?.osia_laskussa) || 1;
            const today = new Date().toISOString().split('T')[0];
            const due = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

            const panel = document.getElementById('createInvoicePanel');
            let html = `<h3>Luo lasku${n > 1 ? ` (${n} osaa)` : ''}</h3>`;

            for (let i = 0; i < n; i++) {
                const label = n > 1 ? ` — Osa ${i + 1}/${n}` : '';
                html += `
                    <div class="invoice-part">
                        ${n > 1 ? `<strong style="display:block;margin-bottom:8px;">Lasku${label}</strong>` : ''}
                        <div class="details-row">
                            <label>Laskutuspäivämäärä</label>
                            <input type="date" class="invoice-pvm" value="${today}">
                        </div>
                        <div class="details-row">
                            <label>Eräpäivämäärä</label>
                            <input type="date" class="invoice-erapaiva" value="${due}">
                        </div>
                    </div>
                `;
            }

            html += `
                <div class="location-form-actions">
                    <button class="button button--primary" onclick="confirmCreateInvoice()">Luo lasku</button>
                    <button class="button button--ghost" onclick="cancelCreateInvoice()">Peruuta</button>
                </div>
            `;

            panel.innerHTML = html;
            panel.classList.remove('hidden');
        }

        function cancelCreateInvoice() {
            document.getElementById('createInvoicePanel').classList.add('hidden');
        }

        async function confirmCreateInvoice() {
            const agreement = agreements.find(a => a.sopimus_id == activeAgreementId);
            const n = parseInt(agreement?.osia_laskussa) || 1;
            const osuus = 1 / n;

            const parts = document.querySelectorAll('#createInvoicePanel .invoice-part');
            for (const part of parts) {
                if (!part.querySelector('.invoice-pvm').value || !part.querySelector('.invoice-erapaiva').value) {
                    alert('Täytä kaikki päivämäärät.');
                    return;
                }
            }

            try {
                for (const part of parts) {
                    const pvm = part.querySelector('.invoice-pvm').value;
                    const erapaiva = part.querySelector('.invoice-erapaiva').value;
                    const response = await fetch('methods/laskut_methods.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ sopimus_id: parseInt(activeAgreementId), pvm, erapaiva, osuus, real_method: 'POST' })
                    });
                    const result = await response.json();
                    if (!result.success) {
                        alert('Laskun luonti epäonnistui.');
                        return;
                    }
                }
                window.location.href = 'laskut.php';
            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe palvelimeen.');
            }
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            document.getElementById('createInvoicePanel').classList.add('hidden');
            activeAgreementId = null;
        }

        function resetForm() {
            document.getElementById('workRows').innerHTML = '';
            document.getElementById('urakkaWorkRows').innerHTML = '';
            document.getElementById('accessoryRows').innerHTML = '';

            document.getElementById('editType').value = 'Urakka';
            document.getElementById('editInstallments').value = '';

            isUrakka = true;

            document.getElementById('editLocation').value = '';
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
            const containerId = isUrakka ? 'urakkaWorkRows' : 'workRows';
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (!items || items.length === 0) {
                addWorkRow();
                return;
            }
            items.forEach(item => addWorkRow(item));
            ensureAtLeastOneWorkRow();
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
            const agreement = agreements.find(item => item.sopimus_id == id);
            if (!agreement) return;
            activeAgreementId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Sopimus: ${agreement.asiakas_nimi}`;
            document.getElementById('viewType').textContent = agreement.tyyppi;
            toggleWorkRow(agreement.tyyppi);
            document.getElementById('viewInstallments').textContent = agreement.osia_laskussa;
            document.getElementById('viewCreated').textContent = new Date(agreement.luotu).toLocaleString('fi-FI');
            document.getElementById('viewUpdated').textContent = new Date(agreement.muokattu || agreement.luotu).toLocaleString('fi-FI');
            document.getElementById('viewLocation').textContent = agreement.kohde_nimi;
            document.getElementById('viewCustomer').textContent = agreement.asiakas_nimi;
            document.getElementById('viewAmount').textContent = formatCurrency(agreement.kokonaishinta);
            document.getElementById('viewAmountALV').textContent = formatCurrency(agreement.alv) 
                                                                                  + ' -> Yhteensä: '
                                                                                  + formatCurrency(
                                                                                    Number(agreement.kokonaishinta) 
                                                                                    + Number(agreement.alv));
            document.getElementById('viewBilled').textContent = Number(agreement.laskutettu) ? 'Kyllä' : 'Ei';

            const agreementAccessories = accessories.filter(a => a.sopimus_id == agreement.sopimus_id);
            const agreementWork = work.filter(w => w.sopimus_id == agreement.sopimus_id);

            renderViewList('viewAccessoriesList', agreementAccessories, item => `
                <span>${item.nimi}</span>
                <span>${Number(item.maara).toFixed(0)} ${item.yksikko === 'metri' ? 'm' : (item.yksikko || 'kpl')}</span>
                <span>${
                    item.hintatekija == 1
                        ? 'Ei alennusta'
                        : item.hintatekija !== undefined && item.hintatekija !== null
                            ? ((1 - item.hintatekija) * 100).toFixed(0) + ' %'
                            : ''
                }</span>
                <span>${formatCurrency(item.hankintahinta)}</span>
                <span>${formatCurrency(item.myyntihinta)}</span>
                <span>${formatCurrency(Number(item.myyntihinta) * Number(item.hintatekija) * Number(item.maara))}</span>
                <span>${formatCurrency(Number(item.myyntihinta) * Number(item.hintatekija) * Number(item.maara) * (Number(item.alv) - 1))}</span>
                <span>${formatCurrency(Number(item.myyntihinta) * Number(item.hintatekija) * Number(item.maara) * Number(item.alv))}</span>
            `);
            if (agreement.tyyppi !== 'Urakka') {
                renderViewList('viewWorkList',  agreementWork, item => {
                    let unit = '';
                    let amount = (item.tyomaara_tunneilla || 0);
                    unit = ' h';

                    return`
                        <span>${item.nimi}</span>
                        <span>${Number(amount).toFixed(0)}${unit}</span>
                        <span>${
                            item.hintatekija == 1
                                ? 'Ei alennusta'
                                : item.hintatekija !== undefined && item.hintatekija !== null
                                    ? ((1 - item.hintatekija) * 100).toFixed(0) + ' %'
                                    : ''
                        }</span>
                        <span>${formatCurrency(Number(item.hinta) / 1.24 * Number(amount))}</span>
                        <span>${formatCurrency(Number(item.hinta) / 1.24 * Number(amount) * Number(item.hintatekija))}</span>
                        <span>${formatCurrency(Number(item.hinta) / 1.24 * Number(amount) * Number(item.hintatekija) * 0.24)}</span>
                        <span>${formatCurrency(Number(item.hinta) / 1.24 * Number(amount) * Number(item.hintatekija) * 1.24)}</span>
                    `
                });
            } else {
                renderViewList('viewUrakkaWorkList',  agreementWork, item => {
                    return`
                        <span>${item.nimi}</span>
                        <span>${formatCurrency(item.urakka_hinta)}</span>
                        <span>${formatCurrency(Number(item.urakka_hinta) * 0.24)}</span>
                        <span>${formatCurrency(Number(item.urakka_hinta) * 0.24 + Number(item.urakka_hinta))}</span>
                `});
            }
        }

        function editAgreement(id) {
            resetForm();
            const agreement = agreements.find(item => item.sopimus_id == id);
            if (!agreement) return;
            activeAgreementId = id;
            isUrakka = (agreement.tyyppi === 'Urakka');
            switchToDetailsView('edit');
            toggleWorkRow(agreement.tyyppi);
            document.getElementById('detailsTitle').textContent = `Muokkaa sopimusta: ${agreement.asiakas_nimi}`;
            document.getElementById('agreementPrompt').textContent = 'Muokkaa sopimusta';
            document.getElementById('editType').value = agreement.tyyppi ?? '';
            document.getElementById('editInstallments').value = agreement.osia_laskussa ?? 1;
            document.getElementById('viewLocationEdit').classList.remove('hidden');
            document.getElementById('editLocation').classList.add('hidden');
            document.getElementById('viewLocationEdit').textContent = agreement.kohde_nimi;
            document.getElementById('customerEditRow').classList.remove('hidden');
            document.getElementById('viewCustomerEdit').textContent = agreement.asiakas_nimi;

            const agreementAccessories = accessories.filter(a => a.sopimus_id == agreement.sopimus_id) || [];
            const agreementWork = work.filter(w => w.sopimus_id == agreement.sopimus_id) || [];
            
            populateAccessoryRows(agreementAccessories);
            populateWorkRows(agreementWork);
        }

        function addAgreement() {
            resetForm();
            activeAgreementId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Uusi sopimus';
            document.getElementById('agreementPrompt').textContent = 'Luo uusi sopimus';
            document.getElementById('editLocation').classList.remove('hidden');
            document.getElementById('viewLocationEdit').classList.add('hidden');
            document.getElementById('customerEditRow').classList.add('hidden');
            document.getElementById('editType').value = 'Urakka';
            document.getElementById('editInstallments').value = 1;
            populateLocationDropdown('');
            toggleWorkRow('Urakka');
        }

        async function deleteAgreement() {
            if (!activeAgreementId) return;
            const agreement = agreements.find(item => item.sopimus_id == activeAgreementId);
            if (!confirm(`Haluatko varmasti poistaa sopimuksen (${agreement?.asiakas_nimi})? Kaikki sopimuksen laskut poistetaan.`)) return;
            try {
                const response = await fetch('methods/sopimukset_methods.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sopimus_id: activeAgreementId, real_method: 'DELETE' })
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Poisto epäonnistui.');
                }
            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe palvelimeen.');
            }
        }

        async function saveAgreement() {
            const type = document.getElementById('editType').value;
            const installments = parseInt(document.getElementById('editInstallments').value, 10);
            let locationId, customerId;

            if (activeAgreementId) {
                const agreement = agreements.find(a => a.sopimus_id == activeAgreementId);
                locationId = agreement.kohde_id;
            } else {
                locationId = document.getElementById('editLocation').value;
                const location = locations.find(l => l.kohde_id == locationId);
                customerId = location ? location.asiakas_id : null;
            }

            if (!locationId || isNaN(installments)) {
                alert('Täytä pakolliset kentät: Asiakas / Kohde ja Osia laskussa.');
                return;
            }

            const accessoriesData = Array.from(document.querySelectorAll('#accessoryRows .accessory-row')).map(row => {
                const select = row.querySelector('select');
                return {
                    tarvike_id: select.value,
                    maara: row.querySelector('.accessory-quantity').value,
                    alennus: row.querySelector('.accessory-factor').value 
                };
            }).filter(item => item.tarvike_id !== "" && item.maara > 0);

            const containerId = type === 'Urakka' ? '#urakkaWorkRows' : '#workRows';
            const workData = Array.from(document.querySelectorAll(`${containerId} .work-row`)).map(row => {
                const select = row.querySelector('.work-type');
                const isUrakka2 = type === 'Urakka';

                return {
                    suoritus_id: select.value,
                    maara: isUrakka2 ? 0 : row.querySelector('.work-quantity').value,
                    alennus: isUrakka2 ? 0 : row.querySelector('.work-factor').value,
                    urakka_hinta: isUrakka2 ? row.querySelector('.work-price').value : 0
                };
            }).filter(item => {
                if (item.suoritus_id === "") return false;
                if (type === 'Urakka') return item.urakka_hinta > 0;
                return item.maara > 0;
            });

            const payload = {
                sopimus_id: activeAgreementId,
                tyyppi: type,
                osia_laskussa: installments,
                kohde_id: locationId,
                asiakas_id: customerId,
                tarvikkeet: accessoriesData,
                tyot: workData
            };

            try {
                const response = await fetch('methods/sopimukset_methods.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ...payload
                    })
                });
                
                const text = await response.text();
                console.log(text);

                const result = JSON.parse(text);

                if (result.success) {
                    window.location.reload();
                } else if (result.varastovirhe) {
                    const lista = result.vajeet.map(v =>
                        `• ${v.nimi}: varastossa ${v.varastossa}, vaadittu ${v.vaadittu}`
                    ).join('\n');
                    alert(`Varastossa ei ole tarpeeksi tarvikkeita:\n\n${lista}`);
                } else {
                    alert("Tallennus epäonnistui.");
                }
            } catch (e) {
                console.error("Virhe: ", e);
                alert("Yhteysvirhe palvelimeen.");
            }

            renderAgreementRows();
            backToMain();
        }

    </script>
    <script src="sort.js"></script>
    <script>makeSortable('agreementTable');</script>
</body>
</html>