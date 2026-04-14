console.log("O JavaScript está funcionando!");
// Espera a página carregar completamente antes de rodar o código
document.addEventListener("DOMContentLoaded", function() {
  
  // Pega o botão pelo ID
  const botao = document.getElementById("botao-email");
  
  // Pega a div da mensagem pelo ID
  const mensagem = document.getElementById("mensagem-email");
  
  // Quando clicar no botão, executa essa função
  botao.addEventListener("click", function() {
    // Mostra a mensagem (muda o display de none para block)
    mensagem.style.display = "block";
    
    // (Opcional: esconde o botão depois de clicar)
    botao.style.display = "none";
    
    // (Opcional: adiciona um efeito legal de "glitch")
    mensagem.classList.add("glitch-effect");
    
    console.log("Email aberto! A anomalia está próxima...");
  });
});