use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['job_id']);

            // Add new foreign key to jobs_panel
            $table->foreign('job_id')->references('id')->on('jobs_panel')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('your_table_name', function (Blueprint $table) {
            // Revert back to referencing jobs table
            $table->dropForeign(['job_id']);
            $table->foreign('job_id')->references('id')->on('jobs')->cascadeOnDelete();
        });
    }
};