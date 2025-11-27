@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Penjualan Baru</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Penjualan Baru</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form method="GET" action="{{ route('get-penjualanbaru-report') }}" id="penjualanBaruForm">
									@csrf
									<div class="form-group">
										<div class="row align-items-end">
											<div class="col-md-2 mb-2">
												<label for="cbg">Cabang <span class="text-danger">*</span></label>
												<select name="cbg" id="cbg" class="form-control" required>
													<option value="">Pilih Cabang</option>
													@foreach ($cbg as $cabang)
														<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
															{{ $cabang->CBG }}
														</option>
													@endforeach
												</select>
											</div>

											<div class="col-md-2 mb-2">
												<label for="periode">Periode</label>
												<select name="periode" id="periode" class="form-control">
													<option value="">Semua Periode</option>
													@foreach ($per as $period)
														<option value="{{ $period->PERIO ?? $period->PERIO }}"
															{{ session()->get('filter_periode') == ($period->PERIODE ?? $period->PERID) ? 'selected' : '' }}>
															{{ $period->PERIO ?? $period->PERIO }}
														</option>
													@endforeach
												</select>
											</div>

											<div class="col-md-2 mb-2">
												<label for="tgl1">Tanggal Mulai</label>
												<input type="date" name="tgl1" id="tgl1" class="form-control" value="{{ session()->get('filter_tgl1') }}">
											</div>

											<div class="col-md-2 mb-2">
												<label for="tgl2">Tanggal Akhir</label>
												<input type="date" name="tgl2" id="tgl2" class="form-control" value="{{ session()->get('filter_tgl2') }}">
											</div>

											<div class="col-md-2 mb-2">
												<label for="sup1">Supplier Awal</label>
												<input type="text" name="sup1" id="sup1" class="form-control" maxlength="20" value="{{ session()->get('filter_sup1') }}"
													placeholder="Supplier awal">
											</div>

											<div class="col-md-2 mb-2">
												<label for="sup2">Supplier Akhir</label>
												<input type="text" name="sup2" id="sup2" class="form-control" maxlength="20" value="{{ session()->get('filter_sup2') }}"
													placeholder="Supplier akhir">
											</div>
										</div>

										<div class="row align-items-end">
											<div class="col-md-2 mb-2">
												<label for="sub1">Sub Item Awal</label>
												<input type="text" name="sub1" id="sub1" class="form-control" maxlength="20" value="{{ session()->get('filter_sub1') }}"
													placeholder="Sub item awal">
											</div>

											<div class="col-md-2 mb-2">
												<label for="sub2">Sub Item Akhir</label>
												<input type="text" name="sub2" id="sub2" class="form-control" maxlength="20" value="{{ session()->get('filter_sub2') }}"
													placeholder="Sub item akhir">
											</div>

											<div class="col-md-2 mb-2">
												<label for="kodec1">Kode Customer Awal</label>
												<input type="text" name="kodec1" id="kodec1" class="form-control" maxlength="20" value="{{ session()->get('filter_kodec1') }}"
													placeholder="Kode customer awal">
											</div>

											<div class="col-md-2 mb-2">
												<label for="kodec2">Kode Customer Akhir</label>
												<input type="text" name="kodec2" id="kodec2" class="form-control" maxlength="20" value="{{ session()->get('filter_kodec2') }}"
													placeholder="Kode customer akhir">
											</div>

											<div class="col-md-2 mb-2">
												<label for="kitir1">Nomor Kitir Awal</label>
												<input type="text" name="kitir1" id="kitir1" class="form-control" maxlength="20"
													value="{{ session()->get('filter_kitir1') }}" placeholder="Nomor kitir awal">
											</div>

											<div class="col-md-2 mb-2">
												<label for="kitir2">Nomor Kitir Akhir</label>
												<input type="text" name="kitir2" id="kitir2" class="form-control" maxlength="20"
													value="{{ session()->get('filter_kitir2') }}" placeholder="Nomor kitir akhir">
											</div>
										</div>

										<div class="row align-items-center">
											<div class="col-md-3 mb-2">
												<div class="form-check mt-4">
													<input type="checkbox" name="group_detail" id="group_detail" class="form-check-input" value="1"
														{{ session()->get('filter_group_detail') ? 'checked' : '' }}>
													<label for="group_detail" class="form-check-label">Group Detail (per No Bukti & Barang)</label>
												</div>
											</div>

											<div class="col-md-9 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak"
													formaction="{{ route('jasper-penjualanbaru-report') }}" formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Export
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }}
														@if (session()->get('filter_periode'))
															| Periode: {{ session()->get('filter_periode') }}
														@endif
														@if (session()->get('filter_tgl1') && session()->get('filter_tgl2'))
															| Tanggal: {{ session()->get('filter_tgl1') }} s/d {{ session()->get('filter_tgl2') }}
														@endif
														@if (session()->get('filter_sup1') || session()->get('filter_sup2'))
															| Supplier: {{ session()->get('filter_sup1') ?: '-' }} s/d {{ session()->get('filter_sup2') ?: '-' }}
														@endif
														@if (session()->get('filter_sub1') || session()->get('filter_sub2'))
															| Sub Item: {{ session()->get('filter_sub1') ?: '-' }} s/d {{ session()->get('filter_sub2') ?: '-' }}
														@endif
														@if (session()->get('filter_kodec1') || session()->get('filter_kodec2'))
															| Kode Customer: {{ session()->get('filter_kodec1') ?: '-' }} s/d {{ session()->get('filter_kodec2') ?: '-' }}
														@endif
														@if (session()->get('filter_kitir1') || session()->get('filter_kitir2'))
															| Nomor Kitir: {{ session()->get('filter_kitir1') ?: '-' }} s/d {{ session()->get('filter_kitir2') ?: '-' }}
														@endif
														@if (session()->get('filter_group_detail'))
															| Group Detail: Ya
														@else
															| Group Detail: Tidak
														@endif
													</div>
												</div>
											</div>
										@endif

										@if (isset($error))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-danger">
														<i class="fas fa-exclamation-triangle mr-2"></i>
														<strong>Error:</strong> {{ $error }}
													</div>
												</div>
											</div>
										@endif
									</div>
								</form>

								<div class="report-content mt-3">
									@if ($hasilPenjualanBaru && count($hasilPenjualanBaru) > 0)
										<div class="table-responsive">
											<table id="penjualanBaruTable" class="table-hover table-striped table-bordered compact table" style="width:100%">
												<thead>
													<tr>
														<th>No Bukti (Kitir)</th>
														<th>Tanggal</th>
														<th>Supp</th>
														<th>Sub Item</th>
														<th>Nama Barang</th>
														<th>LPH</th>
														<th>Qty</th>
														<th>Harga</th>
														<th>H_VIP</th>
														<th>Diskon</th>
														<th>Disc</th>
														<th>PPN</th>
														<th>NPPN</th>
														<th>DPP</th>
														<th>Total</th>
													</tr>
												</thead>
												<tbody>
													@foreach ($hasilPenjualanBaru as $item)
														<tr>
															<td class="text-center">{{ $item['NO_BUKTI'] }}</td>
															<td class="text-center">{{ \Carbon\Carbon::parse($item['TGL'])->format('d-m-Y') }}</td>
															<td class="text-center">{{ $item['SUPP'] }}</td>
															<td class="text-center">{{ $item['SUB'] ?? '-' }}</td>
															<td>{{ $item['NA_BRG'] }}</td>
															<td class="text-center">{{ $item['LPH'] }}</td>
															<td class="text-right">{{ number_format($item['QTY'], 2, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item['HARGA'], 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item['HARGA_VIP'], 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item['DISKON'], 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item['DISC'], 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item['PPN'] ?? 0, 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item['NPPN'], 0, ',', '.') }}</td>
															<td class="text-right">{{ number_format($item['DPP'], 0, ',', '.') }}</td>
															<td class="font-weight-bold text-right">{{ number_format($item['TOTAL'], 0, ',', '.') }}</td>
														</tr>
													@endforeach
												</tbody>
												<tfoot>
													<tr>
														<th colspan="6" class="text-right">Total:</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('QTY'), 2, ',', '.') }}
														</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('HARGA'), 0, ',', '.') }}
														</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('HARGA_VIP'), 0, ',', '.') }}
														</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('DISKON'), 0, ',', '.') }}
														</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('DISC'), 0, ',', '.') }}
														</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('PPN'), 0, ',', '.') }}
														</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('NPPN'), 0, ',', '.') }}
														</th>
														<th class="text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('DPP'), 0, ',', '.') }}
														</th>
														<th class="font-weight-bold text-right">
															{{ number_format(collect($hasilPenjualanBaru)->sum('TOTAL'), 0, ',', '.') }}
														</th>
													</tr>
												</tfoot>
											</table>
										</div>
										<div class="alert alert-success mt-3">
											<i class="fas fa-info-circle mr-2"></i>
											<strong>Informasi Data:</strong><br>
											- Total data ditemukan: <strong>{{ count($hasilPenjualanBaru) }}</strong> baris<br>
											- Data diurutkan berdasarkan No Bukti dan Kode Barang
										</div>
									@elseif(request()->has('action') && request()->get('action') == 'filter')
										<div class="alert alert-warning text-center">
											<i class="fas fa-exclamation-triangle mr-2"></i>
											Tidak ada data penjualan baru ditemukan dengan kriteria filter:
											<br><strong>Cabang: {{ request()->get('cbg') }}</strong>
											@if (request()->get('periode'))
												<br><strong>Periode: {{ request()->get('periode') }}</strong>
											@endif
											@if (request()->get('tgl1') && request()->get('tgl2'))
												<br><strong>Tanggal: {{ request()->get('tgl1') }} s/d {{ request()->get('tgl2') }}</strong>
											@endif
											<br><small class="text-muted mt-2">Pastikan filter sudah benar dan terdapat data penjualan baru untuk kriteria tersebut.</small>
										</div>
									@else
										<div class="alert alert-info text-center">
											<i class="fas fa-info-circle mr-2"></i>
											Silakan pilih cabang untuk menampilkan data penjualan baru.
											<br><small class="text-muted mt-2">Filter lainnya bersifat opsional. Kosongkan untuk menampilkan semua data.</small>
										</div>
									@endif
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal dan komponen lain jika diperlukan bisa ditambahkan di sini -->

