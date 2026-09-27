/**
 * "Verifique o seu CV" na página de uma vaga.
 *
 * O candidato escolhe — ou arrasta — um PDF, e o servidor devolve a
 * percentagem de compatibilidade com esta vaga. O ficheiro nunca é guardado:
 * vai no pedido e acaba ali.
 *
 * A vaga não viaja no pedido; o servidor vai buscá-la pelo endereço da página.
 */
(function () {
    'use strict';

    const cartao = document.getElementById('verificar-cv');

    if (!cartao) {
        return;
    }

    const url = cartao.dataset.url;
    const form = cartao.querySelector('[data-form]');
    const zona = cartao.querySelector('[data-drop]');
    const input = cartao.querySelector('[data-input]');
    const nome = cartao.querySelector('[data-nome]');
    const botao = cartao.querySelector('[data-submit]');
    const erro = cartao.querySelector('[data-erro]');
    const resultado = cartao.querySelector('[data-resultado]');
    const percentagem = cartao.querySelector('[data-percentagem]');
    const barra = cartao.querySelector('[data-barra]');
    const leitura = cartao.querySelector('[data-leitura]');
    const recomecar = cartao.querySelector('[data-recomecar]');

    const nomeInicial = nome.textContent;
    let ficheiro = null;
    let aCorrer = false;

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : '';
    }

    function mostrarErro(mensagem) {
        erro.textContent = mensagem || '';
        erro.classList.toggle('d-none', !mensagem);
    }

    function escolher(escolhido) {
        mostrarErro('');

        if (!escolhido) {
            return;
        }

        if (!/\.pdf$/i.test(escolhido.name)) {
            mostrarErro(cartao.dataset.soPdf);

            return;
        }

        ficheiro = escolhido;
        nome.textContent = escolhido.name;
        botao.disabled = false;
        resultado.classList.add('d-none');
    }

    input.addEventListener('change', (evento) => {
        escolher(evento.target.files[0]);
        // Deixa voltar a escolher o mesmo ficheiro depois de recomeçar.
        evento.target.value = '';
    });

    // Arrastar e soltar. O largar é travado na página inteira porque, por
    // omissão, o browser abre o ficheiro largado e leva o visitante para fora
    // da vaga.
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((evento) => {
        document.addEventListener(evento, (e) => {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    let dentro = 0;

    zona.addEventListener('dragenter', () => {
        dentro += 1;
        zona.classList.add('esta-a-receber');
    });

    zona.addEventListener('dragleave', () => {
        dentro = Math.max(0, dentro - 1);

        if (dentro === 0) {
            zona.classList.remove('esta-a-receber');
        }
    });

    zona.addEventListener('drop', (evento) => {
        dentro = 0;
        zona.classList.remove('esta-a-receber');

        const largados = evento.dataTransfer && evento.dataTransfer.files;

        if (largados && largados.length) {
            escolher(largados[0]);
        }
    });

    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (!ficheiro || aCorrer) {
            return;
        }

        aCorrer = true;
        botao.disabled = true;
        const textoBotao = botao.innerHTML;
        botao.textContent = cartao.dataset.aVerificar;
        mostrarErro('');

        const dados = new FormData();
        dados.append('cv', ficheiro, ficheiro.name);

        try {
            const resposta = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: dados,
            });

            const json = await resposta.json().catch(() => null);

            if (!resposta.ok || !json || json.ok !== true) {
                throw new Error(
                    (json && (json.message || (json.errors && Object.values(json.errors)[0][0])))
                    || cartao.dataset.falhou
                );
            }

            percentagem.textContent = json.percentagem + '%';
            barra.style.width = json.percentagem + '%';
            barra.className = 'progress-bar ' + (
                json.nivel === 'alta' ? 'bg-success'
                    : (json.nivel === 'media' ? 'bg-warning' : 'bg-secondary')
            );
            leitura.textContent = json.leitura;
            resultado.classList.remove('d-none');
            form.classList.add('d-none');
        } catch (e) {
            mostrarErro(e.message);
        } finally {
            aCorrer = false;
            botao.innerHTML = textoBotao;
            botao.disabled = !ficheiro;
        }
    });

    recomecar.addEventListener('click', () => {
        ficheiro = null;
        nome.textContent = nomeInicial;
        botao.disabled = true;
        resultado.classList.add('d-none');
        form.classList.remove('d-none');
        mostrarErro('');
    });
})();
