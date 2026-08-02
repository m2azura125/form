<?php

namespace Tests\Feature\Admin;

use App\Exports\SubmissionsExport;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export(): void
    {
        $this->get('/admin/export')->assertRedirect('/login');
    }

    public function test_admin_can_export_submissions_as_xlsx(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Submission::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/export');

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('content-type')
        );
    }

    public function test_formula_leading_values_are_neutralized_to_prevent_csv_injection(): void
    {
        $submission = Submission::factory()->create([
            'nama' => '=cmd|\'/c calc\'!A1',
            'judul_alat' => '+SUM(1+1)',
        ]);

        $rows = (new SubmissionsExport)->map($submission->fresh());

        $this->assertSame("'=cmd|'/c calc'!A1", $rows[1]);
        $this->assertSame("'+SUM(1+1)", $rows[3]);
    }

    public function test_export_filters_by_month_and_year(): void
    {
        Submission::factory()->create([
            'nama' => 'July Entry',
            'tanggal_pengajuan' => '2026-07-10',
        ]);

        Submission::factory()->create([
            'nama' => 'August Entry',
            'tanggal_pengajuan' => '2026-08-10',
        ]);

        $exportJuly = new SubmissionsExport(['bulan' => '7', 'tahun' => '2026']);
        $queryJuly = $exportJuly->query()->get();

        $this->assertCount(1, $queryJuly);
        $this->assertSame('July Entry', $queryJuly->first()->nama);
    }
}
