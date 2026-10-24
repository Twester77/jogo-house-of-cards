# 🃏 House of Cards: Swipe & Blefe Edition

Um jogo de cartas (estilo Pife) desenvolvido para rodar **100% offline** em navegadores. Criado originalmente para a feira de tecnologia da UNIFEV, o projeto foca em interações fluidas via toque (mobile) e mouse (desktop).

![Status do Projeto](https://img.shields.io/badge/Status-Conclu%C3%ADdo-success)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)

## 🎯 Objetivo do Jogo
O clássico jogo de cartas adaptado com um motor de gestos e blefes táticos. O objetivo é ser o primeiro a **limpar a mão** (zerar as cartas). 

Durante o turno, o jogador arrasta 1 ou 2 cartas para a mesa e faz uma declaração: *"É a manilha"* ou *"Não é a manilha"*. O oponente deve decidir se **Acredita** ou se **Duvida** (Questiona). Se o jogador estiver blefando e for pego, ou se o oponente duvidar de uma verdade, quem errar leva penalidade e compra cartas extras!

## ✨ Funcionalidades
- **Motor de Swipe/Drag customizado:** Arraste as cartas com física de mola (spring physics), otimizado para 60fps e sem dependências externas.
- **Gaveta de Descarte:** Interface intuitiva para arrastar as cartas para cima.
- **Shield de Segurança:** Botão para ocultar/revelar as cartas na hora de passar o celular para o oponente.
- **Feedback Tátil e Visual:** Pop-ups animados gigantes para "BLEFE PEGO" e "VERDADE".
- **PWA & Standalone:** Pode ser instalado no celular via "Adicionar à Tela de Início" (funciona em rede local HTTP).
- **Zero Dependências:** Sem CDNs, sem frameworks, sem banco de dados. Roda em qualquer servidor PHP básico.

## 🛠️ Tecnologias Utilizadas
- **Back-end:** PHP 8+ (Gerenciamento de estado via `$_SESSION` e padrão PRG para evitar reenvio de formulário).
- **Front-end:** Vanilla JS (Pointer Events API, RequestAnimationFrame).
- **Estilização:** CSS3 (Glassmorphism, Flexbox, CSS Grid, Animações Keyframe).
- **Arquitetura:** Design System focado em Mobile-First com responsividade fluida via `clamp()` e Container Queries.

## 🚀 Como Executar Localmente (Modo Feira)

Como o projeto foi feito para ser portátil (ex: rodar de um Pen Drive), você não precisa de XAMPP instalado.

1. Baixe o PHP portátil (ou copie a pasta `php` do seu XAMPP).
2. Coloque a pasta `php` na raiz deste projeto.
3. Crie um arquivo `iniciar.bat` (Windows) com o código:
   ```bat
   @echo off
   title Servidor House of Cards
   cd /d "%~dp0"
   php\php.exe -S 0.0.0.0:8000
   pause