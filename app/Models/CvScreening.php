<?php

namespace App\Models;

use App\Support\VectorSimilarity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Uma triagem avulsa: o recrutador escreve a descrição da vaga e carrega os CVs
 * que quiser, sem precisar de publicar a vaga nem esperar por candidaturas.
 */
class CvScreening extends Model
{
    use HasFactory;

    /** Máximo de CVs carregados de uma só vez. */
    public const MAX_CVS_PER_UPLOAD = 30;

    /** Máximo de CVs guardados por triagem. */
    public const MAX_CVS_PER_SCREENING = 100;

    /** Tamanho máximo de cada CV, em KB (regra de validação do Laravel). */
    public const MAX_CV_SIZE_KB = 5120;

    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'description',
        'description_vector',
        'description_vector_model',
        'description_vector_generated_at',
    ];

    protected $casts = [
        'description_vector' => 'array',
        'description_vector_generated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function candidates()
    {
        return $this->hasMany(CvScreeningCandidate::class);
    }

    /**
     * O vector da descrição só é comparável com os dos CVs se tiver sido gerado
     * pelo mesmo modelo que o serviço de análise usa hoje.
     */
    public function hasCurrentVector(): bool
    {
        return (bool) $this->description_vector
            && $this->description_vector_model === VectorSimilarity::MODEL_ID;
    }

    public function remainingSlots(): int
    {
        return max(0, self::MAX_CVS_PER_SCREENING - $this->candidates()->count());
    }

    public function storageDirectory(): string
    {
        return 'cv-screenings/' . $this->id;
    }
}
