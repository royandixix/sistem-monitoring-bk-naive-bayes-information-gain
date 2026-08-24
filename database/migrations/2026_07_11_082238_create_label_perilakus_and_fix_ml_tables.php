<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('label_perilakus')) {
            Schema::create('label_perilakus', function (Blueprint $table): void {
                $table->id();

                $table->foreignId('siswa_id')
                    ->constrained('siswas')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('tahun_ajaran', 20);

                $table->enum('semester', [
                    'Ganjil',
                    'Genap',
                ]);

                $table->enum('label_aktual', [
                    'Baik',
                    'Butuh Perhatian',
                    'Bermasalah',
                ]);

                $table->text('catatan')->nullable();

                $table->foreignId('labeled_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->unique(
                    [
                        'siswa_id',
                        'tahun_ajaran',
                        'semester',
                    ],
                    'label_perilakus_siswa_periode_unique'
                );
            });
        }

        Schema::table('klasifikasis', function (Blueprint $table): void {
            if (! Schema::hasColumn('klasifikasis', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 20)
                    ->nullable()
                    ->after('siswa_id');
            }

            if (! Schema::hasColumn('klasifikasis', 'semester')) {
                $table->enum('semester', [
                    'Ganjil',
                    'Genap',
                ])
                    ->nullable()
                    ->after('tahun_ajaran');
            }
        });

        Schema::table('klasifikasis', function (Blueprint $table): void {
            $table->unique(
                [
                    'siswa_id',
                    'tahun_ajaran',
                    'semester',
                ],
                'klasifikasis_siswa_periode_unique'
            );
        });

        Schema::table('evaluasi_models', function (Blueprint $table): void {
            if (! Schema::hasColumn('evaluasi_models', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 20)
                    ->nullable()
                    ->after('metode');
            }

            if (! Schema::hasColumn('evaluasi_models', 'semester')) {
                $table->enum('semester', [
                    'Ganjil',
                    'Genap',
                ])
                    ->nullable()
                    ->after('tahun_ajaran');
            }

            if (! Schema::hasColumn('evaluasi_models', 'training_ratio')) {
                $table->decimal('training_ratio', 4, 3)
                    ->nullable()
                    ->after('jumlah_data_testing');
            }

            if (! Schema::hasColumn('evaluasi_models', 'random_seed')) {
                $table->unsignedInteger('random_seed')
                    ->default(42)
                    ->after('training_ratio');
            }

            if (! Schema::hasColumn('evaluasi_models', 'selected_features')) {
                $table->json('selected_features')
                    ->nullable()
                    ->after('confusion_matrix');
            }
        });

        Schema::table('information_gain_results', function (Blueprint $table): void {
            if (! Schema::hasColumn('information_gain_results', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 20)
                    ->nullable()
                    ->after('id');
            }

            if (! Schema::hasColumn('information_gain_results', 'semester')) {
                $table->enum('semester', [
                    'Ganjil',
                    'Genap',
                ])
                    ->nullable()
                    ->after('tahun_ajaran');
            }

            if (! Schema::hasColumn('information_gain_results', 'random_seed')) {
                $table->unsignedInteger('random_seed')
                    ->default(42)
                    ->after('ranking');
            }
        });
    }

    public function down(): void
    {
        Schema::table('klasifikasis', function (Blueprint $table): void {
            $table->dropUnique('klasifikasis_siswa_periode_unique');

            if (Schema::hasColumn('klasifikasis', 'semester')) {
                $table->dropColumn('semester');
            }

            if (Schema::hasColumn('klasifikasis', 'tahun_ajaran')) {
                $table->dropColumn('tahun_ajaran');
            }
        });

        Schema::table('evaluasi_models', function (Blueprint $table): void {
            $columns = [
                'tahun_ajaran',
                'semester',
                'training_ratio',
                'random_seed',
                'selected_features',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('evaluasi_models', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('information_gain_results', function (Blueprint $table): void {
            $columns = [
                'tahun_ajaran',
                'semester',
                'random_seed',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('information_gain_results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('label_perilakus');
    }
};