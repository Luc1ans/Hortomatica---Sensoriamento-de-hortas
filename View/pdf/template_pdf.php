<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Leituras – Horta <?= htmlspecialchars($idHorta, ENT_QUOTES) ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2, h3 { color: #3e8914; }
        table { width: 100%; border-collapse: collapse; margin: 1em 0; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #3e8914; color: #fff; }
        .grafico { margin-bottom: 20px; page-break-inside: avoid; }
        .grafico img { width: 100%; }
    </style>
</head>
<body>
    <h2>Relatório de Leituras – Horta <?= htmlspecialchars($idHorta, ENT_QUOTES) ?></h2>

    <?php if (!empty($chartImages)): ?>
        <h3>Gráficos de Sensores</h3>
        <?php foreach ($chartImages as $sensorKey => $imgUri): 
            // transformar "img_Sensor_Temp" em "Sensor Temp"
            $sensorName = str_replace('_', ' ', substr($sensorKey, 4));
        ?>
            <div class="grafico">
                <h4><?= htmlspecialchars($sensorName, ENT_QUOTES) ?></h4>
                <img src="<?= $imgUri ?>" alt="Gráfico <?= htmlspecialchars($sensorName) ?>">
            </div>
        <?php endforeach ?>
    <?php endif; ?>

    <h3>Leituras Filtradas</h3>
    <table>
      <thead>
        <tr>
          <th>Sensor</th><th>Valor</th><th>Data</th><th>Hora</th><th>Dispositivo</th>
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

    <?php if (!empty($ultimasLeituras)): ?>
        <h3>Últimas Leituras por Dispositivo</h3>
        <table>
          <thead>
            <tr>
              <th>Sensor</th><th>Valor</th><th>Data</th><th>Hora</th><th>Dispositivo</th>
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
    <?php endif; ?>
</body>
</html>
