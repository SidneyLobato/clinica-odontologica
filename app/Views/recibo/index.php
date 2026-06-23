<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pagamento #<?= $at['id'] ?></title>
    <style>
        body{font-family:Arial,sans-serif;font-size:12pt;margin:0;padding:20px;color:#333;}
        .header{text-align:center;border-bottom:2px solid #333;padding-bottom:15px;margin-bottom:20px;}
        .header h1{font-size:18pt;margin:0 0 4px;}
        .header p{margin:3px 0;font-size:10pt;color:#555;}
        .header h2{font-size:14pt;margin-top:10px;}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
        .info-item label{font-weight:bold;font-size:9pt;color:#666;display:block;}
        .info-item span{font-size:11pt;}
        table{width:100%;border-collapse:collapse;margin-bottom:20px;}
        th,td{border:1px solid #ccc;padding:8px 12px;font-size:10pt;}
        th{background:#f0f0f0;font-weight:bold;}
        .total-row{font-weight:bold;background:#f9f9f9;}
        .assinatura{margin-top:60px;text-align:center;}
        .assinatura hr{width:250px;margin:0 auto;}
        .footer{margin-top:20px;text-align:center;font-size:9pt;color:#888;border-top:1px solid #ccc;padding-top:10px;}
        .no-print{margin-bottom:15px;}
        @media print{.no-print{display:none;}body{padding:0;}}
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding:8px 20px;background:#005b96;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px;">🖨 Imprimir Recibo</button>
    <a href="javascript:history.back()" style="margin-left:10px;padding:8px 20px;background:#eee;color:#333;border:none;border-radius:6px;text-decoration:none;font-size:14px;">← Voltar</a>
</div>

<div class="header">
    <h1><?= htmlspecialchars($nomeClinica) ?></h1>
    <h2>RECIBO DE PAGAMENTO</h2>
</div>

<div class="info-grid">
    <div class="info-item"><label>Paciente</label><span><?= htmlspecialchars($at['paciente_nome']) ?></span></div>
    <div class="info-item"><label>CPF</label><span><?= htmlspecialchars($at['paciente_cpf'] ?? 'Não informado') ?></span></div>
    <div class="info-item"><label>Data do Atendimento</label><span><?= $dataFmt ?></span></div>
    <div class="info-item"><label>Responsável Técnico</label><span><?= htmlspecialchars($at['dentista_nome']) ?></span></div>
    <?php if (!empty($at['endereco'])): ?>
    <div class="info-item" style="grid-column:1/-1;">
        <label>Endereço do Paciente</label>
        <span><?= htmlspecialchars(trim(($at['endereco']??'').', '.($at['numero']??'').' — '.($at['bairro']??'').', '.($at['cidade']??'').' — '.($at['estado']??''), ', ')) ?></span>
    </div>
    <?php endif; ?>
</div>

<table>
    <thead>
        <tr><th>Procedimento Realizado</th><th style="text-align:right;width:150px;">Valor</th></tr>
    </thead>
    <tbody>
        <?php foreach ($procs as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td style="text-align:right;">R$ <?= number_format($p['valor_procedimento'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td>TOTAL PAGO</td>
            <td style="text-align:right;">R$ <?= number_format($at['valor_total'], 2, ',', '.') ?></td>
        </tr>
    </tfoot>
</table>

<p style="font-size:10pt;color:#555;line-height:1.6;">
    Recebi a quantia de <strong>R$ <?= number_format($at['valor_total'], 2, ',', '.') ?></strong>
    referente aos procedimentos odontológicos acima discriminados, realizados na data indicada.
</p>

<div class="assinatura">
    <p><?= $dataFmt ?></p>
    <br><br><hr>
    <p><?= htmlspecialchars($at['dentista_nome']) ?> — Responsável Técnico</p>
</div>

<div class="footer">
    Este recibo é válido como comprovante de pagamento dos serviços odontológicos prestados.<br>
    <?= htmlspecialchars($nomeClinica) ?> © <?= date('Y') ?>
</div>
</body>
</html>