@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Inisialisasi DataTable dengan fitur export dan styling
			$('#penjualanBaruTable').DataTable({
				dom: 'Bfrtip',
				buttons: [
					'copy', 'excel', 'csv', 'pdf', 'print'
				],
				language: {
					url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
				},
				order: [
					[0, 'asc'],
					[4, 'asc']
				], // Urutkan No Bukti, Nama Barang
				pageLength: 25,
				scrollX: true,
				columnDefs: [{
						className: 'dt-center',
						targets: [0, 1, 2, 3, 5]
					}, // No Bukti, Tanggal, Supp, Sub Item, LPH
					{
						className: 'dt-right',
						targets: [6, 7, 8, 9, 10, 11, 12, 13, 14]
					}, // Qty, Harga, H_VIP, Diskon, Disc, PPN, NPPN, DPP, Total
					{
						className: 'dt-left',
						targets: [4]
					} // Nama Barang
				]
			});

			// Reset form function
			window.resetForm = function() {
				window.location.href = '{{ route('rpenjualanbaru') }}';
			};

			// Export data function triggers DataTables export buttons
			window.exportData = function() {
				var table = $('#penjualanBaruTable').DataTable();
				if (table.data().count() === 0) {
					alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
					return;
				}
				var format = prompt(
					'Pilih format export:\n1. Excel\n2. CSV\n3. PDF\n4. Copy to Clipboard\n5. Print\nMasukkan nomor pilihan (1-5):');
				switch (format) {
					case '1':
						table.button('.buttons-excel').trigger();
						break;
					case '2':
						table.button('.buttons-csv').trigger();
						break;
					case '3':
						table.button('.buttons-pdf').trigger();
						break;
					case '4':
						table.button('.buttons-copy').trigger();
						break;
					case '5':
						table.button('.buttons-print').trigger();
						break;
					default:
						if (format !== null) alert('Pilihan tidak valid. Masukkan angka 1-5.');
				}
			};

			// Auto uppercase for supplier, sub item, kodec, kitir inputs
			$('#sup1, #sup2, #sub1, #sub2, #kodec1, #kodec2, #kitir1, #kitir2').on('input', function() {
				this.value = this.value.toUpperCase();
			});

			// Form validation before submit
			$('#penjualanBaruForm').on('submit', function(e) {
				var cbg = $('#cbg').val();
				if (!cbg) {
					alert('Harap pilih cabang terlebih dahulu.');
					$('#cbg').focus();
					e.preventDefault();
					return false;
				}
				// Validate periode format MM/YYYY if filled
				var periode = $('#periode').val();
				if (periode && !/^\d{2}\/\d{4}$/.test(periode)) {
					alert('Format periode tidak valid. Gunakan format MM/YYYY.');
					$('#periode').focus();
					e.preventDefault();
					return false;
				}
				// Validate date range if both filled
				var tgl1 = $('#tgl1').val();
				var tgl2 = $('#tgl2').val();
				if (tgl1 && tgl2 && tgl1 > tgl2) {
					alert('Tanggal Mulai tidak boleh lebih besar dari Tanggal Akhir.');
					$('#tgl1').focus();
					e.preventDefault();
					return false;
				}
				return true;
			});
		});
	</script>
@endsection
