<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /** Os campos que esta API deixa editar num artigo. */
    private const CAMPOS_EDITAVEIS = ['title', 'slug', 'description', 'image'];

    public function store(Request $request)
    {
        $post = Post::create($request->all());
        return response()->json($post);
    }

    /**
     * Edita um artigo já publicado.
     *
     * O {identificador} aceita o id (ex.: 42) ou o slug (ex.:
     * como-escrever-um-bom-curriculo) — é a mesma rota para os dois.
     *
     * Mudar o `title` NÃO muda o `slug` sozinho: o slug é o endereço do
     * artigo, e trocá-lo às escondidas partia qualquer ligação já partilhada.
     * Só muda se vier `slug` explicitamente no pedido.
     */
    public function update(Request $request, string $identificador)
    {
        $post = $this->encontrar($identificador);

        if (!$post) {
            return response()->json([
                'message' => 'Artigo não encontrado.',
                'identificador' => $identificador,
            ], 404);
        }

        $dados = $request->only(self::CAMPOS_EDITAVEIS);

        if ($dados === []) {
            return response()->json([
                'message' => 'Nada para actualizar. Envie pelo menos um destes campos: '
                    . implode(', ', self::CAMPOS_EDITAVEIS) . '.',
            ], 422);
        }

        if (array_key_exists('slug', $dados)) {
            $erro = $this->validarNovoSlug($post, $dados['slug']);

            if ($erro) {
                return $erro;
            }

            $dados['slug'] = Str::slug((string) $dados['slug']);
        }

        $post->update($dados);

        return response()->json($post->fresh());
    }

    /**
     * @return \Illuminate\Http\JsonResponse|null  null quando o slug é válido
     */
    private function validarNovoSlug(Post $post, $slugProposto)
    {
        $slug = Str::slug((string) $slugProposto);

        if ($slug === '') {
            return response()->json([
                'message' => 'O slug não pode ficar vazio.',
                'slug' => $slugProposto,
            ], 422);
        }

        $emUso = Post::where('slug', $slug)->where('id', '!=', $post->id)->exists();

        if ($emUso) {
            return response()->json([
                'message' => 'Já existe um artigo com este slug.',
                'slug' => $slug,
            ], 422);
        }

        return null;
    }

    /**
     * Um identificador todo em dígitos é tentado primeiro como id; só cai
     * para slug se não encontrar nada — assim um slug que por acaso seja só
     * números (ex.: um artigo chamado "2026") continua a funcionar.
     */
    private function encontrar(string $identificador): ?Post
    {
        if (ctype_digit($identificador) && ($post = Post::find((int) $identificador))) {
            return $post;
        }

        return Post::where('slug', $identificador)->first();
    }
}
