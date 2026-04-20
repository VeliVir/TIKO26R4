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
                        <span>Yksikköhinta</span>
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
                            <h3>Muokkaa sopimusta</h3>
                            <div class="details-row">
                                <label>Työkohde</label>
                                    <span id="editLocation"></span>
                            </div>
                            <div class="details-row">
                                <label>Asiakas</label>
                                    <span id="editCustomer"></span>
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
            populateWorkDropdown(select, data.nimi);

            return row;
        }

        function addAccessoryRow(data) {
            document.getElementById('accessoryRows').appendChild(createAccessoryRow(data));
        }

        function addWorkRow(data) {
            document.getElementById('workRows').appendChild(createWorkRow(data));
        }

        function removeRow(button) {
            const row = button.closest('.details-row');
            const container = row.parentElement;
            const allRows = container.querySelectorAll('.details-row');

            if (allRows.length > 1) {
                row.remove();
            } else {
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.value = '';
                });
                
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
                toggleWorkRow('Urakka');
            } else {
                itemsToDisplay = uniqueWorkTypes.filter(w => w.nimi !== 'Urakka');
                toggleWorkRow('toimiiko');
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
        }

        // Poistaa mahdollisuuden lisätä työrivejä jos työtyyppi on urakka ja muokkaa otsikoita.
        function toggleWorkRow(selectedWork) {
            const addBtn = document.getElementById('addWorkRowBtn');
            const urakkaWorkView = document.getElementById('agreementUrakkaWorkView');
            const workView = document.getElementById('agreementWorkView');

            if (selectedWork === 'Urakka') {
                if (editMode) addBtn.style.display = 'none';
                workView.style.display = 'none';
                urakkaWorkView.style.display = 'block';
            } else {
                if (editMode) addBtn.style.display = 'block';
                workView.style.display = 'block';
                urakkaWorkView.style.display = 'none';
            }
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
            const agreement = agreements.find(item => item.sopimus_id == id);
            if (!agreement) return;
            activeAgreementId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Sopimus: ${agreement.asiakas_nimi}`;
            document.getElementById('viewType').textContent = agreement.tyyppi;
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
                <span>${Number(item.maara).toFixed(0)} ${item.yksikko}${item.yksikko === 'metri' ? 'ä' : ''}</span>
                <span>${
                    item.hintatekija == 1
                        ? 'Ei alennusta'
                        : item.hintatekija !== undefined && item.hintatekija !== null
                            ? ((1 - item.hintatekija) * 100).toFixed(0) + ' %'
                            : ''
                }</span>
                <span>${formatCurrency(item.myyntihinta)}</span>
                <span>${formatCurrency(Number(item.myyntihinta) * Number(item.hintatekija) * Number(item.maara))}</span>
                <span>${formatCurrency(Number(item.myyntihinta) * Number(item.hintatekija) * Number(item.maara) * 0.24)}</span>
                <span>${formatCurrency(Number(item.myyntihinta) * Number(item.hintatekija) * Number(item.maara) * 1.24)}</span>
            `);
            if (agreement.tyyppi !== 'Urakka') {
                renderViewList('viewWorkList',  agreementWork, item => {
                    toggleWorkRow(item.nimi);
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
                    toggleWorkRow(item.nimi);
                    return`
                        <span>${item.nimi}</span>
                        <span>${formatCurrency(item.urakka_hinta)}</span>
                        <span>${formatCurrency(Number(item.urakka_hinta) * 0.24)}</span>
                        <span>${formatCurrency(Number(item.urakka_hinta) * 0.24 + Number(item.urakka_hinta))}</span>
                `});
            }
        }

        function editAgreement(id) {
            const agreement = agreements.find(item => item.sopimus_id == id);
            if (!agreement) return;
            activeAgreementId = id;
            isUrakka = (agreement.tyyppi === 'Urakka');
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa sopimusta: ${agreement.asiakas_nimi}`;
            document.getElementById('editType').value = agreement.tyyppi ?? '';
            document.getElementById('editInstallments').value = agreement.osia_laskussa ?? 1;
            document.getElementById('editLocation').textContent = agreement.kohde_nimi;
            document.getElementById('editCustomer').textContent = agreement.asiakas_nimi;

            const agreementAccessories = accessories.filter(a => a.sopimus_id == agreement.sopimus_id) || [];
            const agreementWork = work.filter(w => w.sopimus_id == agreement.sopimus_id) || [];
            
            populateAccessoryRows(agreementAccessories);
            populateWorkRows(agreementWork);
        }

        function addAgreement() {
            activeAgreementId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Uusi sopimus';
            document.getElementById('editType').value = 'Valitse sopimustyyli';
            document.getElementById('editInstallments').value = 0;
            populateCustomerDropdown('');
            populateLocationDropdown('');
            populateAccessoryRows([]);
            populateWorkRows([]);
        }

        async function saveAgreement() {
            const type = document.getElementById('editType').value;
            const installments = parseInt(document.getElementById('editInstallments').value, 10);
            const locationId = document.getElementById('editLocation').value;
            const customerId = document.getElementById('editCustomer').value;

            if (!locationId || !customerId || isNaN(installments)) {
                alert('Täytä pakolliset kentät: Asiakas, Kohde ja Osia laskussa.');
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

            const workData = Array.from(document.querySelectorAll('#workRows .work-row')).map(row => {
                const select = row.querySelector('.work-type');
                const isUrakka2 = select.options[select.selectedIndex].text === 'Urakka';
                
                return {
                    suoritus_id: select.value,
                    // Jos urakka, lähetetään hinta, muuten määrät
                    maara: isUrakka2 ? 0 : row.querySelector('.work-quantity').value,
                    alennus: isUrakka2 ? 0 : row.querySelector('.work-factor').value,
                    urakka_hinta: isUrakka2 ? row.querySelector('.work-price').value : 0
                };
            }).filter(item => {
                if (item.suoritus_id === "") return false;
                
                if (item.isUrakka2) {
                    return item.urakka_hinta > 0;
                } else {
                    return item.maara > 0;
                }
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

                //const result = await response.json(); 
                const text = await response.text(); 
                console.log("Palvelimen vastaus:", text); 

                // Jatka vasta sitten:
                const result = JSON.parse(text);
                if (result.success) {
                    window.location.reload();
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