<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Leituras</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .titulo { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #3e8914; color: #fff; }
    </style>
</head>
<body>
    <h2 class="titulo">Relatório de Leituras - Horta <?= htmlspecialchars($idHorta, ENT_QUOTES, 'UTF-8') ?></h2>

    <!-- Tabela de Últimas Leituras -->
    <h3>Últimas Leituras</h3>
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
            <?php foreach ($ultimasLeituras as $leitura): ?>
            <tr>
                <td><?= htmlspecialchars($leitura['nome_sensor'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['valor_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['data_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['hora_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['Dispositivo_idDispositivo'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tabela de Leituras Filtradas -->
    <h3>Leituras Filtradas</h3>
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
            <?php foreach ($leituras as $leitura): ?>
            <tr>
                <td><?= htmlspecialchars($leitura['nome_sensor'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['valor_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['data_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['hora_leitura'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($leitura['Dispositivo_idDispositivo'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>