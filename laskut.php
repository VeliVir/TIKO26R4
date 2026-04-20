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
                <button type="button" class="button button--primary" onclick="addInvoice()">Lisää uusi lasku</button>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button class="button button--primary" onclick="createReminders()">Luo muistutuslaskut</button>
                    <span id="creation-message" style="font-weight: bold; color: red; font-size: 1.1rem; min-width: 18rem;"></span>
                </div>
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
                <div class="details-card" id="invoiceContainer">
                    <div class="details-card" id="invoiceInfoView">
                        <h3>Tiedot</h3>
                        <div class="details-row"><span>Asiakas</span><span id="viewCustomer"></span></div>
                        <div class="details-row"><span>Laskutusosoite</span><span id="viewAddress"></span></div>
                        <div class="details-row"><span>Maksettu</span><span id="viewPaid"></span></div>
                        <div class="details-row"><span>Laskutuspäivämäärä</span><span id="viewBillingDate"></span></div>
                        <div class="details-row"><span>Eräpäivämäärä</span><span id="viewDueDate"></span></div>
                    </div>

                    <div class="details-card" id="invoiceDetailsView">
                        <h3>Laskun tiedot</h3>
                        <div class="details-row"><span>Laskun numero</span><span id="viewInvoiceNumber"></span></div>
                        <div class="details-row"><span>Tyyppi</span><span id="viewInvoiceType"></span></div>
                    </div>

                    <div class="details-card" id="itemsView">
                        <h3>Tarvikkeet</h3>
                        <div id="itemsList"></div>
                    </div>

                    <div class="details-card" id="workView">
                        <h3>Työ</h3>
                        <div id="workList"></div>
                    </div>

                    <div class="details-card" id="pricingView">
                        <h3>Hintaerittely</h3>
                        <div class="details-row"><span>Perussumma</span><span id="viewBaseAmount"></span></div>
                        <div class="details-row"><span>Laskutuslisä</span><span id="viewLaskutuslisa"></span></div>
                        <div class="details-row"><span>Viivästyskorko</span><span id="viewViivastyskorko"></span></div>
                        <div class="details-row total-row"><span><strong>Yhteensä</strong></span><span id="viewTotal"></span></div>
                    </div>
                </div>

                <div class="details-card" id="invoiceInfoEdit">
                    <h3>Muokkaa laskua</h3>
                    <div class="details-row">
                        <label for="editSopimus">Sopimus</label>
                        <select id="editSopimus">
                            <option value="">Valitse sopimus</option>
                        </select>
                    </div>
                    <div class="details-row">
                        <label for="editAddress">Laskutusosoite</label>
                        <input type="text" id="editAddress" readonly>
                    </div>
                    <div class="details-row">
                        <label for="editPvm">Laskutuspäivämäärä</label>
                        <input type="date" id="editPvm">
                    </div>
                    <div class="details-row">
                        <label for="editErapaiva">Eräpäivämäärä</label>
                        <input type="date" id="editErapaiva">
                    </div>
                    <div class="details-row">
                        <label for="editMaksupaiva">Maksupäivä</label>
                        <input type="date" id="editMaksupaiva">
                    </div>
                    <div class="details-row">
                        <label for="editEdellinen">Edellinen lasku</label>
                        <select id="editEdellinen">
                            <option value="">Ei edeltävää</option>
                        </select>
                    </div>
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary" id="saveInvoiceBtn" onclick="saveInvoice()" style="display: none;">Tallenna</button>
                    <button class="button button--danger" id="deleteInvoiceBtn" onclick="deleteInvoice()" style="display: none;">Poista</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let invoices = [];
        let sopimukset = [];
        let details = [];
        let activeInvoiceId = null;
        let editMode = false;
        let disabledInvoiceIds = new Set();

        async function init() {
            try {
                const response = await fetch('methods/laskut_methods.php');
                const data = await response.json();

                if (!data.success) {
                    alert('Datan haku epäonnistui');
                    return;
                }

                invoices = data.invoices;
                sopimukset = data.sopimukset;
                details = data.details;

                computeDisabledInvoices();
                renderInvoiceRows();

            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe');
            }
        }

        function computeDisabledInvoices() {
            disabledInvoiceIds.clear();
            invoices.forEach(inv => {
                if (inv.paid) {
                    disabledInvoiceIds.add(inv.lasku_id);
                }
            });
            invoices.forEach(inv => {
                if (inv.paid) {
                    let currentId = inv.edellinen_lasku_id;
                    while (currentId) {
                        disabledInvoiceIds.add(currentId);
                        const prev = invoices.find(i => i.lasku_id == currentId);
                        currentId = prev ? prev.edellinen_lasku_id : null;
                    }
                }
            });
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

        function renderInvoiceRows() {
            const tbody = document.querySelector('#invoiceTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#invoiceFilter').value.toLowerCase();

            invoices
                .filter(invoice => invoice.asiakas_nimi.toLowerCase().includes(filter))
                .forEach(invoice => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${toFinnishDate(invoice.pvm)}</td>
                        <td>${toFinnishDate(invoice.erapaiva)}</td>
                        <td>${invoice.asiakas_nimi}</td>
                        <td>${formatCurrency(invoice.pricing.total)}</td>
                        <td>${invoice.paid ? 'Kyllä' : 'Ei'}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showInvoice(${invoice.lasku_id})">Näytä</button>
                            ${disabledInvoiceIds.has(invoice.lasku_id) ? '' : `<button class="button button--ghost" onclick="editInvoice(${invoice.lasku_id})">Muokkaa</button>`}
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
            document.getElementById('invoiceInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveInvoiceBtn').style.display = editMode ? 'inline-flex' : 'none';
            document.getElementById('deleteInvoiceBtn').style.display = (editMode && activeInvoiceId) ? 'inline-flex' : 'none';
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeInvoiceId = null;
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function showInvoice(id) {
            const invoice = invoices.find(item => Number(item.lasku_id) === Number(id));
            if (!invoice) return;
            activeInvoiceId = id;
            switchToDetailsView('view');
            
            document.getElementById('detailsTitle').textContent = `Lasku: ${invoice.asiakas_nimi}`;
            
            // Tiedot card
            document.getElementById('viewCustomer').textContent = invoice.asiakas_nimi;
            document.getElementById('viewAddress').textContent = invoice.asiakas_osoite || '-';
            document.getElementById('viewPaid').textContent = invoice.paid ? 'Kyllä' : 'Ei';
            document.getElementById('viewBillingDate').textContent = toFinnishDate(invoice.pvm);
            document.getElementById('viewDueDate').textContent = toFinnishDate(invoice.erapaiva);
            
            // Laskun tiedot card
            document.getElementById('viewInvoiceNumber').textContent = invoice.invoice_number;
            document.getElementById('viewInvoiceType').textContent = invoice.invoice_type;
            
            // Tarvikkeet card
            const itemsList = document.getElementById('itemsList');
            itemsList.innerHTML = '';
            if (details[id] && details[id].items && details[id].items.length > 0) {
                details[id].items.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'details-row';
                    itemDiv.innerHTML = `
                        <span>${item.tarvike_nimi} (${item.maara} kpl × ${formatCurrency(item.hankintahinta)} × ${item.hintatekija})</span>
                        <span>${formatCurrency(item.kokonaishinta)}</span>
                    `;
                    itemsList.appendChild(itemDiv);
                });
            } else {
                itemsList.innerHTML = '<div class="details-row"><span>Ei tarvikkeita</span><span>-</span></div>';
            }
            
            // Työ card
            const workList = document.getElementById('workList');
            workList.innerHTML = '';
            if (details[id] && details[id].work && details[id].work.length > 0) {
                details[id].work.forEach(work => {
                    const workDiv = document.createElement('div');
                    workDiv.className = 'details-row';
                    const description = work.urakka_hinta 
                        ? `${work.suoritus_nimi} (urakka × ${work.hintatekija})`
                        : `${work.suoritus_nimi} (${work.tyomaara_tunneilla} h × ${formatCurrency(work.tuntiveloitus)} × ${work.hintatekija})`;
                    workDiv.innerHTML = `
                        <span>${description}</span>
                        <span>${formatCurrency(work.kokonaishinta)}</span>
                    `;
                    workList.appendChild(workDiv);
                });
            } else {
                workList.innerHTML = '<div class="details-row"><span>Ei työtä</span><span>-</span></div>';
            }
            
            // Hintaerittely card
            const pricing = invoice.pricing;
            document.getElementById('viewBaseAmount').textContent = formatCurrency(pricing.base_amount);
            document.getElementById('viewLaskutuslisa').textContent = formatCurrency(pricing.laskutuslisa);
            document.getElementById('viewViivastyskorko').textContent = formatCurrency(pricing.viivastyskorko);
            document.getElementById('viewTotal').textContent = formatCurrency(pricing.total);
            
            populateSopimuksetDropdown('');
            populatePreviousInvoicesDropdown('');
        }

        function editInvoice(id) {
            const invoice = invoices.find(item => Number(item.lasku_id) === Number(id));
            if (!invoice || disabledInvoiceIds.has(id)) return;
            activeInvoiceId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa laskua: ${invoice.asiakas_nimi}`;
            
            const sopimus = sopimukset.find(s => s.sopimus_id == invoice.sopimus_id);
            document.getElementById('editAddress').value = invoice.asiakas_osoite || '';
            document.getElementById('editPvm').value = invoice.pvm;
            document.getElementById('editErapaiva').value = invoice.erapaiva;
            document.getElementById('editMaksupaiva').value = invoice.maksupaiva || '';
            
            populateSopimuksetDropdown(invoice.sopimus_id);
            populatePreviousInvoicesDropdown(invoice.edellinen_lasku_id);
        }

        function addInvoice() {
            activeInvoiceId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi lasku';
            document.getElementById('editAddress').value = '';
            document.getElementById('editPvm').value = '';
            document.getElementById('editErapaiva').value = '';
            document.getElementById('editMaksupaiva').value = '';
            populateSopimuksetDropdown('');
            populatePreviousInvoicesDropdown('');
        }

        function populateSopimuksetDropdown(selectedId) {
            const select = document.getElementById('editSopimus');
            select.innerHTML = '<option value="">Valitse sopimus</option>';
            sopimukset.forEach(s => {
                const option = document.createElement('option');
                option.value = s.sopimus_id;
                option.textContent = `${s.asiakas_nimi} - ${s.kohde_nimi}`;
                if (Number(s.sopimus_id) === Number(selectedId)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function populatePreviousInvoicesDropdown(selectedId) {
            const select = document.getElementById('editEdellinen');
            select.innerHTML = '<option value="">Ei edeltävää</option>';
            invoices.forEach(inv => {
                const option = document.createElement('option');
                option.value = inv.lasku_id;
                option.textContent = `${toFinnishDate(inv.pvm)} - ${inv.asiakas_nimi}`;
                if (Number(inv.lasku_id) === Number(selectedId)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        async function postInvoice(payload, method) {
            const response = await fetch('methods/laskut_methods.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...payload, real_method: method })
            });
            return await response.json();
        }

        async function deleteInvoice() {
            if (!activeInvoiceId) return;
            const invoice = invoices.find(item => Number(item.lasku_id) === Number(activeInvoiceId));
            if (!confirm(`Haluatko varmasti poistaa laskun (${invoice?.asiakas_nimi})?`)) return;
            try {
                const result = await postInvoice({ lasku_id: activeInvoiceId }, 'DELETE');
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Poisto epäonnistui.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function saveInvoice() {
            const sopimusId = document.getElementById('editSopimus').value;
            const pvm = document.getElementById('editPvm').value;
            const erapaiva = document.getElementById('editErapaiva').value;
            const maksupaiva = document.getElementById('editMaksupaiva').value;
            const edellinen = document.getElementById('editEdellinen').value;

            if (!sopimusId || !pvm || !erapaiva) {
                alert('Täytä kaikki pakolliset kentät ennen tallentamista.');
                return;
            }

            const payload = {
                sopimus_id: parseInt(sopimusId),
                pvm: pvm,
                erapaiva: erapaiva,
                maksupaiva: maksupaiva || null,
                edellinen_lasku_id: edellinen ? parseInt(edellinen) : null
            };

            const method = activeInvoiceId ? 'PUT' : 'POST';
            if (activeInvoiceId) {
                payload.lasku_id = activeInvoiceId;
            }

            try {
                const result = await postInvoice(payload, method);
                if (result.success) {
                    window.location.reload();
                } else {
                    alert("Tallennus epäonnistui.");
                }
            } catch (e) {
                console.error("Virhe: ", e);
            }

            renderInvoiceRows();
            backToMain();
        }

        async function createReminders() {
            let amountCreated = 0;
            const invoicesWithFollowUp = new Set(
                invoices.map(inv => inv.edellinen_lasku_id).filter(id => id != null)
            );

            for (const inv of invoices) {
                const isChainTail = !invoicesWithFollowUp.has(inv.lasku_id);
                if (!inv.paid && new Date(inv.erapaiva) < new Date() && isChainTail) {
                    const payload = {
                        sopimus_id: parseInt(inv.sopimus_id),
                        pvm: new Date().toISOString().split('T')[0],
                        erapaiva: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                        edellinen_lasku_id: parseInt(inv.lasku_id)
                    };

                    try {
                        const result = await postInvoice(payload, 'POST');
                        if (result.success) {
                            amountCreated++;
                        } else {
                            alert("Tallennus epäonnistui.");
                        }
                    } catch (e) {
                        console.error("Virhe: ", e);
                    }
                }
            }

            await init();

            const msg = document.getElementById("creation-message");
            msg.textContent = `${amountCreated} muistutuslaskua luotu.`;
            setTimeout(function(){
                msg.textContent = '';
            }, 5000);
        }
    </script>
    <script src="sort.js"></script>
    <script>makeSortable('invoiceTable');</script>
</body>
</html>
