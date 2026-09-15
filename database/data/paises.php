<?php

/**
 * Países onde se publicam vagas.
 *
 * Angola e Brasil primeiro, porque são os dois países de língua portuguesa do
 * portal e a ordem em que foram pedidos fixa o id de cada um (Angola = 1, que é
 * o que as vagas antigas passam a ter). A seguir vêm, por ordem alfabética, os
 * países de língua espanhola.
 *
 * O "code" é o ISO 3166-1 alfa-2 — é isso que o addressCountry do JobPosting
 * espera, e é a partir dele que se desenha a bandeira.
 *
 * Esta lista é lida pela migração que cria a tabela e pelo CountrySeeder. Para
 * acrescentar um país basta juntá-lo aqui e correr `php artisan db:seed
 * --class=CountrySeeder`: o seeder só acrescenta o que falta.
 *
 * @return array<int, array{name: string, name_en: string, code: string}>
 */
return [
    ['name' => 'Angola', 'name_en' => 'Angola', 'code' => 'AO'],
    ['name' => 'Brasil', 'name_en' => 'Brazil', 'code' => 'BR'],

    // Países de língua espanhola
    ['name' => 'Argentina', 'name_en' => 'Argentina', 'code' => 'AR'],
    ['name' => 'Bolívia', 'name_en' => 'Bolivia', 'code' => 'BO'],
    ['name' => 'Chile', 'name_en' => 'Chile', 'code' => 'CL'],
    ['name' => 'Colômbia', 'name_en' => 'Colombia', 'code' => 'CO'],
    ['name' => 'Costa Rica', 'name_en' => 'Costa Rica', 'code' => 'CR'],
    ['name' => 'Cuba', 'name_en' => 'Cuba', 'code' => 'CU'],
    ['name' => 'El Salvador', 'name_en' => 'El Salvador', 'code' => 'SV'],
    ['name' => 'Equador', 'name_en' => 'Ecuador', 'code' => 'EC'],
    ['name' => 'Espanha', 'name_en' => 'Spain', 'code' => 'ES'],
    ['name' => 'Guatemala', 'name_en' => 'Guatemala', 'code' => 'GT'],
    ['name' => 'Guiné Equatorial', 'name_en' => 'Equatorial Guinea', 'code' => 'GQ'],
    ['name' => 'Honduras', 'name_en' => 'Honduras', 'code' => 'HN'],
    ['name' => 'México', 'name_en' => 'Mexico', 'code' => 'MX'],
    ['name' => 'Nicarágua', 'name_en' => 'Nicaragua', 'code' => 'NI'],
    ['name' => 'Panamá', 'name_en' => 'Panama', 'code' => 'PA'],
    ['name' => 'Paraguai', 'name_en' => 'Paraguay', 'code' => 'PY'],
    ['name' => 'Peru', 'name_en' => 'Peru', 'code' => 'PE'],
    ['name' => 'Porto Rico', 'name_en' => 'Puerto Rico', 'code' => 'PR'],
    ['name' => 'República Dominicana', 'name_en' => 'Dominican Republic', 'code' => 'DO'],
    ['name' => 'Uruguai', 'name_en' => 'Uruguay', 'code' => 'UY'],
    ['name' => 'Venezuela', 'name_en' => 'Venezuela', 'code' => 'VE'],
];
