<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Análise de Dados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/Assets/css/style.css">
    <?php include __DIR__ . '/../Assets/navbar.php'; ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', { 'packages': ['corechart'] });
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            var chartData = <?php echo json_encode($chartData); ?>;
            var dispositivosSelecionados = <?php echo json_encode($dispositivosSelecionados); ?>;
            for (var sensorName in chartData) {
                var safeId = sensorName.replace(/\W+/g, '_');
                drawChart(safeId, sensorName, chartData[sensorName], dispositivosSelecionados);
            }
        }

        function drawChart(safeId, sensorName, rows, dispositivos) {
            var container = document.getElementById('chart_' + safeId);
            if (!container) {
                console.error('Container não encontrado: chart_' + safeId);
                return;
            }

            var data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Data e Hora');
            dispositivos.forEach(id => data.addColumn('number', 'Disp ' + id));
            var formattedRows = rows.map(r => [new Date(r[0]), ...r.slice(1)]);
            data.addRows(formattedRows);

            var options = {
                title: 'Leituras do Sensor: ' + sensorName,
                legend: { position: 'bottom' },
                hAxis: { title: 'Data e Hora', format: 'yyyy/MM/dd HH:mm', slantedText: true },
                vAxis: { title: 'Valor' },
                backgroundColor: '#f8f9fa',
                chartArea: { backgroundColor: '#f8f9fa' }
            };

            // 1) Cria e desenha o gráfico
            var chart = new google.visualization.LineChart(container);
            chart.draw(data, options);

            // 2) Depois de desenhado, captura a imagem
            var imgUri = chart.getImageURI();

            // 3) Cria o input hidden e ANEXA DENTRO do form de PDF
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'img_' + safeId;
            input.value = imgUri;

            // garante que #chartImagesContainer esteja dentro do <form id="pdfForm">
            document.querySelector('#pdfForm #chartImagesContainer').appendChild(input);
        }

    </script>

</head>

