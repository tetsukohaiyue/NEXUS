# Projeto NEXUS - Contexto Completo

## 📋 Visão Geral
**Nome:** NEXUS  
**Tipo:** Jogo Narrativo Web Imersivo  
**Duração:** ~30 minutos de experiência  
**Objetivo:** Explorar temas de segurança de dados, corporativismo e aprisionamento digital através de uma narrativa interativa

## 🎮 Experiência Principal
- Sistema narrativo imersivo baseado no tema de "I Have No Mouth, and I Must Scream"
- Múltiplos personagens (Sistema, Você, Ruel, Voz Distorcida, Narração)
- Efeitos visuais retro-futuristas com paleta neon
- 4 finais distintos baseados em escolhas do jogador
- Minigames integrados que afetam a progressão da história

## 🔑 Features Principais

### 1. Sistema de Diálogos Dinâmicos
- Efeito de digitação progressiva
- Múltiplos personagens com cores distintas
- Clique para avançar entre diálogos
- Nomeação personalizada do jogador

### 2. Sistema de Escolhas Narrativas
- Múltiplas escolhas que afetam o fluxo da história
- Primeira escolha crítica: abrir ou ignorar email da empresa
- Navegação entre cenas baseada em escolhas

### 3. Gerenciamento de Estado
- Hook customizado (useGameState) que controla toda a lógica
- Rastreamento de cenas, diálogos, escolhas, sincronização
- Persistência automática via localStorage

### 4. HUD (Interface de Jogo)
- Barra de Sincronização (saúde da consciência de Ruel)
- Timer de 30 minutos com contagem regressiva
- Contador de Fragmentos coletados
- Informações em tempo real

### 5. Efeitos Visuais
- Glitches visuais (linhas vermelhas, distorção)
- Transições suaves entre cenas
- Fundo dinâmico com imagens de cenas

### 6. Minigames Integrados
- **Minigame 1:** Fuga de Entidade (evasão - desviar de criatura clicando direções corretas)
- **Minigame 2:** Decifrador de Código (puzzle - resolver padrão de código)
- **Minigame 3:** Sincronização de Consciência (resistência - clicar no tempo certo)

### 7. Sistema de Múltiplos Finais
- **Final 1 (Demissão):** Ignorar email → Demitido, nunca descobre a verdade
- **Final 2 (Eliminação):** Deletar Ruel → Problema resolvido, mas vitória vazia
- **Final 3 (Sacrifício):** Transferir Ruel → Você morre, Ruel escapa
- **Final 4 (Verdadeiro):** Entrar na máquina com Ruel → Ambos digitalizados para sempre

## 💻 Stack Tecnológica
- **Frontend:** React 19 com TypeScript
- **Styling:** TailwindCSS 4 (design retro-futurista, paleta neon)
- **Gerenciamento de Estado:** Hook customizado (useGameState)
- **Roteamento:** Wouter (se necessário)
- **Persistência:** LocalStorage
- **Responsividade:** Desktop e Mobile

## 🎨 Design & Visual
- **Paleta de Cores:** Cyan, Magenta, Verde, Vermelho
- **Tema:** Retro-futurista / Cyberpunk
- **Efeitos:** Glitches, distorção, animações de digitação
- **Ilustrações:** 4-5 PNG (1920x1080) para diferentes cenas

## ✅ Requisitos Técnicos
- Carregamento rápido, sem lag durante minigames
- Compatibilidade com navegadores modernos (Chrome, Firefox, Safari, Edge)
- Auto-save com localStorage
- Design responsivo em desktop e mobile
- Tipografia monoespacial para atmosfera

## 📦 Entregáveis
1. Repositório GitHub com código versionado
2. Deploy Web (jogo publicado e acessível via URL)
3. Documentação de desenvolvimento
4. Projeto de portfólio demonstrando habilidades em game design e web dev
