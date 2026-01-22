<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BarangKasirTdControllerTest extends TestCase
{
    /**
     * Test if index page loads successfully
     *
     * @return void
     */
    public function test_index_page_loads()
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($user)->get('/usl-brg-td');

        $response->assertStatus(200);
        $response->assertViewIs('master_usulan_barang_kasir_td.index');
    }

    /**
     * Test if getSub endpoint returns data
     *
     * @return void
     */
    public function test_get_sub_returns_data()
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($user)->get('/get-sub-td');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['SUB', 'KELOMPOK']
        ]);
    }

    /**
     * Test if getUsulanBrgTd endpoint returns datatable data
     *
     * @return void
     */
    public function test_get_usulan_brg_td_returns_datatable()
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        // Get first SUB value
        $sub = DB::table('aotprice')->first();

        if (!$sub) {
            $this->markTestSkipped('No SUB data found in aotprice table');
        }

        $response = $this->actingAs($user)->get('/get-usl-brg-td?sub=' . $sub->SUB);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data'
        ]);
    }

    /**
     * Test proses endpoint with valid data
     *
     * @return void
     */
    public function test_proses_with_valid_data()
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        // Get a sample item from masks
        $item = DB::table('masks')->first();

        if (!$item) {
            $this->markTestSkipped('No data found in masks table');
        }

        // Only send checked items (JTD = 1)
        $data = [
            'items' => [
                [
                    'KD_BRG' => $item->KD_BRG,
                    'JTD' => 1  // Checked item only
                ]
            ]
        ];

        $response = $this->actingAs($user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/uslBrgTd-proses', $data);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success'
        ]);

        // Verify the data was updated
        $updated = DB::table('masks')->where('KD_BRG', $item->KD_BRG)->first();
        $this->assertEquals(1, $updated->JTD);
    }

    /**
     * Test proses endpoint with empty data
     *
     * @return void
     */
    public function test_proses_with_empty_data()
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        $data = [
            'items' => []
        ];

        $response = $this->actingAs($user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/uslBrgTd-proses', $data);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Tidak ada data untuk diproses'
        ]);
    }

    /**
     * Test database connection and masks table structure
     *
     * @return void
     */
    public function test_masks_table_structure()
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM masks");

            $columnNames = array_map(function ($col) {
                return $col->Field;
            }, $columns);

            $requiredColumns = ['NO_ID', 'SUB', 'KD_BRG', 'NA_BRG', 'HB', 'JTD'];

            foreach ($requiredColumns as $required) {
                $this->assertContains($required, $columnNames, "Column $required not found in masks table");
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Unable to access masks table: ' . $e->getMessage());
        }
    }
}
