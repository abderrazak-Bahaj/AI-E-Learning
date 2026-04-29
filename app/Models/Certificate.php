<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $certificate_number
 * @property int $student_id
 * @property string $course_id
 * @property string|null $enrollment_id
 * @property string $status
 * @property string|null $certificate_url
 * @property string|null $template_url
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $issue_date
 * @property Carbon|null $generated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'certificate_number',
        'student_id',
        'course_id',
        'enrollment_id',
        'status',
        'certificate_url',
        'template_url',
        'metadata',
        'issue_date',
        'generated_at',
    ];

    public static function generateNumber(): string
    {
        return 'CERT-'.mb_strtoupper(Str::random(10));
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeGenerated(Builder $query): Builder
    {
        return $query->where('status', 'GENERATED');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeForStudent(Builder $query, int|string $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isGenerated(): bool
    {
        return $this->status === 'GENERATED';
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'issue_date' => 'datetime',
            'generated_at' => 'datetime',
        ];
    }
}
