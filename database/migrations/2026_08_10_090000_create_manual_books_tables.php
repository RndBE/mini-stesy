<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('manual_books')) {
            Schema::create('manual_books', function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';

                $table->bigIncrements('id');
                $table->string('judul', 150);
                $table->text('deskripsi')->nullable();

                // Path relatif di disk `local` (storage/app/private), bukan di public.
                $table->string('file_path', 255);
                $table->string('file_name', 255);
                $table->string('file_mime', 100)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();

                // all | instansi | role | selected
                $table->string('visibility', 20)->default('all');
                $table->unsignedInteger('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('uploaded_by')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'urutan'], 'idx_manual_books_aktif_urutan');
                $table->index('uploaded_by', 'idx_manual_books_uploader');

                $table->foreign('uploaded_by', 'fk_manual_books_uploader')
                    ->references('id_user')
                    ->on('t_user')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasTable('manual_book_targets')) {
            Schema::create('manual_book_targets', function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';

                $table->bigIncrements('id');
                $table->unsignedBigInteger('manual_book_id');

                // user | role | instansi — mengikuti kolom visibility di manual_books.
                $table->string('target_type', 20);

                // Disimpan sebagai string karena tipe acuannya berbeda-beda
                // (t_user.id_user integer, roles.role_name string, instansi.id integer).
                $table->string('target_id', 100);
                $table->timestamps();

                $table->unique(['manual_book_id', 'target_type', 'target_id'], 'uq_manual_book_targets');
                $table->index(['target_type', 'target_id'], 'idx_manual_book_targets_target');

                $table->foreign('manual_book_id', 'fk_manual_book_targets_book')
                    ->references('id')
                    ->on('manual_books')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('manual_book_targets')) {
            Schema::table('manual_book_targets', function (Blueprint $table) {
                $table->dropForeign('fk_manual_book_targets_book');
            });

            Schema::dropIfExists('manual_book_targets');
        }

        if (Schema::hasTable('manual_books')) {
            Schema::table('manual_books', function (Blueprint $table) {
                $table->dropForeign('fk_manual_books_uploader');
            });

            Schema::dropIfExists('manual_books');
        }
    }
};
