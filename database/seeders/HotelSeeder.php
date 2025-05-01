<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Hotel::create([
      'nombre' => 'Hotel Ejemplo',
      'nit' => '12345678-9',
      'nombre_fiscal' => 'Hotel Ejemplo S.A.',
      'direccion' => 'Calle Principal #123',
      'simbolo_moneda' => 'Q.',
      'logo' => null
    ]);
  }
}
