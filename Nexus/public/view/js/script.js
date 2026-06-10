G.saveState = () => {
  try {
    const data = JSON.stringify(G.S);
    localStorage.setItem(SAVE_KEY, data);
    sessionStorage.setItem(SAVE_KEY, data);

    // Envia os dados do jogo (Nome, Sync, Suspeita, Frags) para o index.php via POST
    fetch('index.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        nome: G.S.name || 'Agente Anónimo',
        sync: G.S.sync || 100,
        sus: G.S.sus || 0,
        frags: G.S.frags || 0
      })
    })
    .then(res => res.json())
    .then(dados => console.log('Sincronizado com o Banco PHP:', dados.message))
    .catch(err => console.warn('Erro ao conectar ao PHP:', err));

  } catch(e) { console.warn('Save failed:', e); }
};