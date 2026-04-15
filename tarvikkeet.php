<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarvikkeet - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div id="mainView">
            <header class="page-header">
                <h1>Tarvikkeet</h1>
            </header>

            <div class="top-actions">
                <button class="button button--primary" onclick="addAccessory()">Lisää tarvikkeita</button>
                <div class="filter-field">
                    <label for="accessoryFilter">Suodata</label>
                    <input type="text" id="accessoryFilter" placeholder="Etsi tarvikkeen nimellä" oninput="filterAccessories()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="accessoryTable">
                    <thead>
                        <tr>
                            <th>Nimi</th>
                            <th>Merkki</th>
                            <th>Toimittaja</th>
                            <th>Varastossa</th>
                            <th>ALV</th>
                            <th>Kokonaishinta</th>
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
                <h1 id="detailsTitle">Tarvike</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="accessoryInfoView">
                    <h3>Tarvikkeen tiedot</h3>
                    <div class="details-row"><span>Nimi</span><span id="viewName"></span></div>
                    <div class="details-row"><span>Merkki</span><span id="viewBrand"></span></div>
                    <div class="details-row"><span>Toimittaja</span><span id="viewSupplier"></span></div>
                    <div class="details-row"><span>Varastossa</span><span id="viewStock"></span></div>
                    <div class="details-row"><span>ALV</span><span id="viewVat"></span></div>
                    <div class="details-row"><span>Hankintahinta</span><span id="viewPurchasePrice"></span></div>
                    <div class="details-row"><span>Myyntihinta</span><span id="viewSellingPrice"></span></div>
                    <div class="details-row"><span>ALV-osuus</span><span id="viewVatAmount"></span></div>
                    <div class="details-row"><span>Kokonaishinta</span><span id="viewTotalPrice"></span></div>
                </div>

                <div class="details-card" id="accessoryInfoEdit">
                    <h3>Muokkaa tarviketta</h3>
                    <div class="details-row"><label for="editName">Nimi</label><input type="text" id="editName"></div>
                    <div class="details-row"><label for="editBrand">Merkki</label><input type="text" id="editBrand"></div>
                    <div class="details-row"><label for="editSupplier">Toimittaja</label><input type="text" id="editSupplier"></div>
                    <div class="details-row"><label for="editStock">Varastossa</label><input type="number" id="editStock" min="0"></div>
                    <div class="details-row"><label for="editVat">ALV</label><select id="editVat">
                        <option value="10">10%</option>
                        <option value="24">24%</option>
                    </select></div>
                    <div class="details-row"><label for="editPurchasePrice">Hankintahinta</label><input type="number" id="editPurchasePrice" min="0" step="0.01"></div>
                    <div class="details-row"><label for="editSellingPrice">Myyntihinta</label><input type="number" id="editSellingPrice" min="0" step="0.01"></div>
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary" id="saveAccessoryBtn" onclick="saveAccessory()">Tallenna</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const accessories = [
            {
                id: 1,
                name: 'Ruuvimeisseli',
                brand: 'ProTool',
                supplier: 'Toimittaja Oy',
                stock: 24,
                vat: 24,
                purchasePrice: 5.50,
                sellingPrice: 8.90
            },
            {
                id: 2,
                name: 'Poranterä 10mm',
                brand: 'DrillPro',
                supplier: 'Rakennusmateriaali Ltd',
                stock: 120,
                vat: 10,
                purchasePrice: 2.80,
                sellingPrice: 3.90
            },
            {
                id: 3,
                name: 'Työkalupakki',
                brand: 'MasterBox',
                supplier: 'ToolHouse',
                stock: 18,
                vat: 24,
                purchasePrice: 35.00,
                sellingPrice: 48.00
            }
        ];

        let activeAccessoryId = null;
        let editMode = false;

        function calculateTotalPrice(accessory) {
            const vatAmount = accessory.sellingPrice * (accessory.vat / 100);
            return accessory.sellingPrice + vatAmount;
        }

        function formatCurrency(value) {
            return `${value.toFixed(2).replace('.', ',')} €`;
        }

        function calculateVatAmount(price, vatRate) {
            return price * (vatRate / 100);
        }

        function renderAccessoryRows() {
            const tbody = document.querySelector('#accessoryTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#accessoryFilter').value.toLowerCase();

            accessories
                .filter(item => item.name.toLowerCase().includes(filter))
                .forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.name}</td>
                        <td>${item.brand}</td>
                        <td>${item.supplier}</td>
                        <td>${item.stock}</td>
                        <td>${item.vat}%</td>
                        <td>${formatCurrency(calculateTotalPrice(item))}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showAccessory(${item.id})">Näytä</button>
                            <button class="button button--ghost" onclick="editAccessory(${item.id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterAccessories() {
            renderAccessoryRows();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('accessoryInfoView').classList.toggle('hidden', editMode);
            document.getElementById('accessoryInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveAccessoryBtn').style.display = editMode ? 'inline-flex' : 'none';
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeAccessoryId = null;
        }

        function showAccessory(id) {
            const accessory = accessories.find(item => item.id === id);
            if (!accessory) return;
            activeAccessoryId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Tarvike: ${accessory.name}`;
            document.getElementById('viewName').textContent = accessory.name;
            document.getElementById('viewBrand').textContent = accessory.brand;
            document.getElementById('viewSupplier').textContent = accessory.supplier;
            document.getElementById('viewStock').textContent = accessory.stock;
            document.getElementById('viewVat').textContent = `${accessory.vat}%`;
            document.getElementById('viewPurchasePrice').textContent = formatCurrency(accessory.purchasePrice);
            document.getElementById('viewSellingPrice').textContent = formatCurrency(accessory.sellingPrice);
            document.getElementById('viewVatAmount').textContent = formatCurrency(calculateVatAmount(accessory.sellingPrice, accessory.vat));
            document.getElementById('viewTotalPrice').textContent = formatCurrency(calculateTotalPrice(accessory));
        }

        function editAccessory(id) {
            const accessory = accessories.find(item => item.id === id);
            if (!accessory) return;
            activeAccessoryId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa tarviketta: ${accessory.name}`;
            document.getElementById('editName').value = accessory.name;
            document.getElementById('editBrand').value = accessory.brand;
            document.getElementById('editSupplier').value = accessory.supplier;
            document.getElementById('editStock').value = accessory.stock;
            document.getElementById('editVat').value = accessory.vat;
            document.getElementById('editPurchasePrice').value = accessory.purchasePrice;
            document.getElementById('editSellingPrice').value = accessory.sellingPrice;
            document.getElementById('viewVatAmount').textContent = formatCurrency(calculateVatAmount(accessory.sellingPrice, accessory.vat));
            document.getElementById('viewCalculatedTotal').textContent = formatCurrency(calculateTotalPrice(accessory));
        }

        function addAccessory() {
            activeAccessoryId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi tarvikkeita';
            document.getElementById('editName').value = '';
            document.getElementById('editBrand').value = '';
            document.getElementById('editSupplier').value = '';
            document.getElementById('editStock').value = 0;
            document.getElementById('editVat').value = 24;
            document.getElementById('editPurchasePrice').value = '';
            document.getElementById('editSellingPrice').value = '';
            document.getElementById('viewVatAmount').textContent = formatCurrency(0);
            document.getElementById('viewCalculatedTotal').textContent = formatCurrency(0);
        }

        function saveAccessory() {
            const name = document.getElementById('editName').value.trim();
            const brand = document.getElementById('editBrand').value.trim();
            const supplier = document.getElementById('editSupplier').value.trim();
            const stock = parseInt(document.getElementById('editStock').value, 10);
            const vat = parseInt(document.getElementById('editVat').value, 10);
            const purchasePrice = parseFloat(document.getElementById('editPurchasePrice').value);
            const sellingPrice = parseFloat(document.getElementById('editSellingPrice').value);

            if (!name || !brand || !supplier || isNaN(stock) || isNaN(purchasePrice) || isNaN(sellingPrice)) {
                alert('Täytä kaikki kentät oikein ennen tallentamista.');
                return;
            }

            if (activeAccessoryId) {
                const accessory = accessories.find(item => item.id === activeAccessoryId);
                if (!accessory) return;
                accessory.name = name;
                accessory.brand = brand;
                accessory.supplier = supplier;
                accessory.stock = stock;
                accessory.vat = vat;
                accessory.purchasePrice = purchasePrice;
                accessory.sellingPrice = sellingPrice;
            } else {
                accessories.push({
                    id: Date.now(),
                    name,
                    brand,
                    supplier,
                    stock,
                    vat,
                    purchasePrice,
                    sellingPrice
                });
            }

            renderAccessoryRows();
            backToMain();
        }

        renderAccessoryRows();
    </script>
</body>
</html>
