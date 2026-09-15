<?php

namespace App\Support;

/**
 * As cores e a forma do cartão social de uma vaga.
 *
 * O portal nasceu só com vagas de Angola e o cartão era um só: azul-escuro com o
 * logótipo do Angola Emprego ao cimo. Agora que se publicam vagas de outros
 * países, cada país pode ter o seu aspecto — quem vê a imagem numa rede social
 * percebe logo de onde é a vaga, antes de ler o título.
 *
 * Para dar aspecto próprio a mais um país basta acrescentar uma entrada em
 * TEMAS. Quem não tiver entrada fica com o cartão de sempre.
 */
class CoverTheme
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private const TEMAS = [
        // Brasil: verde e amarelo, e em vez do logótipo do Angola Emprego um
        // letreiro a dizer de que país é a vaga.
        'BR' => [
            'fundo_topo' => '#06351A',
            'fundo_base' => '#0B5B2C',
            'destaque' => '#FFDF00',      // o amarelo da bandeira
            'brilho' => [255, 223, 0],
            // O amarelo sobre o verde salta muito mais do que o azul saltava
            // sobre o azul-escuro: com a mesma força do tema de Angola, o
            // clarão virava um sol e roubava o cartão ao título.
            'brilho_forca' => 126,
            'titulo' => '#FFFFFF',
            'suave' => '#CFE8D2',
            'letreiro' => 'VAGA PARA BRASIL',
            'letreiro_texto' => '#06351A', // verde escuro sobre o amarelo
            'mostrar_local' => true,
        ],
    ];

    /** O cartão de sempre: azul-escuro com o logótipo do site. */
    private const OMISSAO = [
        'fundo_topo' => '#0b1526',
        'fundo_base' => '#10233d',
        'destaque' => '#106EEA',
        'brilho' => [16, 110, 234],
        'brilho_forca' => 123,
        'titulo' => '#FFFFFF',
        'suave' => '#AABEDC',
        'letreiro' => null,               // null = desenha-se o logótipo
        'letreiro_texto' => '#FFFFFF',
        'mostrar_local' => false,
    ];

    private array $tema;

    private function __construct(array $tema)
    {
        $this->tema = $tema;
    }

    public static function paraPais(?string $codigo): self
    {
        $codigo = strtoupper(trim((string) $codigo));

        return new self(self::TEMAS[$codigo] ?? self::OMISSAO);
    }

    public function fundoTopo(): string
    {
        return $this->tema['fundo_topo'];
    }

    public function fundoBase(): string
    {
        return $this->tema['fundo_base'];
    }

    public function destaque(): string
    {
        return $this->tema['destaque'];
    }

    /** @return array{0: int, 1: int, 2: int} */
    public function brilho(): array
    {
        return $this->tema['brilho'];
    }

    /**
     * Transparência de cada camada do clarão, na escala do GD: 0 é opaco e 127
     * é invisível. São dezenas de camadas sobrepostas, por isso um valor mais
     * alto dá um clarão bastante mais discreto.
     */
    public function brilhoForca(): int
    {
        return $this->tema['brilho_forca'];
    }

    public function titulo(): string
    {
        return $this->tema['titulo'];
    }

    public function suave(): string
    {
        return $this->tema['suave'];
    }

    /** Texto do letreiro grande, ou null quando o cartão leva o logótipo. */
    public function letreiro(): ?string
    {
        return $this->tema['letreiro'];
    }

    public function letreiroTexto(): string
    {
        return $this->tema['letreiro_texto'];
    }

    public function temLetreiro(): bool
    {
        return $this->tema['letreiro'] !== null;
    }

    /** Se o cartão mostra a localização da vaga por baixo do título. */
    public function mostrarLocal(): bool
    {
        return $this->tema['mostrar_local'];
    }
}
