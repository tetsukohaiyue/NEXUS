# NEXUS ENTERPRISE — Backend PHP

Backend em PHP puro com arquitetura em camadas (Repository Pattern + DI) para salvar o progresso do jogo.

## Estrutura de Arquivos

```
nexus_backend/
├── config/
│   └── config.ini          ← credenciais do banco (no .gitignore)
├── src/
│   ├── Database/
│   │   └── Database.php    ← Passo 1: Singleton PDO
│   ├── Model/
│   │   └── Jogador.php     ← Passo 2: Entidade (sem SQL)
│   ├── Repository/
│   │   ├── IJogadorRepository.php  ← Passo 2: Interface/Contrato
│   │   └── JogadorRepository.php  ← Passo 2: SQL fica aqui
│   ├── Service/
│   │   └── JogadorService.php     ← Passo 3: Regras de negócio + DI
│   ├── Exception/
│   │   └── BusinessRuleException.php  ← Passo 3: Exceção própria
│   └── Controller/
│       └── JogadorController.php  ← Passo 4: try/catch, sem if/else
├── middleware/
│   └── Middleware.php      ← Passo 5: sanitização XSS, filter_input
├── public/
│   └── index.php           ← Passo 5: Container DI + roteador
├── banco.sql               ← Script MySQL
└── .gitignore
```

## Setup (XAMPP)

### 1. Banco de dados
```bash
# Abra o phpMyAdmin ou execute no terminal MySQL:
mysql -u root -p < banco.sql
```

### 2. Configuração
```bash
# Edite o config.ini com suas credenciais:
[database]
driver   = mysql
host     = localhost
port     = 3306
dbname   = nexus_game
username = root
password =        ← sua senha do MySQL (pode ficar em branco no XAMPP)
charset  = utf8mb4
```

### 3. Rodar o servidor
```bash
cd nexus_backend/public
php -S localhost:8000
```

Acesse http://localhost:8000 para ver o healthcheck.

---

## Rotas da API

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/jogador/iniciar` | Cria ou retoma sessão |
| POST | `/jogador/progresso` | Autosave do jogo |
| POST | `/jogador/final` | Desbloqueia final/morte |
| GET | `/jogador/mural?id=X` | Retorna mural do jogador |
| GET | `/jogador?id=X` | Retorna dados completos |
| DELETE | `/jogador?id=X` | Reseta jogador |

---

## Integração com o Jogo (HTML/JS)

Cole isso no `nexus_v6_fixed.html` dentro do `<script>`, logo antes do `console.log` final:

```javascript
// ══════════════════════════════
// NEXUS API — integração backend
// ══════════════════════════════
const API_URL = 'http://localhost:8000';
let PLAYER_ID = null; // preenchido ao iniciar

// Inicia sessão no backend ao começar o jogo
G._apiIniciar = async (nome, genero) => {
    try {
        const form = new FormData();
        form.append('nome', nome);
        form.append('genero', genero);
        const res = await fetch(`${API_URL}/jogador/iniciar`, { method:'POST', body: form });
        const data = await res.json();
        if (data.success) {
            PLAYER_ID = data.jogador.id;
            // Restaura progresso salvo no banco
            if (data.jogador.cena_atual !== 'room_start') {
                G.S.sync      = data.jogador.sync;
                G.S.sus       = data.jogador.suspeita;
                G.S.frags     = data.jogador.frags;
                G.S.inv       = data.jogador.inventario;
                G.S.lore      = data.jogador.lore;
                G.S.flags     = data.jogador.flags;
                G.S.path      = data.jogador.path_escolhido;
            }
        }
    } catch(e) { console.warn('Backend offline, usando localStorage.', e); }
};

// Autosave no backend
G._apiSalvar = async () => {
    if (!PLAYER_ID) return;
    try {
        await fetch(`${API_URL}/jogador/progresso`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id:         PLAYER_ID,
                cena:       G.S.scene,
                sync:       G.S.sync,
                suspeita:   G.S.sus,
                frags:      G.S.frags,
                path:       G.S.path || '',
                flags:      G.S.flags || {},
                inventario: G.S.inv || [],
                lore:       G.S.lore || [],
            }),
        });
    } catch(e) { /* silencioso — localStorage ainda funciona */ }
};

// Desbloquear final/morte no mural
G._apiMural = async (chave, tipo) => {
    if (!PLAYER_ID) return;
    try {
        await fetch(`${API_URL}/jogador/final`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: PLAYER_ID, chave, tipo }),
        });
    } catch(e) {}
};

// Hookear nas funções existentes do jogo:
const _origStart = G.startGame.bind(G);
G.startGame = async () => {
    _origStart();
    await G._apiIniciar(G.S.name, G.S.gen);
};

const _origSave = G.saveState.bind(G);
G.saveState = () => {
    _origSave();
    G._apiSalvar(); // autosave no banco também
};

const _origMural = G.saveMural.bind(G);
G.saveMural = (key, type) => {
    _origMural(key, type);
    G._apiMural(key, type); // persiste no banco também
};
```

---

## Testando (Passo 6)

### Forçar erro de banco:
Troque `jogadores` por `jogadores_xxx` no `JogadorRepository.php` e recarregue. O jogo não quebrará — verá `"Erro interno."` sem stack trace.

### Testar XSS:
No formulário de início, tente: `<script>alert(1)</script>`
O `filter_input` do Middleware barra antes de chegar ao Controller.

---

## Git (Passo 7)

```bash
git init
git add .
git commit -m "refactor: implementa padrao repository, variaveis de ambiente e injecao de dependencia"
git remote add origin https://github.com/seu-usuario/nexus-enterprise
git push -u origin main
```
