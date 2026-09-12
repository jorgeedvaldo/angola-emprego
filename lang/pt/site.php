<?php

/**
 * Texto do site em português.
 *
 * As chaves descrevem onde o texto aparece, não o que ele diz — assim uma
 * mudança de redacção não obriga a renomear a chave em dois ficheiros e em
 * todas as views que a usam.
 */
return [

    'nav' => [
        'vagas' => 'Vagas',
        'cursos' => 'Cursos',
        'noticias' => 'Notícias',
        'recrutadores' => 'Empresas e Recrutadores',
        'analisar_cvs' => 'Analisar CVs',
        'entrar' => 'Entrar',
        'criar_conta' => 'Criar Conta',
        'sou_empresa' => 'Sou empresa',
        'painel_empresa' => 'Painel Empresa',
        'meu_perfil' => 'Meu Perfil',
        'vagas_sugeridas' => 'Vagas Sugeridas',
        'ver_pagina' => 'Ver página',
        'sair' => 'Sair',
    ],

    'footer' => [
        'sobre_o_site' => 'Angola Emprego é o maior portal de emprego e notícias em Angola. Conectamos talentos às melhores oportunidades em Angola.',
        'links_uteis' => 'Links Úteis',
        'inicio' => 'Início',
        'sobre' => 'Sobre',
        'empresas' => 'Empresas',
        'candidatos' => 'Candidatos',
        'siga_nos' => 'Siga-nos',
        'novidades' => 'Fique por dentro das novidades',
        'direitos' => 'Todos os direitos reservados',
        'idioma' => 'Idioma',
    ],

    'recrutadores' => [
        'titulo' => 'Empresas e Recrutadores',
        'intro' => 'Tudo o que precisa para contratar num só sítio: criar a página da sua empresa, conhecer as empresas que já publicam vagas e analisar os CVs que recebeu.',
        'como_funciona' => 'Como funciona a análise de CV',
        'criar_empresa' => 'Criar empresa',
        'criar_empresa_texto' => 'Registe a sua empresa gratuitamente, monte a página oficial com logótipo e contactos e comece a publicar vagas.',
        'registar_empresa' => 'Registar empresa',
        'ir_para_painel' => 'Ir para o painel',
        'ver_empresas' => 'Ver empresas',
        'lista_empresas' => 'Lista de empresas',
        'lista_empresas_texto' => ':count empresa(s) com página oficial no Angola Emprego. Veja quem está a contratar e as vagas de cada uma.',
        'ver_lista' => 'Ver lista de empresas',
        'analisar_cv' => 'Analisar CV',
        'analisar_cv_texto' => 'Escreva a descrição da vaga, carregue os CVs que tem em mão e receba-os ordenados do candidato mais compatível para o menos compatível. Sem conta e sem guardar os ficheiros.',
        'abrir_analisador' => 'Abrir analisador de CVs',
        'empresas_recentes' => 'Empresas recentes',
        'ver_todas' => 'Ver todas',
    ],

    'analisador' => [
        'titulo' => 'Analisador de CVs',
        'meta_titulo' => 'Analisador de CVs — compare currículos com a sua vaga',
        'meta_descricao' => 'Escreva a descrição da vaga, carregue os CVs e receba-os ordenados por compatibilidade. Grátis, sem criar conta e sem guardar os ficheiros.',
        'intro' => 'Escreva a descrição da vaga, escolha os CVs que tem em mão e receba-os ordenados do candidato mais compatível para o menos compatível. Não precisa de conta.',
        'privacidade_titulo' => 'Os CVs não são guardados.',
        'privacidade_texto' => 'Os ficheiros ficam no seu computador; cada um é enviado uma única vez, só durante a análise, e não é arquivado nem no site nem em nenhuma conta. Feche a página e não fica nada.',
        'nome_vaga' => 'Nome da vaga',
        'nome_vaga_ajuda' => 'Ajuda a distinguir cargos com requisitos parecidos.',
        'nome_vaga_exemplo' => 'Ex.: Técnico de Recursos Humanos',
        'descricao' => 'Descrição da vaga',
        'descricao_exemplo' => 'Descreva as funções, os requisitos, a formação e a experiência pretendida. Quanto mais detalhada a descrição, melhor a ordenação.',
        'ficheiros' => 'CVs (PDF)',
        'ficheiros_ajuda' => 'Até :max ficheiros, no máximo 5 MB cada. Só PDF — é o formato de que conseguimos ler o texto.',
        'analisar' => 'Analisar CVs',
        'como_funciona' => 'Como funciona',
        'passo_1' => 'Separamos o anúncio nos requisitos que pede, um a um.',
        'passo_2' => 'Lemos o texto de cada CV e dividimo-lo em blocos.',
        'passo_3' => 'Para cada requisito procuramos o bloco do CV que melhor lhe responde, e confirmamos se os termos do requisito aparecem mesmo no texto.',
        'passo_4' => 'Cada CV fica com a lista do que cumpre e do que falta, e a ordenação sai daí.',
        'dica_requisitos' => 'Escreva os requisitos em linhas separadas, debaixo de um título como <em>Requisitos</em> — é assim que conseguimos lê-los um a um.',
        'resultado' => 'Resultado',
        'vazio' => 'Escolha os CVs à esquerda e clique em “Analisar CVs”.',
    ],

    'js' => [
        'escolhidos_um' => 'CV escolhido',
        'escolhidos_varios' => 'CVs escolhidos',
        'falhou' => 'Falhou',
        'por_analisar' => 'Por analisar',
        'compativel' => ':percent% compatível',
        'termos_encontrados' => 'Termos da vaga encontrados no CV:',
        'requisitos_lidos' => 'Requisitos lidos do anúncio:',
        'diferencial' => 'diferencial',
        'cumpre' => 'cumpre',
        'parcial' => 'parcial',
        'ausente' => 'não encontrado',
        'abrir' => 'Abrir',
        'ignorados' => 'Ignorámos :count ficheiro(s): só aceitamos PDF, no máximo :max de cada vez.',
        'descricao_curta' => 'Escreva a descrição da vaga com algum detalhe antes de analisar.',
        'sem_ficheiros' => 'Escolha pelo menos um CV em PDF.',
        'a_analisar_vaga' => 'A analisar a descrição da vaga…',
        'a_analisar_cv' => 'A analisar :nome…',
        'concluida' => 'Análise concluída. Os CVs estão ordenados do mais para o menos compatível.',
        'concluida_com_erros' => 'Análise concluída com :count erro(s).',
        'demasiados_pedidos' => 'Demasiados pedidos seguidos. Espere um minuto e tente de novo.',
        'pedido_falhou' => 'O pedido ao servidor falhou (:status).',
    ],

];
