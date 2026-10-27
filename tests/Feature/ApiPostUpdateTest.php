<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * PUT/PATCH /api/posts/{identificador}.
 *
 * O identificador aceita o id ou o slug — é a mesma rota para os dois, e é
 * o que estes testes guardam: que as duas formas de apontar para o artigo
 * chegam ao mesmo sítio, e que mudar o título não muda o endereço sozinho.
 */
class ApiPostUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * O evento `created` do modelo regenera sempre o slug a partir do título,
     * mesmo que um seja passado ao create() — por isso um slug concreto só se
     * consegue impor depois, com forceFill(). O valor passado ao create() é
     * só para satisfazer a coluna NOT NULL durante o INSERT; é substituído de
     * imediato. Ver a nota no fim do ficheiro sobre /api/post/create.
     */
    private function artigo(array $sobrepor = []): Post
    {
        $slugDesejado = $sobrepor['slug'] ?? null;
        unset($sobrepor['slug']);

        $post = Post::create(array_merge([
            'title' => 'Como escrever um bom currículo',
            'slug' => 'temporario-' . Str::random(8),
            'description' => '<p>Texto original.</p>',
        ], $sobrepor));

        if ($slugDesejado !== null) {
            $post->forceFill(['slug' => $slugDesejado])->save();
        }

        return $post->fresh();
    }

    private function editar(string $identificador, array $corpo)
    {
        return $this->putJson("/api/posts/$identificador", $corpo, ['Accept' => 'application/json']);
    }

    // --------------------------------------------------------- pelo id

    public function test_it_edits_an_article_by_id()
    {
        $post = $this->artigo();

        $this->editar((string) $post->id, ['title' => 'Como escrever um currículo em 2026'])
            ->assertOk()
            ->assertJsonPath('title', 'Como escrever um currículo em 2026');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Como escrever um currículo em 2026',
        ]);
    }

    // ------------------------------------------------------- pelo slug

    public function test_it_edits_an_article_by_slug()
    {
        $post = $this->artigo();
        $slug = $post->fresh()->slug;

        $this->editar($slug, ['description' => '<p>Texto novo.</p>'])
            ->assertOk()
            ->assertJsonPath('description', '<p>Texto novo.</p>');
    }

    public function test_patch_works_the_same_as_put()
    {
        $post = $this->artigo();

        $this->patchJson('/api/posts/' . $post->id, ['title' => 'Título via PATCH'], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('title', 'Título via PATCH');
    }

    // ---------------------------------------------- o slug é o endereço

    /**
     * Mudar o título não pode mudar o slug sozinho — o slug é o endereço do
     * artigo, e trocá-lo às escondidas parte qualquer ligação partilhada.
     */
    public function test_changing_the_title_does_not_change_the_slug()
    {
        $post = $this->artigo();
        $slugOriginal = $post->fresh()->slug;

        $this->editar((string) $post->id, ['title' => 'Um título completamente diferente'])->assertOk();

        $this->assertSame($slugOriginal, $post->fresh()->slug);
    }

    /** Mas se vier `slug` explicitamente, esse é que manda. */
    public function test_an_explicit_slug_is_applied()
    {
        $post = $this->artigo();

        $this->editar((string) $post->id, ['slug' => 'novo-endereco'])
            ->assertOk()
            ->assertJsonPath('slug', 'novo-endereco');

        $this->assertSame('novo-endereco', $post->fresh()->slug);
    }

    /** O slug novo passa por Str::slug: maiúsculas, acentos e espaços saem limpos. */
    public function test_the_new_slug_is_normalised()
    {
        $post = $this->artigo();

        $this->editar((string) $post->id, ['slug' => 'Notícia de Última Hora!'])
            ->assertOk()
            ->assertJsonPath('slug', 'noticia-de-ultima-hora');
    }

    public function test_an_empty_slug_is_rejected()
    {
        $post = $this->artigo();

        $this->editar((string) $post->id, ['slug' => '   '])
            ->assertStatus(422);

        $this->assertNotEquals('', $post->fresh()->slug);
    }

    public function test_a_slug_already_in_use_by_another_article_is_rejected()
    {
        $this->artigo(['title' => 'Primeiro artigo', 'slug' => 'ocupado']);
        $segundo = $this->artigo(['title' => 'Segundo artigo']);

        $this->editar((string) $segundo->id, ['slug' => 'ocupado'])
            ->assertStatus(422)
            ->assertJsonPath('slug', 'ocupado');

        $this->assertNotSame('ocupado', $segundo->fresh()->slug);
    }

    /** Mas o artigo pode "mudar" para o seu próprio slug sem se queixar. */
    public function test_an_article_can_keep_its_own_slug()
    {
        $post = $this->artigo(['slug' => 'o-meu-slug']);

        $this->editar('o-meu-slug', ['slug' => 'o-meu-slug', 'title' => 'Título actualizado'])
            ->assertOk();
    }

    // ------------------------------------------------------------- a cache

    /**
     * O artigo fica em cache pelo slug. Quando o slug muda, a cópia presa à
     * morada antiga tem de sair, senão fica lá até expirar sozinha (24h).
     */
    public function test_renaming_the_slug_forgets_the_old_cache_entry()
    {
        $post = $this->artigo(['slug' => 'endereco-antigo']);
        Cache::get('post_endereco-antigo'); // aquece o caminho, sem forçar nada

        $this->editar((string) $post->id, ['slug' => 'endereco-novo'])->assertOk();

        $this->assertNull(Cache::get('post_endereco-antigo'));
    }

    // -------------------------------------------------------------- o resto

    public function test_an_article_that_does_not_exist_is_a_404()
    {
        $this->editar('999999', ['title' => 'x'])->assertStatus(404);
        $this->editar('nao-existe', ['title' => 'x'])->assertStatus(404);
    }

    public function test_an_empty_body_is_rejected_with_a_clear_message()
    {
        $post = $this->artigo();

        $this->editar((string) $post->id, [])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    /** Campos fora da lista (como o id) são ignorados, não rejeitados. */
    public function test_unknown_fields_are_simply_ignored()
    {
        $post = $this->artigo();

        $this->editar((string) $post->id, ['id' => 999999, 'title' => 'Título válido', 'autor' => 'Alguém'])
            ->assertOk();

        $this->assertSame($post->id, $post->fresh()->id);
    }

    /**
     * NÃO funciona — e já não funcionava antes desta tarefa. A coluna
     * `posts.slug` é NOT NULL sem valor por omissão, e Post::create() nesta
     * rota nunca recebe slug (só é gerado DEPOIS de o registo já ter sido
     * inserido, no evento `created`). Em MySQL sem strict mode isto passa
     * silenciosamente a inserir uma string vazia; em SQLite, e em MySQL com
     * strict mode (a omissão desde o MySQL 5.7), falha. Não é desta tarefa
     * — fica documentado, não corrigido.
     */
    public function test_creating_an_article_is_broken_independently_of_this_task()
    {
        $resposta = $this->postJson('/api/post/create', [
            'title' => 'Artigo criado pela API',
            'description' => '<p>Texto.</p>',
        ]);

        $this->assertSame(500, $resposta->getStatusCode());
    }

    /** Um slug numérico — um artigo chamado só "2026" — continua a resolver-se por slug. */
    public function test_a_purely_numeric_slug_still_resolves_as_a_slug_when_no_id_matches()
    {
        $post = $this->artigo(['title' => 'Resumo de 2026']);
        $post->forceFill(['slug' => '999999'])->save();

        $this->assertNull(Post::find(999999));

        $this->editar('999999', ['title' => 'Resumo actualizado de 2026'])
            ->assertOk()
            ->assertJsonPath('title', 'Resumo actualizado de 2026');
    }
}
