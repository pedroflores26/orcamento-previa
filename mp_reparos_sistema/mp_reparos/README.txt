╔══════════════════════════════════════════════════════════╗
║        MP REPAROS AUTOMOTIVOS — Sistema de Gestão        ║
║                   Versão 1.0 — PHP + MySQL               ║
╚══════════════════════════════════════════════════════════╝

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 REQUISITOS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ✅ XAMPP instalado (Apache + MySQL + PHP)
     Download: https://www.apachefriends.org/download.html

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 PASSO A PASSO — INSTALAÇÃO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  PASSO 1 — Copiar os arquivos
  ─────────────────────────────
  Copie a pasta "mp_reparos" inteira para dentro de:
    C:\xampp\htdocs\

  Resultado: C:\xampp\htdocs\mp_reparos\

  PASSO 2 — Criar o banco de dados
  ──────────────────────────────────
  1. Abra o XAMPP Control Panel
  2. Clique em "Start" no Apache e no MySQL
  3. Clique em "Admin" ao lado do MySQL
     (ou acesse: http://localhost/phpmyadmin)
  4. No phpMyAdmin, clique em "Importar"
  5. Selecione o arquivo: banco/mp_reparos.sql
  6. Clique em "Executar"

  PASSO 3 — Acessar o sistema
  ─────────────────────────────
  Abra o navegador e acesse:
    http://localhost/mp_reparos/

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 CONFIGURAÇÃO DO BANCO (se necessário)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Se o seu XAMPP tem senha no MySQL, edite:
    config/db.php
  
  Altere as linhas:
    define('DB_USER', 'root');   ← usuário MySQL
    define('DB_PASS', '');       ← senha MySQL (padrão XAMPP = vazio)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 DADOS DA EMPRESA (para o orçamento)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Para alterar os dados da empresa nos orçamentos, edite:
    orcamento_print.php  (linhas ~54-58)
    orcamentos.php       (linha ~40-43)
  
  Substitua as informações de endereço, telefone e CNPJ.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 FUNCIONALIDADES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  📊 Dashboard      — Visão geral com estatísticas da semana
  📅 Agenda         — Calendário semanal de agendamentos
                      Adicionar, editar, excluir agendamentos
                      Status: Aguardando / Em andamento / Pronto / Cancelado
                      Serviços e valor por agendamento
  👤 Clientes       — Cadastro completo de clientes
  🚗 Veículos       — Cadastro de veículos vinculados a clientes
  📄 Orçamentos     — Criação de orçamentos com itens
                      Impressão em PDF via navegador

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 ESTRUTURA DE ARQUIVOS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  mp_reparos/
  ├── banco/
  │   └── mp_reparos.sql     ← Importar no phpMyAdmin
  ├── config/
  │   └── db.php             ← Configuração do banco
  ├── includes/
  │   ├── topo.php           ← Cabeçalho + navegação
  │   └── rodape.php         ← Rodapé
  ├── assets/
  │   └── style.css          ← Estilos do sistema
  ├── api/
  │   ├── agendamentos.php   ← API agendamentos
  │   ├── clientes.php       ← API clientes
  │   ├── veiculos.php       ← API veículos
  │   └── orcamentos.php     ← API orçamentos
  ├── index.php              ← Dashboard
  ├── agenda.php             ← Agenda semanal
  ├── clientes.php           ← Gestão de clientes
  ├── veiculos.php           ← Gestão de veículos
  ├── orcamentos.php         ← Gestão de orçamentos
  └── orcamento_print.php    ← Impressão do orçamento

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 GERAR PDF DO ORÇAMENTO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  1. Abra um orçamento e clique em "Imprimir"
  2. Na janela que abrir, clique em "Imprimir / Salvar PDF"
  3. Na caixa de impressão do navegador:
     - Selecione "Salvar como PDF" como impressora
     - Clique em "Salvar"

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 SUPORTE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Desenvolvido com PHP 8+ e MySQL 8+
  Compatível com XAMPP 8.x
