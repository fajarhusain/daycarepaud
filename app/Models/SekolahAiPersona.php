<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use App\Support\AiPersonaScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SekolahAiPersona extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    public const GENDER_PEREMPUAN = 'perempuan';

    public const GENDER_LAKI_LAKI = 'laki_laki';

    public const GENDER_NETRAL = 'netral';

    protected $fillable = [
        'sekolah_id',
        'scope',
        'name',
        'role_title',
        'description',
        'gender',
        'age',
        'dialog_language',
        'personality_traits',
        'communication_style',
        'behavior_guidelines',
        'background',
        'is_active',
        'ai_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'age' => 'integer',
            'ai_generated_at' => 'datetime',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public static function forScope(int $sekolahId, string $scope): ?self
    {
        return self::query()
            ->where('sekolah_id', $sekolahId)
            ->where('scope', $scope)
            ->first();
    }

    public function genderLabel(): string
    {
        return match ($this->gender) {
            self::GENDER_PEREMPUAN => 'Perempuan',
            self::GENDER_LAKI_LAKI => 'Laki-laki',
            self::GENDER_NETRAL => 'Netral',
            default => 'Tidak disebutkan',
        };
    }

    public static function resolveActivePrompt(int $sekolahId, string $scope, string $sekolahName): ?string
    {
        $persona = self::forScope($sekolahId, $scope);

        if ($persona === null || ! $persona->is_active) {
            return null;
        }

        $prompt = $persona->buildPromptSection($sekolahName);

        return $prompt !== '' ? $prompt : null;
    }

    public function buildPromptSection(string $sekolahName): string
    {
        if (! $this->is_active) {
            return '';
        }

        $lines = [];
        $name = trim($this->name) ?: AiPersonaScope::defaultName($this->scope);
        $roleTitle = trim((string) $this->role_title) ?: AiPersonaScope::defaultRoleTitle($this->scope);

        $lines[] = "Kamu adalah {$name}, {$roleTitle} di {$sekolahName}.";
        $lines[] = '';
        $lines[] = 'IDENTITAS:';

        if (filled($this->description)) {
            $lines[] = '- Deskripsi: '.trim($this->description);
        }
        if (filled($this->gender)) {
            $lines[] = '- Jenis kelamin: '.$this->genderLabel();
        }
        if ($this->age !== null) {
            $lines[] = '- Usia: '.$this->age.' tahun';
        }
        $lines[] = '- Bahasa dialog: '.trim($this->dialog_language ?: 'Bahasa Indonesia');

        $lines[] = '';
        $lines[] = 'KARAKTER:';

        if (filled($this->personality_traits)) {
            $lines[] = '- Kepribadian: '.trim($this->personality_traits);
        }
        if (filled($this->communication_style)) {
            $lines[] = '- Gaya komunikasi: '.trim($this->communication_style);
        }
        if (filled($this->behavior_guidelines)) {
            $lines[] = '- Panduan perilaku: '.trim($this->behavior_guidelines);
        }

        if (filled($this->background)) {
            $lines[] = '';
            $lines[] = 'LATAR BELAKANG:';
            $lines[] = trim($this->background);
        }

        if ($this->scope === AiPersonaScope::CHAT_ORANGTUA) {
            $lines[] = '';
            $lines[] = 'ATURAN GAYA PERSONA:';
            $lines[] = '- Jawab singkat dan natural seperti chat WhatsApp (1-3 kalimat untuk sapaan sederhana).';
            $lines[] = '- Sapa orang tua dengan "Ayah/Bunda", jangan "Bu/Ibu/Bapak" atau menebak gender dari nama.';
            $lines[] = '- Jangan mengulang frasa yang sama dalam satu balasan.';
            $lines[] = '- Persona memengaruhi nada, bukan skrip yang harus dibaca kata per kata.';
        }

        return implode("\n", $lines);
    }
}
