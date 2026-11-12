<?php

use App\Support\AiPersonaScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah_ai_personas', function (Blueprint $table) {
            $table->string('scope')->default(AiPersonaScope::CHAT_ORANGTUA)->after('sekolah_id');
            $table->string('name')->default('Asisten PAUD')->after('scope');
            $table->string('role_title')->nullable()->after('name');
            $table->text('description')->nullable()->after('role_title');
            $table->string('gender', 20)->nullable()->after('description');
            $table->unsignedTinyInteger('age')->nullable()->after('gender');
            $table->string('dialog_language')->default('Bahasa Indonesia')->after('age');
            $table->text('personality_traits')->nullable()->after('dialog_language');
            $table->text('behavior_guidelines')->nullable()->after('communication_style');
            $table->text('background')->nullable()->after('behavior_guidelines');
        });

        if (Schema::hasColumn('sekolah_ai_personas', 'assistant_name')) {
            $rows = DB::table('sekolah_ai_personas')->get();

            foreach ($rows as $row) {
                $guidelines = collect([
                    $row->greeting_style ?? null,
                    $row->boundaries ?? null,
                    $row->custom_instructions ?? null,
                ])->filter()->implode("\n\n");

                DB::table('sekolah_ai_personas')
                    ->where('id', $row->id)
                    ->update([
                        'scope' => AiPersonaScope::CHAT_ORANGTUA,
                        'name' => $row->assistant_name ?: 'Asisten PAUD',
                        'role_title' => AiPersonaScope::defaultRoleTitle(AiPersonaScope::CHAT_ORANGTUA),
                        'personality_traits' => $row->personality,
                        'behavior_guidelines' => $guidelines !== '' ? $guidelines : null,
                    ]);
            }

            Schema::table('sekolah_ai_personas', function (Blueprint $table) {
                $table->dropForeign(['sekolah_id']);
                $table->dropUnique(['sekolah_id']);
                $table->dropColumn([
                    'assistant_name',
                    'personality',
                    'greeting_style',
                    'boundaries',
                    'custom_instructions',
                ]);
            });
        }

        Schema::table('sekolah_ai_personas', function (Blueprint $table) {
            $table->unique(['sekolah_id', 'scope']);
            $table->index('scope');
            $table->foreign('sekolah_id')->references('id')->on('sekolahs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sekolah_ai_personas', function (Blueprint $table) {
            $table->dropUnique(['sekolah_id', 'scope']);
            $table->dropIndex(['scope']);
        });

        Schema::table('sekolah_ai_personas', function (Blueprint $table) {
            $table->string('assistant_name')->default('Asisten PAUD');
            $table->text('personality')->nullable();
            $table->text('greeting_style')->nullable();
            $table->text('boundaries')->nullable();
            $table->text('custom_instructions')->nullable();
        });

        $rows = DB::table('sekolah_ai_personas')->get();
        foreach ($rows as $row) {
            if ($row->scope !== AiPersonaScope::CHAT_ORANGTUA) {
                DB::table('sekolah_ai_personas')->where('id', $row->id)->delete();

                continue;
            }

            DB::table('sekolah_ai_personas')
                ->where('id', $row->id)
                ->update([
                    'assistant_name' => $row->name,
                    'personality' => $row->personality_traits,
                    'custom_instructions' => $row->behavior_guidelines,
                ]);
        }

        Schema::table('sekolah_ai_personas', function (Blueprint $table) {
            $table->dropColumn([
                'scope',
                'name',
                'role_title',
                'description',
                'gender',
                'age',
                'dialog_language',
                'personality_traits',
                'behavior_guidelines',
                'background',
            ]);
            $table->unique('sekolah_id');
        });
    }
};
