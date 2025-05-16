<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Relatório – Horta <?= htmlspecialchars(isset($nome_horta) ? $nome_horta : '–', ENT_QUOTES) ?></title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      color: #333;
      margin: 20px;
      font-size: 12px;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #3e8914;
      padding-bottom: 10px;
    }

    .header h1 {
      margin: 0;
      font-size: 24px;
      color: #3e8914;
    }

    .logo {
      height: 40px;
      margin-bottom: 10px;
    }

    .section {
      margin-bottom: 25px;
    }

    h2 {
      font-size: 18px;
      color: #3e8914;
      margin-bottom: 10px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
    }

    h3 {
      font-size: 14px;
      color: #2d6b12;
      margin: 8px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 6px;
      text-align: left;
    }

    th {
      background-color: #3e8914;
      color: #fff;
      text-transform: uppercase;
      font-size: 12px;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .grafico img {
      width: 100%;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-top: 5px;
    }
  </style>
</head>

<body>
  <!-- Cabeçalho simples, sem position:fixed -->
  <div class="header">
    <!-- Logo opcional -->

    <h1>Relatório de Leituras</h1>
    <p><strong>Horta:</strong> <?= htmlspecialchars($nome_horta ?? '–', ENT_QUOTES) ?></p>
  </div>

  <?php if (!empty($chartImages)): ?>
    <div class="section">
      <h2>Gráficos de Sensores</h2>
      <?php foreach ($chartImages as $sensorKey => $imgUri):
        $sensorName = ucfirst(str_replace('_', ' ', substr($sensorKey, 4)));
        ?>
        <div class="grafico">
          <h3><?= htmlspecialchars($sensorName, ENT_QUOTES) ?></h3>
          <img src="<?= $imgUri ?>" alt="Gráfico <?= htmlspecialchars($sensorName) ?>">
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="section">
    <h2>Leituras Filtradas</h2>
    <table>
      <thead>
        <tr>
          <th>Sensor</th>
          <th>Valor</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Dispositivo</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leituras as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['nome_sensor'], ENT_QUOTES) ?></td>
            <td><?= htmlspecialchars($l['valor_leitura'], ENT_QUOTES) ?></td>
            <td><?= htmlspecialchars($l['data_leitura'], ENT_QUOTES) ?></td>
            <td><?= htmlspecialchars($l['hora_leitura'], ENT_QUOTES) ?></td>
            <td><?= htmlspecialchars($l['Dispositivo_idDispositivo'], ENT_QUOTES) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($ultimasLeituras)): ?>
    <div class="section">
      <h2>Últimas Leituras por Dispositivo</h2>
      <table>
        <thead>
          <tr>
            <th>Sensor</th>
            <th>Valor</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Dispositivo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasLeituras as $l): ?>
            <tr>
              <td><?= htmlspecialchars($l['nome_sensor'], ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars($l['valor_leitura'], ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars($l['data_leitura'], ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars($l['hora_leitura'], ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars($l['Dispositivo_idDispositivo'], ENT_QUOTES) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</body>

</html>