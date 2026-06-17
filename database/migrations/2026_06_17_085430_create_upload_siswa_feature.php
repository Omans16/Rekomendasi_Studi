<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $hasilTable = 'hasil_prediksis';

    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'nisn')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nisn')->nullable()->unique();
            });
        }

        if (!Schema::hasTable('upload_siswa_batches')) {
            Schema::create('upload_siswa_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

                $table->string('original_filename')->nullable();
                $table->string('stored_filename')->nullable();

                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('valid_rows')->default(0);
                $table->unsignedInteger('failed_rows')->default(0);

                $table->unsignedInteger('linked_user_count')->default(0);
                $table->unsignedInteger('unlinked_user_count')->default(0);

                $table->unsignedInteger('prediksi_success_count')->default(0);
                $table->unsignedInteger('rekomendasi_success_count')->default(0);

                $table->string('status')->default('processing');
                $table->json('summary')->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable($this->hasilTable)) {
            Schema::create($this->hasilTable, function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedBigInteger('upload_batch_id')->nullable();

                $table->string('nisn')->nullable()->index();
                $table->string('nama_siswa')->nullable();
                $table->string('jurusan_smk')->nullable();
                $table->string('jurusan_smk_lengkap')->nullable();

                $table->decimal('rata_pai', 6, 2)->nullable();
                $table->decimal('rata_ppkn', 6, 2)->nullable();
                $table->decimal('rata_ind', 6, 2)->nullable();
                $table->decimal('rata_mtk', 6, 2)->nullable();
                $table->decimal('rata_ing', 6, 2)->nullable();
                $table->decimal('ukk', 6, 2)->nullable();

                $table->decimal('nilai_max', 6, 2)->nullable();
                $table->decimal('nilai_min', 6, 2)->nullable();
                $table->decimal('std_nilai', 8, 4)->nullable();

                $table->tinyInteger('prediksi_rf')->nullable();
                $table->string('status_rf')->nullable();
                $table->decimal('probabilitas_studi_lanjut', 8, 6)->nullable();
                $table->string('kategori_probabilitas')->nullable();

                $table->text('pesan')->nullable();
                $table->text('narasi_rekomendasi')->nullable();

                $table->json('profil_siswa')->nullable();
                $table->json('alumni_terdekat')->nullable();
                $table->json('rekomendasi_final')->nullable();
                $table->json('kualitas_rekomendasi')->nullable();
                $table->json('raw_response')->nullable();

                $table->string('sumber')->default('manual');
                $table->text('error_message')->nullable();

                $table->timestamps();
            });
        } else {
            $this->addColumnIfMissing($this->hasilTable, 'user_id', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            });

            $this->addColumnIfMissing($this->hasilTable, 'upload_batch_id', function (Blueprint $table) {
                $table->unsignedBigInteger('upload_batch_id')->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'nisn', function (Blueprint $table) {
                $table->string('nisn')->nullable()->index();
            });

            $this->addColumnIfMissing($this->hasilTable, 'nama_siswa', function (Blueprint $table) {
                $table->string('nama_siswa')->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'jurusan_smk', function (Blueprint $table) {
                $table->string('jurusan_smk')->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'jurusan_smk_lengkap', function (Blueprint $table) {
                $table->string('jurusan_smk_lengkap')->nullable();
            });

            foreach (['rata_pai', 'rata_ppkn', 'rata_ind', 'rata_mtk', 'rata_ing', 'ukk', 'nilai_max', 'nilai_min'] as $column) {
                $this->addColumnIfMissing($this->hasilTable, $column, function (Blueprint $table) use ($column) {
                    $table->decimal($column, 6, 2)->nullable();
                });
            }

            $this->addColumnIfMissing($this->hasilTable, 'std_nilai', function (Blueprint $table) {
                $table->decimal('std_nilai', 8, 4)->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'prediksi_rf', function (Blueprint $table) {
                $table->tinyInteger('prediksi_rf')->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'status_rf', function (Blueprint $table) {
                $table->string('status_rf')->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'probabilitas_studi_lanjut', function (Blueprint $table) {
                $table->decimal('probabilitas_studi_lanjut', 8, 6)->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'kategori_probabilitas', function (Blueprint $table) {
                $table->string('kategori_probabilitas')->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'pesan', function (Blueprint $table) {
                $table->text('pesan')->nullable();
            });

            $this->addColumnIfMissing($this->hasilTable, 'narasi_rekomendasi', function (Blueprint $table) {
                $table->text('narasi_rekomendasi')->nullable();
            });

            foreach (['profil_siswa', 'alumni_terdekat', 'rekomendasi_final', 'kualitas_rekomendasi', 'raw_response'] as $column) {
                $this->addColumnIfMissing($this->hasilTable, $column, function (Blueprint $table) use ($column) {
                    $table->json($column)->nullable();
                });
            }

            $this->addColumnIfMissing($this->hasilTable, 'sumber', function (Blueprint $table) {
                $table->string('sumber')->default('manual');
            });

            $this->addColumnIfMissing($this->hasilTable, 'error_message', function (Blueprint $table) {
                $table->text('error_message')->nullable();
            });
        }

        if (!Schema::hasTable('upload_siswa_rows')) {
            Schema::create('upload_siswa_rows', function (Blueprint $table) {
                $table->id();

                $table->foreignId('upload_siswa_batch_id')
                    ->constrained('upload_siswa_batches')
                    ->cascadeOnDelete();

                $table->unsignedInteger('row_number')->nullable();

                $table->string('nisn')->nullable()->index();
                $table->string('nama_siswa')->nullable();
                $table->string('jurusan_smk')->nullable();

                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('hasil_prediksi_id')->nullable()->constrained($this->hasilTable)->nullOnDelete();

                $table->string('status')->default('pending');
                $table->text('message')->nullable();

                $table->json('payload')->nullable();
                $table->json('response')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_siswa_rows');
        Schema::dropIfExists('upload_siswa_batches');
    }

    private function addColumnIfMissing(string $tableName, string $columnName, callable $callback): void
    {
        if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($callback) {
                $callback($table);
            });
        }
    }
};