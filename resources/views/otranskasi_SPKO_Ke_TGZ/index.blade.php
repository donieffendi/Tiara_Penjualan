@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">SPKO ke TGZ</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">SPKO ke TGZ</li>
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
							<div class="card-header">
								<div class="row">
									<div class="col-md-6">
										<a href="{{ route('tspkoketgz.edit', ['status' => 'simpan']) }}" class="btn btn-primary">
											<i class="fas fa-plus"></i> New
										</a>
									</div>
									<div class="col-md-6">
										<div class="form-group row mb-0">
											<label class="col-sm-3 col-form-label text-right">Cabang:</label>
											<div class="col-sm-6">
												<select class="form-control form-control-sm" id="filter-cabang">
													@foreach ($cabang as $cbg)
														<option value="{{ $cbg->KODE ?? ($cbg->kode ?? '') }}" {{ ($cbg->KODE ?? ($cbg->kode ?? '')) == session('cbg') ? 'selected' : '' }}>
															{{ $cbg->KODE ?? ($cbg->kode ?? '') }} - {{ $cbg->NA_TOKO ?? ($cbg->na_toko ?? ($cbg->nama ?? '')) }}
														</option>
													@endforeach
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card-body">
								<table id="datatable" class="table-bordered table-striped table-sm table">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="15%">No Bukti</th>
											<th width="12%">Tgl</th>
											<th width="10%">Kode</th>
											<th width="23%">Supplier</th>
											<th width="25%">Notes</th>
											<th width="10%">Action</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$(document).ready(function() {
			var table = $('#datatable').DataTable({
				processing: true,
				serverSide: true,
				pageLength: 25,
				ajax: {
					url: "{{ route('tspkoketgz.get-data') }}",
					data: function(d) {
						d.cbg = $('#filter-cabang').val();
					},
					error: function(xhr, error, thrown) {
						console.error('DataTables AJAX Error:', xhr.responseText);
						Swal.fire({
							icon: 'error',
							title: 'Error',
							html: '<div style="text-align: left;">' +
								'<p><strong>Gagal memuat data:</strong></p>' +
								'<p style="color: #dc3545; font-family: monospace; font-size: 12px;">' +
								(xhr.responseJSON?.error || xhr.responseJSON?.message || 'Terjadi kesalahan pada server') +
								'</p>' +
								'<p class="mt-2"><strong>Status:</strong> ' + xhr.status + '</p>' +
								'<p><strong>Saran:</strong></p>' +
								'<ul>' +
								'<li>Periksa apakah periode sudah diset</li>' +
								'<li>Periksa apakah database ' + $('#filter-cabang').val() + ' tersedia</li>' +
								'<li>Periksa log Laravel untuk detail error</li>' +
								'</ul>' +
								'</div>',
							confirmButtonText: 'OK',
							width: '600px'
						});
					}
				},
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false,
						className: 'text-center'
					},
					{
						data: 'NO_BUKTI',
						name: 'NO_BUKTI'
					},
					{
						data: 'TGL',
						name: 'TGL',
						className: 'text-center'
					},
					{
						data: 'KODES',
						name: 'KODES'
					},
					{
						data: 'NAMAS',
						name: 'NAMAS'
					},
					{
						data: 'NOTES',
						name: 'NOTES'
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false,
						className: 'text-center'
					}
				],
				order: [
					[1, 'desc']
				],
				language: {
					lengthMenu: "Show _MENU_",
					search: "Search:",
					zeroRecords: "No matching records found",
					info: "Showing _START_ to _END_ of _TOTAL_ entries",
					infoEmpty: "Showing 0 to 0 of 0 entries",
					infoFiltered: "(filtered from _MAX_ total entries)",
					paginate: {
						first: "First",
						last: "Last",
						next: "Next",
						previous: "Previous"
					}
				}
			});

			$('#filter-cabang').change(function() {
				table.ajax.reload();
			});

			$(document).on('click', '[data-action="print"]', function(e) {
				e.preventDefault();
				var noBukti = $(this).data('id');
				var cbg = $('#filter-cabang').val();

				$.ajax({
					url: "{{ route('tspkoketgz.print') }}",
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						no_bukti: noBukti,
						cbg: cbg
					},
					success: function(response) {
						if (response.data && response.data.length > 0) {
							var printWindow = window.open('', '_blank');
							printWindow.document.write('<html><head><title>Print SPKO</title>');
							printWindow.document.write(
								'<style>body{font-family:Arial;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #000;padding:5px;}</style>'
							);
							printWindow.document.write('</head><body>');
							printWindow.document.write('<h2>SPKO ke TGZ</h2>');
							printWindow.document.write(
								'<table><thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Kemasan</th><th>Qty</th></tr></thead><tbody>'
							);

							response.data.forEach(function(item, index) {
								printWindow.document.write('<tr><td>' + (index + 1) + '</td><td>' + item.KD_BRG +
									'</td><td>' + item.na_brg + '</td><td>' + item.ket_kem + '</td><td>' + item.qty +
									'</td></tr>');
							});

							printWindow.document.write('</tbody></table></body></html>');
							printWindow.document.close();
							printWindow.print();
						} else {
							Swal.fire('Info', 'Tidak ada data untuk dicetak', 'info');
						}
					},
					error: function(xhr) {
						Swal.fire('Error', 'Gagal memuat data print', 'error');
					}
				});
			});
		});

		function editData(noBukti, cbg) {
			window.location.href = "{{ route('tspkoketgz.edit') }}?status=edit&no_bukti=" + noBukti + "&cbg=" + cbg;
		}

		function printData(noBukti, cbg) {
			$('[data-action="print"][data-id="' + noBukti + '"]').click();
		}
	</script>
@endsection
