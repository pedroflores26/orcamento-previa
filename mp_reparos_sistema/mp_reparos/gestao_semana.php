<?php
$pagina = 'gestao';
$titulo = 'Detalhes da Semana';
$subtitulo = 'Fluxo semanal de veículos';

require_once 'config/db.php';
$db = getDB();

$inicio = $_GET['inicio'] ?? date('Y-m-d');
$fim    = $_GET['fim'] ?? date('Y-m-d');
$ano    = $_GET['ano'] ?? date('Y');
$mes    = $_GET['mes'] ?? date('m');

function dataBR($d){
    return date('d/m/Y', strtotime($d));
}

function moedaBR($v){
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function clienteId($db, $nome){
    $nome = trim($nome);
    if($nome === '') return null;

    $s = $db->prepare("SELECT id FROM clientes WHERE LOWER(nome)=LOWER(?) LIMIT 1");
    $s->bind_param('s', $nome);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();

    if($r) return $r['id'];

    $s = $db->prepare("INSERT INTO clientes (nome) VALUES (?)");
    $s->bind_param('s', $nome);
    $s->execute();

    return $db->insert_id;
}

function veiculoId($db, $clienteId, $modelo, $placa, $cor){
    $modelo = trim($modelo);
    $placa  = strtoupper(trim($placa));
    $cor    = trim($cor);

    if(!$clienteId || ($modelo === '' && $placa === '')) return null;

    if($placa !== ''){
        $s = $db->prepare("SELECT id FROM veiculos WHERE cliente_id=? AND UPPER(placa)=UPPER(?) LIMIT 1");
        $s->bind_param('is', $clienteId, $placa);
    } else {
        $s = $db->prepare("SELECT id FROM veiculos WHERE cliente_id=? AND LOWER(modelo)=LOWER(?) LIMIT 1");
        $s->bind_param('is', $clienteId, $modelo);
    }

    $s->execute();
    $r = $s->get_result()->fetch_assoc();

    if($r) return $r['id'];

    $s = $db->prepare("INSERT INTO veiculos (cliente_id, placa, modelo, cor) VALUES (?, ?, ?, ?)");
    $s->bind_param('isss', $clienteId, $placa, $modelo, $cor);
    $s->execute();

    return $db->insert_id;
}

/* AÇÕES */
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $acao = $_POST['acao'] ?? '';

    if($acao === 'delete'){
        $id = (int)($_POST['id'] ?? 0);

        $s = $db->prepare("DELETE FROM agendamento_servicos WHERE agendamento_id=?");
        $s->bind_param('i', $id);
        $s->execute();

        $s = $db->prepare("DELETE FROM agendamentos WHERE id=?");
        $s->bind_param('i', $id);
        $s->execute();

        header("Location: gestao_semana.php?ano=$ano&mes=$mes&inicio=$inicio&fim=$fim");
        exit;
    }

    $id           = (int)($_POST['id'] ?? 0);
    $clienteNome  = trim($_POST['cliente_nome'] ?? '');
    $veiculoDesc  = trim($_POST['veiculo_desc'] ?? '');
    $placa        = strtoupper(trim($_POST['placa'] ?? ''));
    $cor          = trim($_POST['cor'] ?? '');
    $dataAgenda   = $_POST['data_agenda'] ?? $inicio;
    $valorTotal   = (float)($_POST['valor_total'] ?? 0);
    $obs          = trim($_POST['observacao'] ?? '');

    if($clienteNome !== '' && $veiculoDesc !== '' && $dataAgenda !== ''){

        $cliId = clienteId($db, $clienteNome);
        $veiId = veiculoId($db, $cliId, $veiculoDesc, $placa, $cor);

        if($acao === 'update' && $id > 0){

            $s = $db->prepare("
                UPDATE agendamentos SET
                    cliente_id=?,
                    veiculo_id=?,
                    data_agenda=?,
                    observacao=?,
                    valor_total=?,
                    hora=NULL
                WHERE id=?
            ");

            $s->bind_param(
                'iissdi',
                $cliId,
                $veiId,
                $dataAgenda,
                $obs,
                $valorTotal,
                $id
            );

            $s->execute();

        } else {

            $status = 'aguardando';

            $s = $db->prepare("
                INSERT INTO agendamentos 
                (cliente_id, veiculo_id, data_agenda, hora, status, observacao, valor_total)
                VALUES (?, ?, ?, NULL, ?, ?, ?)
            ");

            $s->bind_param(
                'iisssd',
                $cliId,
                $veiId,
                $dataAgenda,
                $status,
                $obs,
                $valorTotal
            );

            $s->execute();
        }

        header("Location: gestao_semana.php?ano=$ano&mes=$mes&inicio=$inicio&fim=$fim");
        exit;
    }
}

include 'includes/topo.php';

$sql = "
SELECT
    a.id,
    a.data_agenda,
    a.observacao,
    a.valor_total,
    c.nome AS cliente,
    v.placa,
    v.modelo,
    v.cor
FROM agendamentos a
LEFT JOIN clientes c ON c.id = a.cliente_id
LEFT JOIN veiculos v ON v.id = a.veiculo_id
WHERE a.data_agenda BETWEEN '$inicio' AND '$fim'
ORDER BY a.data_agenda
";

$agendamentos = $db->query($sql);
$totalCarros = $agendamentos->num_rows;

$totalPrevisto = 0;
$agendamentosArray = [];

while($ag = $agendamentos->fetch_assoc()){
    $totalPrevisto += (float)$ag['valor_total'];
    $agendamentosArray[] = $ag;
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Carros marcados</div>
        <div class="stat-val blue"><?= $totalCarros ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total da Semana

        </div>
        <div class="stat-val green"><?= moedaBR($totalPrevisto) ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Período</div>
        <div class="stat-val orange" style="font-size:20px;">
            <?= dataBR($inicio) ?> até <?= dataBR($fim) ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Mês/Ano</div>
        <div class="stat-val"><?= str_pad($mes,2,'0',STR_PAD_LEFT) ?>/<?= $ano ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <div class="card-icon">📅</div>
            <div class="card-title">
                Semana <?= dataBR($inicio) ?> até <?= dataBR($fim) ?>
            </div>
        </div>

        <div class="btn-group">
            <a href="gestao_mes.php?ano=<?= $ano ?>&mes=<?= $mes ?>" class="btn btn-ghost btn-sm">
                ← Voltar
            </a>

            <button class="btn btn-primary btn-sm" onclick="abrirNovoAgendamentoSemana()">
                ＋ Marcar carro
            </button>
        </div>
    </div>

    <div class="card-body">

        <?php if($totalCarros == 0): ?>

            <div class="empty-week">
                <div class="empty-icon">🚗</div>
                <h2>Nenhum veículo marcado nesta semana</h2>
                <p>Clique em “Marcar carro” para cadastrar um agendamento nesta semana.</p>

                <button class="btn btn-primary" onclick="abrirNovoAgendamentoSemana()">
                    ＋ Marcar carro
                </button>
            </div>

        <?php else: ?>

            <div class="semana-lista">

                <?php foreach($agendamentosArray as $ag): ?>

                    <div class="ag-card">

                        <div class="ag-card-top">
                            <div>
                                <div class="ag-data">
                                    <?= dataBR($ag['data_agenda']) ?>
                                </div>

                                <div class="ag-veiculo">
                                    <?= htmlspecialchars($ag['modelo'] ?: 'Veículo') ?>
                                </div>

                                <div class="ag-cliente">
                                    <?= htmlspecialchars($ag['cliente'] ?: 'Cliente não informado') ?>
                                </div>
                            </div>

                            <div class="ag-placa">
                                <?= htmlspecialchars($ag['placa'] ?: 'Sem placa') ?>
                            </div>
                        </div>

                        <div class="ag-info-grid">
                            <div>
                                <span>Cor</span>
                                <strong><?= htmlspecialchars($ag['cor'] ?: '—') ?></strong>
                            </div>

                            <div>
                                <span>Data</span>
                                <strong><?= dataBR($ag['data_agenda']) ?></strong>
                            </div>

                            <div>
                                <span>Valor</span>
                                <strong><?= moedaBR($ag['valor_total']) ?></strong>
                            </div>
                        </div>

                        <?php if(!empty($ag['observacao'])): ?>
                            <div class="ag-obs">
                                <?= nl2br(htmlspecialchars($ag['observacao'])) ?>
                            </div>
                        <?php endif; ?>

                        <div class="ag-actions">
                            <button
                                class="btn btn-ghost btn-xs"
                                onclick="editarAgendamentoSemana(
                                    '<?= $ag['id'] ?>',
                                    '<?= htmlspecialchars($ag['cliente'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($ag['modelo'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($ag['placa'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($ag['cor'], ENT_QUOTES) ?>',
                                    '<?= $ag['data_agenda'] ?>',
                                    '<?= htmlspecialchars($ag['observacao'], ENT_QUOTES) ?>',
                                    '<?= $ag['valor_total'] ?>'
                                )"
                            >
                                ✏️ Editar
                            </button>

                            <form method="POST" onsubmit="return confirm('Excluir este agendamento?')" style="display:inline;">
                                <input type="hidden" name="acao" value="delete">
                                <input type="hidden" name="id" value="<?= $ag['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-xs">
                                    🗑 Excluir
                                </button>
                            </form>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</div>

<div class="modal-overlay" id="modal-agendar-semana">
    <div class="modal modal-lg">

        <div class="modal-header">
            <div class="modal-title">🚗 <span id="modal-titulo-semana">Marcar carro</span></div>
            <button class="modal-close" onclick="fecharModal('modal-agendar-semana')">✕</button>
        </div>

        <form method="POST">

            <input type="hidden" name="acao" id="ag-acao-semana" value="create">
            <input type="hidden" name="id" id="ag-id-semana">

            <div class="modal-body">

                <div class="g2">
                    <div class="field">
                        <label>Cliente</label>
                        <input type="text" name="cliente_nome" id="ag-cliente-semana" placeholder="Nome do cliente ou revenda" required>
                    </div>

                    <div class="field">
                        <label>Veículo</label>
                        <input type="text" name="veiculo_desc" id="ag-veiculo-semana" placeholder="Ex: Gol G6, Onix, Civic..." required>
                    </div>
                </div>

                <div class="g3">
                    <div class="field">
                        <label>Placa</label>
                        <input type="text" name="placa" id="ag-placa-semana" placeholder="Ex: ABC1234">
                    </div>

                    <div class="field">
                        <label>Cor</label>
                        <input type="text" name="cor" id="ag-cor-semana" placeholder="Ex: Branco">
                    </div>

                    <div class="field">
                        <label>Data marcada</label>
                        <input type="date" name="data_agenda" id="ag-data-semana" min="<?= $inicio ?>" max="<?= $fim ?>" value="<?= $inicio ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label>Valor</label>
                    <input type="number" name="valor_total" id="ag-valor-semana" min="0" step="0.01" placeholder="0,00">
                </div>

                <div class="field">
                    <label>Observação</label>
                    <textarea name="observacao" id="ag-obs-semana" placeholder="Observações sobre o agendamento..."></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="fecharModal('modal-agendar-semana')">
                    Cancelar
                </button>

                <button type="submit" class="btn btn-primary btn-sm">
                    💾 Salvar
                </button>
            </div>

        </form>

    </div>
</div>

<style>
.empty-week{
    padding:45px;
    text-align:center;
    background:#f8fafc;
    border-radius:14px;
    border:1px dashed #cbd5e1;
}

.empty-icon{
    font-size:42px;
    margin-bottom:10px;
}

.semana-lista{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:16px;
}

.ag-card{
    background:white;
    border:1px solid var(--g200);
    border-radius:14px;
    padding:16px;
    box-shadow:var(--sh);
}

.ag-card-top{
    display:flex;
    justify-content:space-between;
    gap:14px;
    margin-bottom:14px;
}

.ag-data{
    font-size:12px;
    font-weight:800;
    color:var(--orange);
    text-transform:uppercase;
}

.ag-veiculo{
    font-size:19px;
    font-weight:900;
    color:var(--g800);
    margin-top:4px;
}

.ag-cliente{
    font-size:13px;
    color:var(--g500);
    margin-top:2px;
}

.ag-placa{
    background:var(--navy);
    color:white;
    border-radius:8px;
    padding:8px 10px;
    height:max-content;
    font-size:12px;
    font-weight:900;
    white-space:nowrap;
}

.ag-info-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8px;
    margin-bottom:12px;
}

.ag-info-grid div{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:8px;
    padding:8px;
}

.ag-info-grid span{
    display:block;
    font-size:10px;
    color:#64748b;
    font-weight:800;
    text-transform:uppercase;
    margin-bottom:2px;
}

.ag-info-grid strong{
    font-size:13px;
    color:#1e293b;
}

.ag-obs{
    margin-top:10px;
    background:#fff7ed;
    border:1px solid #fed7aa;
    border-radius:8px;
    padding:10px;
    font-size:13px;
    color:#9a3412;
}

.ag-actions{
    margin-top:14px;
    display:flex;
    gap:8px;
    justify-content:flex-end;
}

@media(max-width:600px){
    .semana-lista{
        grid-template-columns:1fr;
    }

    .ag-info-grid{
        grid-template-columns:1fr;
    }

    .ag-actions{
        flex-direction:column;
    }
}
</style>

<script>
function abrirNovoAgendamentoSemana(){
    document.getElementById('modal-titulo-semana').textContent = 'Marcar carro';
    document.getElementById('ag-acao-semana').value = 'create';
    document.getElementById('ag-id-semana').value = '';

    document.getElementById('ag-cliente-semana').value = '';
    document.getElementById('ag-veiculo-semana').value = '';
    document.getElementById('ag-placa-semana').value = '';
    document.getElementById('ag-cor-semana').value = '';
    document.getElementById('ag-data-semana').value = '<?= $inicio ?>';
    document.getElementById('ag-valor-semana').value = '';
    document.getElementById('ag-obs-semana').value = '';

    abrirModal('modal-agendar-semana');
}

function editarAgendamentoSemana(id, cliente, veiculo, placa, cor, data, obs, valor){
    document.getElementById('modal-titulo-semana').textContent = 'Editar agendamento';
    document.getElementById('ag-acao-semana').value = 'update';
    document.getElementById('ag-id-semana').value = id;

    document.getElementById('ag-cliente-semana').value = cliente || '';
    document.getElementById('ag-veiculo-semana').value = veiculo || '';
    document.getElementById('ag-placa-semana').value = placa || '';
    document.getElementById('ag-cor-semana').value = cor || '';
    document.getElementById('ag-data-semana').value = data || '<?= $inicio ?>';
    document.getElementById('ag-obs-semana').value = obs || '';
    document.getElementById('ag-valor-semana').value = valor || '';

    abrirModal('modal-agendar-semana');
}
</script>

<?php include 'includes/rodape.php'; ?>