<body>

    <div class="container mt-4">
        <h3><i class="bi bi-graph-up me-2 text-success"></i>Análise de Dados</h3>

        <!-- FILTROS -->
        <form method="GET" class="card p-3 mb-4">
            <input type="hidden" name="page" value="analise">
            <input type="hidden" name="idHorta" value="<?= $idHorta ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Canteiro</label>
                    <select name="idCanteiro" class="form-select">
                        <?php foreach ($canteiros as $c): ?>
                            <option value="<?= $c['idCanteiros'] ?>" <?= $c['idCanteiros'] == $selectedCanteiro ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['Cultura'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Dispositivos</label>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($devices as $d): ?>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" name="dispositivos[]"
                                    value="<?= $d['idDispositivo'] ?>" <?= in_array($d['idDispositivo'], $selDevices) ? 'checked' : '' ?>>
                                <label class="form-check-label">Disp <?= $d['idDispositivo'] ?></label>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <label>Sensor</label>
                    <select name="sensor" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (array_unique(array_column($leituras, 'nome_sensor')) as $s): ?>
                            <option value="<?= $s ?>" <?= $s == $filtroSensor ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Data Inicial</label>
                    <input type="date" name="data_inicial" value="<?= $filtroDataInicial ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Data Final</label>
                    <input type="date" name="data_final" value="<?= $filtroDataFinal ?>" class="form-control">
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary"><i class="bi bi-filter me-1"></i>Filtrar</button>
                <a href="?page=analise&idHorta=<?= htmlspecialchars($idHorta, ENT_QUOTES) ?>"
                    class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i>Limpar</a>
            </div>
        </form>

        <div id="charts">
            <div class="row g-4 mb-4">
                <!-- Gerar PDF -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-danger">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Gerar Relatório PDF
                            </h5>
                            <p class="card-text flex-grow-1">
                                Faça o download de um relatório completo das leituras atuais para a horta selecionada.
                            </p>
                            <form id="pdfForm" method="POST" action="<?= BASE_PATH ?>/Assets/gerar_pdf.php"
                                target="_blank">
                                <input type="hidden" name="idHorta"
                                    value="<?= htmlspecialchars($idHorta, ENT_QUOTES) ?>">
                                <input type="hidden" name="idCanteiro"
                                    value="<?= htmlspecialchars($selectedCanteiro, ENT_QUOTES) ?>">
                                <input type="hidden" name="dispositivos"
                                    value="<?= htmlspecialchars(implode(',', $selDevices), ENT_QUOTES) ?>">
                                <input type="hidden" name="sensor"
                                    value="<?= htmlspecialchars($filtroSensor, ENT_QUOTES) ?>">
                                <input type="hidden" name="data_inicial"
                                    value="<?= htmlspecialchars($filtroDataInicial, ENT_QUOTES) ?>">
                                <input type="hidden" name="data_final"
                                    value="<?= htmlspecialchars($filtroDataFinal, ENT_QUOTES) ?>">
                                <div id="chartImagesContainer"></div>
                                <button type="submit" class="btn btn-outline-danger w-100 mt-3">
                                    <i class="bi bi-download me-1"></i>Baixar PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Importar CSV -->
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary">
                                <i class="bi bi-upload me-2"></i>Importar CSV
                            </h5>
                            <p class="card-text flex-grow-1">
                                Carregue um arquivo CSV de leituras para inserir novos dados no banco (somente linhas
                                posteriores à última leitura).
                            </p>
                            <form method="POST" action="<?= BASE_PATH ?>/Assets/importCSV.php"
                                enctype="multipart/form-data">
                                <input type="hidden" name="idHorta"
                                    value="<?= htmlspecialchars($idHorta, ENT_QUOTES) ?>">
                                <input type="hidden" name="idCanteiro"
                                    value="<?= htmlspecialchars($selectedCanteiro, ENT_QUOTES) ?>">
                                <div class="input-group">
                                    <input type="file" name="csvFile" accept=".csv" class="form-control" required>
                                    <button type="submit" class="btn btn-outline-primary">
                                        Importar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($chartData as $sensor => $rows):
                $safe = preg_replace('/\W+/', '_', $sensor);
                ?>
                <div class="mb-4">
                    <h5 class="text-success"><i class="bi bi-bar-chart-line me-2"></i><?= $sensor ?></h5>
                    <div id="chart_<?= $safe ?>" style="height:300px;"></div>
                </div>
            <?php endforeach ?>
        </div>

        <!-- TABELAS -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card p-3">
                    <h5 class="text-success"><i class="bi bi-clock-history me-1"></i>Últimas Leituras</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sensor</th>
                                <th>Valor</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Disp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimas as $l): ?>
                                <tr>
                                    <td><?= $l['nome_sensor'] ?></td>
                                    <td><?= $l['valor_leitura'] ?></td>
                                    <td><?= $l['data_leitura'] ?></td>
                                    <td><?= $l['hora_leitura'] ?></td>
                                    <td><?= $l['Dispositivo_idDispositivo'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-3">
                    <h5 class="text-success"><i class="bi bi-table me-1"></i>Leituras Filtradas</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sensor</th>
                                <th>Valor</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Disp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leituras as $l): ?>
                                <tr>
                                    <td><?= $l['nome_sensor'] ?></td>
                                    <td><?= $l['valor_leitura'] ?></td>
                                    <td><?= $l['data_leitura'] ?></td>
                                    <td><?= $l['hora_leitura'] ?></td>
                                    <td><?= $l['Dispositivo_idDispositivo'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS DE CHARTS -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', { packages: ['corechart'] });
        google.charts.setOnLoadCallback(() => {
            const chartData = <?= json_encode($chartData) ?>;
            const selDevices = <?= json_encode($selDevices) ?>;

            for (let sensor in chartData) {
                const rows = chartData[sensor];
                const dataTable = new google.visualization.DataTable();
                dataTable.addColumn('datetime', 'Data e Hora');
                selDevices.forEach(id => dataTable.addColumn('number', 'Disp ' + id));
                const formatted = rows.map(r => [new Date(r[0]), ...r.slice(1)]);
                dataTable.addRows(formatted);

                const options = {
                    hAxis: { format: 'yyyy/MM/dd HH:mm', slantedText: true },
                    legend: { position: 'bottom' }
                };
                const div = document.getElementById('chart_' + sensor.replace(/\W+/g, '_'));
                new google.visualization.LineChart(div).draw(dataTable, options);
            }
        });
    </script>

    <?php include __DIR__ . '/../Assets/footer.php'; ?>
</body>

</html>