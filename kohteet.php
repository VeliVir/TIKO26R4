<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kohteet - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <!-- Main List View -->
        <div id="mainView">
            <header class="page-header">
                <h1>Kohteet</h1>
            </header>

            <div class="top-actions">
                <button type="button" class="button button--primary" onclick="addLocation()">Lisää uusi kohde</button>
                <div class="filter-field">
                    <label for="locationFilter">Suodata</label>
                    <input type="text" id="locationFilter" placeholder="Etsi kohteen nimellä" oninput="filterLocations()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="locationTable">
                    <thead>
                        <tr>
                            <th>Nimi</th>
                            <th>Osoite</th>
                            <th>Asiakas</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Details View -->
        <div id="detailsView" class="hidden">
            <div class="details-view-header">
                <button class="button button--ghost back-button" onclick="backToMain()">← Takaisin</button>
                <h1 id="detailsTitle">Kohde</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="locationInfoView">
                    <h3>Kohteen tiedot</h3>
                    <div class="details-row"><span>Nimi</span><span id="viewName"></span></div>
                    <div class="details-row"><span>Osoite</span><span id="viewAddress"></span></div>
                    <div class="details-row"><span>Asiakas</span><span id="viewCustomer"></span></div>
                </div>

                <div class="details-card" id="locationInfoEdit">
                    <h3>Muokkaa kohdetta</h3>
                    <div class="details-row"><label for="editName">Nimi</label><input type="text" id="editName"></div>
                    <div class="details-row"><label for="editAddress">Osoite</label><input type="text" id="editAddress"></div>
                    <div class="details-row">
                        <label for="editCustomer">Asiakas</label>
                        <select id="editCustomer">
                            <option value="">Valitse asiakas</option>
                        </select>
                    </div>
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary" id="saveLocationBtn" onclick="saveLocation()">Tallenna</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let customers = [];
        let locations = [];

        async function init() {
            try {
                const response = await fetch('methods/kohteet_methods.php');
                const data = await response.json();

                if (!data.success) {
                    alert('Datan haku epäonnistui');
                    return;
                }

                customers = data.customers;
                locations = data.locations;

                renderLocationRows();

            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe');
            }
        }

        init();

        let activeLocationId = null;
        let editMode = false;

        function renderLocationRows() {
            const tbody = document.querySelector('#locationTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#locationFilter').value.toLowerCase();

            locations
                .filter(location => location.nimi.toLowerCase().includes(filter))
                .forEach(location => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${location.nimi}</td>
                        <td>${location.osoite}</td>
                        <td>${location.asiakas_nimi}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showLocation(${location.kohde_id})">Näytä</button>
                            <button class="button button--ghost" onclick="editLocation(${location.kohde_id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterLocations() {
            renderLocationRows();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('locationInfoView').classList.toggle('hidden', editMode);
            document.getElementById('locationInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveLocationBtn').style.display = editMode ? 'inline-flex' : 'none';
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeLocationId = null;
            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function showLocation(id) {
            const location = locations.find(item => Number(item.kohde_id) === Number(id));
            if (!location) return;
            activeLocationId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Kohde: ${location.nimi}`;
            document.getElementById('viewName').textContent = location.nimi;
            document.getElementById('viewAddress').textContent = location.osoite;
            document.getElementById('viewCustomer').textContent = location.asiakas_nimi;
            document.getElementById('editName').value = location.nimi;
            document.getElementById('editAddress').value = location.osoite;
            populateCustomerDropdown(location.asiakas_nimi);
        }

        function editLocation(id) {
            const location = locations.find(item => Number(item.kohde_id) === Number(id));
            if (!location) return;
            activeLocationId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa kohdetta: ${location.nimi}`;
            document.getElementById('editName').value = location.nimi;
            document.getElementById('editAddress').value = location.osoite;
            populateCustomerDropdown(location.asiakas_nimi);
        }

        function addLocation() {
            activeLocationId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi kohde';
            document.getElementById('editName').value = '';
            document.getElementById('editAddress').value = '';
            populateCustomerDropdown('');
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

        async function saveLocation() {
            const name = document.getElementById('editName').value.trim();
            const address = document.getElementById('editAddress').value.trim();
            const customerId = document.getElementById('editCustomer').value;

            if (!name || !address || !customerId) {
                alert('Täytä kaikki kentät ennen tallentamista.');
                return;
            }
            
            const payload = {
                nimi: name,
                osoite: address,
                asiakas_id: parseInt(customerId)
            };

            // Valitaan muokataanko vai tehdäänkö uusi kohde
            const method = activeLocationId ? 'PUT' : 'POST';
            if (activeLocationId) {
                payload.kohde_id = activeLocationId;
            }

            try {
                // Käytetään molemmissa "virallisena" metodina POST, koska PUT antaa 403 forbidden virheen.
                // real_method lähettää oikean metodin.
                const response = await fetch('methods/kohteet_methods.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ...payload,
                        real_method: method // Kerrotaan PHP:lle oikea toiminto
                    })
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    alert("Tallennus epäonnistui.");
                }
            } catch (e) {
                console.error("Virhe: ", e);
                alert("Yhteysvirhe palvelimeen.");
            }

            renderLocationRows();
            backToMain();
        }

        // Check for URL parameters on page load
        function checkUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            const locationId = urlParams.get('location');
            if (locationId) {
                showLocation(parseInt(locationId));
            }
        }

        checkUrlParameters();
    </script>
</body>
</html>
