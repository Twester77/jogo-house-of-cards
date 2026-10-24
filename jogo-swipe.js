document.addEventListener('DOMContentLoaded', () => {
    const mesa       = document.getElementById('mesa');
    const zonaJogo   = document.getElementById('zona-jogo');
    const btnDesc    = document.getElementById('btn-descartar');
    const glassCard  = document.querySelector('.glass-card');
    if (!mesa) return;

    let draggedCard = null;
    let dragStart   = null;
    let dragBase    = null;
    let dragPointer = null;

    // ---------- LAYOUT ----------
    function layoutAll() {
        const handCards = Array.from(mesa.querySelectorAll('.carta-card[data-location="hand"]'));
        const playCards = Array.from(mesa.querySelectorAll('.carta-card[data-location="play"]'));

        const mesaRect = mesa.getBoundingClientRect();
        const zonaRect = zonaJogo.getBoundingClientRect();

        const cardEl = mesa.querySelector('.carta-card');
        const cardH  = cardEl ? cardEl.offsetHeight : 120;
        const cardW  = cardEl ? cardEl.offsetWidth : 80;

        const homeCx = mesaRect.left + mesaRect.width / 2;
        const homeCy = mesaRect.bottom - 20 - cardH / 2;
        const playCx = zonaRect.left + zonaRect.width / 2;
        const playCy = zonaRect.top  + zonaRect.height / 2;

        const playTx = playCx - homeCx;
        const playTy = playCy - homeCy;

        const spread = cardW * 0.45; 

        // ----- LEQUE (fan) -----
        const N = handCards.length;
        const center = (N - 1) / 2;
        handCards.forEach((card, i) => {
            const offset = i - center;
            const rot = offset * 6;
            const tx  = offset * spread;
            const ty  = -Math.abs(offset) * 5;
            card._base = { tx, ty, rot };
            if (!card.classList.contains('arrastando')) {
                card.style.transform = `translate(${tx}px, ${ty}px) rotate(${rot}deg)`;
            }
            card.style.zIndex = 100 + i;
        });

        // ----- GAVETA (stack) -----
        playCards.forEach((card, i) => {
            const offset = i - (playCards.length - 1) / 2;
            const rot = offset * 4;
            const tx  = playTx + (offset * spread * 0.3); 
            const ty  = playTy;
            card._base = { tx, ty, rot };
            if (!card.classList.contains('arrastando')) {
                card.style.transform = `translate(${tx}px, ${ty}px) rotate(${rot}deg)`;
            }
            card.style.zIndex = 200 + i;
        });

        if (btnDesc) {
            const count = playCards.length;
            if (count > 0 && count <= 2) {
                btnDesc.disabled = false;
                btnDesc.textContent = `Descartar ${count} carta${count > 1 ? 's' : ''} 🎯`;
            } else {
                btnDesc.disabled = true;
                btnDesc.textContent = count > 2 ? 'Máximo 2 cartas!' : 'Arraste cartas para a mesa';
            }
        }

        const hint = zonaJogo.querySelector('.zona-hint');
        if (hint) hint.style.opacity = playCards.length > 0 ? '0' : '1';
    }

    // ---------- DRAG ----------
    function startDrag(card, e) {
        if (mesa.classList.contains('mesa-oculta')) return;

        e.preventDefault();
        draggedCard = card;
        dragPointer = e.pointerId;
        dragStart   = { x: e.clientX, y: e.clientY };
        dragBase    = { ...(card._base || { tx: 0, ty: 0, rot: 0 }) };

        card.classList.add('arrastando');
        try { card.setPointerCapture(e.pointerId); } catch (_) {}
        if (glassCard) glassCard.classList.add('dragging-active');
    }

    function moveDrag(e) {
        if (!draggedCard || e.pointerId !== dragPointer) return;
        const dx = e.clientX - dragStart.x;
        const dy = e.clientY - dragStart.y;
        const b  = dragBase;
        draggedCard.style.transform = `translate(${b.tx + dx}px, ${b.ty + dy}px) rotate(${b.rot}deg)`;

        if (zonaJogo) {
            const r = draggedCard.getBoundingClientRect();
            const zr = zonaJogo.getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height / 2;
            const dentro = cx >= zr.left && cx <= zr.right && cy >= zr.top && cy <= zr.bottom;
            zonaJogo.classList.toggle('zona-ativa', dentro);
        }
    }

    function endDrag(e) {
        if (!draggedCard || e.pointerId !== dragPointer) return;
        const card = draggedCard;
        const rect = card.getBoundingClientRect();
        const zr   = zonaJogo.getBoundingClientRect();
        const cx   = rect.left + rect.width / 2;
        const cy   = rect.top  + rect.height / 2;
        const dentroGaveta = cx >= zr.left && cx <= zr.right && cy >= zr.top && cy <= zr.bottom;

        const locAtual  = card.dataset.location;
        const playCount = mesa.querySelectorAll('.carta-card[data-location="play"]').length;

        let novaLoc = locAtual;
        if (dentroGaveta && locAtual === 'hand') {
            novaLoc = playCount < 2 ? 'play' : 'hand';
            if (playCount >= 2) shakeCard(card);
        } else if (!dentroGaveta && locAtual === 'play') {
            novaLoc = 'hand';
        }

        card.dataset.location = novaLoc;
        const cb = card.querySelector('input[type="checkbox"]');
        if (cb) cb.checked = (novaLoc === 'play');

        card.classList.remove('arrastando');
        if (zonaJogo) zonaJogo.classList.remove('zona-ativa');
        if (glassCard) glassCard.classList.remove('dragging-active');

        requestAnimationFrame(() => layoutAll());

        draggedCard = null;
        dragStart = null;
        dragBase = null;
        dragPointer = null;
    }

    function cancelDrag() {
        if (!draggedCard) return;
        draggedCard.classList.remove('arrastando');
        if (zonaJogo) zonaJogo.classList.remove('zona-ativa');
        if (glassCard) glassCard.classList.remove('dragging-active');
        requestAnimationFrame(() => layoutAll());
        draggedCard = null;
        dragStart = null;
        dragBase = null;
        dragPointer = null;
    }

    function shakeCard(card) {
        card.classList.add('shake');
        setTimeout(() => card.classList.remove('shake'), 300);
    }

    // ---------- EVENTOS ----------
    mesa.addEventListener('pointerdown', (e) => {
        if (!e.isPrimary) return;
        if (e.target.closest('button')) return;
        const card = e.target.closest('.carta-card');
        if (card) { startDrag(card, e); return; }
    });

    window.addEventListener('pointermove', moveDrag, { passive: false });
    window.addEventListener('pointerup', endDrag);
    window.addEventListener('pointercancel', cancelDrag);

    window.addEventListener('resize', () => requestAnimationFrame(layoutAll));

    layoutAll();
    setTimeout(layoutAll, 100);
});

// ---------- FUNÇÕES GLOBAIS ----------
function abrirRegras() { document.getElementById('modal-regras').classList.add('aberto'); }
function fecharRegras() { document.getElementById('modal-regras').classList.remove('aberto'); }

function toggleRevelar() {
    const mesa = document.getElementById('mesa');
    const painel = document.getElementById('painel-oculto');
    const btn = document.getElementById('btn-revelar');
    const dica = document.getElementById('dica-flutuante');
    if (!mesa) return;

    const estaOculto = mesa.classList.toggle('mesa-oculta');
    
    if (estaOculto) {
        if (painel) painel.style.display = 'flex';
        if (btn) btn.innerHTML = '👁️ Revelar Minhas Cartas';
        if (dica) dica.innerHTML = 'Passe o celular/ PC e clique em Revelar! 👆';
    } else {
        if (painel) painel.style.display = 'none';
        if (btn) btn.innerHTML = ' Ocultar Minhas Cartas';
        if (dica) dica.innerHTML = 'Arraste as cartas para cima 👆';
    }
}

async function sairDoJogo() {
    if (!confirm('Sair do jogo e voltar ao Spotted?')) return;
    try { await fetch('api_jogo.php?acao=reset'); } catch {}
    window.location.href = 'feed.php';
}