<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * País de uma vaga.
 *
 * O portal nasceu só com vagas de Angola e durante muito tempo o "AO" esteve
 * escrito à mão nos dados estruturados de cada vaga. Agora cada vaga diz de que
 * país é, e é daqui que sai o addressCountry do JobPosting.
 */
class Country extends Model
{
    use HasFactory;

    /** Código do país por omissão — o portal é angolano. */
    public const OMISSAO = 'AO';

    private const CHAVE_OMISSAO = 'country_id_omissao';

    protected $fillable = ['name', 'name_en', 'code'];

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    /**
     * O nome no idioma em que a página está a ser vista.
     */
    public function getNomeAttribute(): string
    {
        return app()->getLocale() === 'en' && $this->name_en
            ? $this->name_en
            : $this->name;
    }

    /**
     * A bandeira sai do próprio código: cada letra tem um símbolo indicador
     * regional correspondente, e o par forma a bandeira. Assim não é preciso
     * guardar imagens nem uma coluna com o emoji.
     */
    public function getBandeiraAttribute(): string
    {
        $codigo = strtoupper((string) $this->code);

        if (!preg_match('/^[A-Z]{2}$/', $codigo)) {
            return '';
        }

        return mb_chr(ord($codigo[0]) - 65 + 0x1F1E6, 'UTF-8')
            . mb_chr(ord($codigo[1]) - 65 + 0x1F1E6, 'UTF-8');
    }

    protected static function boot()
    {
        parent::boot();

        $esquecerOmissao = fn () => Cache::forget(self::CHAVE_OMISSAO);

        static::saved($esquecerOmissao);
        static::deleted($esquecerOmissao);
    }

    /**
     * Id de Angola, o país por omissão de tudo o que for publicado sem país.
     *
     * Fica em cache porque é consultado em cada vaga criada e o valor nunca muda.
     * Entre o `git pull` e o `artisan migrate` a tabela ainda não existe no
     * servidor; nessa janela vale 1, que é o id que a migração dá a Angola — e
     * nada fica em cache, para o valor certo entrar assim que a tabela existir.
     */
    public static function idPorOmissao(): int
    {
        try {
            return Cache::rememberForever(
                self::CHAVE_OMISSAO,
                fn () => (int) (static::query()->where('code', self::OMISSAO)->value('id') ?? 1)
            );
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Encontra um país pelo código ISO ou pelo nome, como vier da API.
     */
    public static function porCodigoOuNome(?string $valor): ?self
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z]{2}$/', $valor)) {
            return static::query()->whereRaw('UPPER(code) = ?', [strtoupper($valor)])->first();
        }

        return static::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($valor, 'UTF-8')])
            ->orWhereRaw('LOWER(name_en) = ?', [mb_strtolower($valor, 'UTF-8')])
            ->first();
    }

    /**
     * Lista para os selectores, já no idioma do site e por ordem alfabética —
     * com Angola à cabeça, que é o caso mais comum de quem publica aqui.
     */
    public static function paraSelector()
    {
        return static::query()
            ->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [self::OMISSAO])
            ->orderBy(app()->getLocale() === 'en' ? 'name_en' : 'name')
            ->get();
    }
}
