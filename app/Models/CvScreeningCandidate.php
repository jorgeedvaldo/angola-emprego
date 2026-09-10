<?php

namespace App\Models;

use App\Support\VectorSimilarity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CvScreeningCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_screening_id',
        'path',
        'original_name',
        'cv_text',
        'cv_vector',
        'cv_vector_model',
        'cv_analyzed_at',
    ];

    protected $casts = [
        'cv_vector' => 'array',
        'cv_analyzed_at' => 'datetime',
    ];

    public function screening()
    {
        return $this->belongsTo(CvScreening::class, 'cv_screening_id');
    }

    public function hasCurrentVector(): bool
    {
        return (bool) $this->cv_vector && $this->cv_vector_model === VectorSimilarity::MODEL_ID;
    }

    /**
     * Nome legível do candidato: o serviço de análise devolve o texto do CV, mas
     * não o nome, por isso usamos o nome do ficheiro como etiqueta da linha.
     */
    public function label(): string
    {
        $name = $this->original_name ?: basename($this->path);

        return Str::limit(pathinfo($name, PATHINFO_FILENAME) ?: $name, 60);
    }
}
