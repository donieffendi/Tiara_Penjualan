@extends('layouts.plain')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $pageTitle }}</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">{{ $pageTitle }}</li>
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
										<a href="{{ route('tpengembaliankegudang.edit', ['tipe' => $tipe, 'status' => 'simpan']) }}" class="btn btn-primary">
											<i class="fas fa-plus"></i> New
										</a>
										<button type="button" class="btn btn-success ml-1" id="btn-print-multiple">
											<i class="fas fa-print"></i> Print Multiple
										</button>
										<button type="button" class="btn btn-info ml-1" id="btn-refresh">
											<i class="fas fa-sync"></i> Refresh
										</button>
									</div>
								</div>
							</div>
							<div class="card-body">
								<!-- Filter Section -->
								<div class="row mb-3">
									<div class="col-md-3">
										<label>No Bukti</label>
										<input type="text" class="form-control form-control-sm" id="filter_no_bukti" placeholder="Cari No Bukti">
									</div>
									<div class="col-md-3">
										<label>Notes</label>
										<input type="text" class="form-control form-control-sm" id="filter_notes" placeholder="Cari Notes">
									</div>
									<div class="col-md-3">
										<label>Periode</label>
										<input type="text" class="form-control form-control-sm" id="filter_per" placeholder="MM.YYYY" value="{{ $periode }}">
									</div>
									<div class="col-md-3">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-primary btn-sm btn-block" id="btn-filter">
											<i class="fas fa-search"></i> Filter
										</button>
									</div>
								</div>

								<table id="datatable" class="table-bordered table-striped table-sm table">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="20%">No Bukti</th>
											<th width="12%">Tgl</th>
											<th width="10%">Total Qty</th>
											<th width="23%">Notes</th>
											<th width="10%">Type</th>
											<th width="8%">Posted</th>
											<th width="7%">Print</th>
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
					url: "{{ route('tpengembaliankegudang.get-data', ['tipe' => $tipe]) }}",
					data: function(d) {
						d.no_bukti = $('#filter_no_bukti').val();
						d.notes = $('#filter_notes').val();
						d.per = $('#filter_per').val();
					},
					error: function(xhr, error, code) {
						console.log('DataTables Ajax Error:', xhr.responseText);
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Gagal memuat data: ' + (xhr.responseJSON?.message || error)
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
						data: 'TOTAL_QTY',
						name: 'TOTAL_QTY',
						className: 'text-right'
					},
					{
						data: 'NOTES',
						name: 'NOTES'
					},
					{
						data: 'TYPE',
						name: 'TYPE',
						className: 'text-center'
					},
					{
						data: 'POSTED',
						name: 'POSTED',
						className: 'text-center'
					},
					{
						data: 'print',
						name: 'print',
						orderable: false,
						searchable: false,
						className: 'text-center'
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

			// Filter button - reload data dengan filter aktif
			$('#btn-filter').click(function() {
				console.log('Filter clicked:', {
					no_bukti: $('#filter_no_bukti').val(),
					notes: $('#filter_notes').val(),
					per: $('#filter_per').val()
				});
				table.ajax.reload(null, false); // false = tetap di halaman yang sama
			});

			// Enter key filter
			$('#filter_no_bukti, #filter_notes, #filter_per').keypress(function(e) {
				if (e.which == 13) {
					e.preventDefault();
					$('#btn-filter').click();
				}
			});

			// Refresh button - reset filter dan reload
			$('#btn-refresh').click(function() {
				$('#filter_no_bukti').val('');
				$('#filter_notes').val('');
				$('#filter_per').val('{{ $periode }}');
				table.ajax.reload(null, true); // true = kembali ke halaman 1
			});

			// Handle checkbox print (spacebar toggle)
			$(document).on('click', '.chk-print', function() {
				if ($(this).is(':disabled')) return;

				var bukti = $(this).data('bukti');
				var com = $(this).data('com');
				var isChecked = $(this).is(':checked');
				var newValue = isChecked ? 1 : 0;
				var tableName = com === 'a' ? 'stockb' : 'stockbz';

				$.ajax({
					url: "{{ route('tpengembaliankegudang.update-print', ['tipe' => $tipe]) }}",
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						no_bukti: bukti,
						table: tableName,
						print: newValue
					},
					success: function(response) {
						if (!response.success) {
							Swal.fire('Error', response.message || 'Gagal update print flag', 'error');
						}
					},
					error: function() {
						Swal.fire('Error', 'Terjadi kesalahan saat update print flag', 'error');
					}
				});
			});

			// Print Multiple
			$('#btn-print-multiple').click(function() {
				var selectedCount = $('.chk-print:checked').length;

				if (selectedCount === 0) {
					Swal.fire('Peringatan', 'Tidak ada data yang dipilih untuk dicetak', 'warning');
					return;
				}

				Swal.fire({
					title: 'Print Multiple?',
					text: 'Akan mencetak ' + selectedCount + ' dokumen',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Print!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						var printPromises = [];

						$('.chk-print:checked').each(function() {
							var bukti = $(this).data('bukti');
							var posted = $(this).closest('tr').find('.badge-success').length > 0 ? 1 : 0;
							printPromises.push(printDataPromise(bukti, posted));
						});

						Promise.all(printPromises).then(function() {
							Swal.fire('Berhasil', 'Print Selesai !', 'success');
							table.ajax.reload();
						}).catch(function(error) {
							Swal.fire('Error', 'Terjadi kesalahan saat print', 'error');
						});
					}
				});
			});
		});

		function editData(noBukti) {
			window.location.href = "{{ route('tpengembaliankegudang.edit', ['tipe' => $tipe]) }}?status=edit&no_bukti=" + noBukti;
		}

		function printData(noBukti, posted) {
			$.ajax({
				url: "{{ route('tpengembaliankegudang.print', ['tipe' => $tipe]) }}",
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					no_bukti: noBukti,
					posted: posted
				},
				success: function(response) {
					if (response.data && response.data.length > 0) {
						generatePrintWindow(response.data, noBukti);
					} else {
						Swal.fire('Info', 'Tidak ada data untuk dicetak', 'info');
					}
				},
				error: function(xhr) {
					Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data print', 'error');
				}
			});
		}

		function printDataPromise(noBukti, posted) {
			return new Promise((resolve, reject) => {
				$.ajax({
					url: "{{ route('tpengembaliankegudang.print', ['tipe' => $tipe]) }}",
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}',
						no_bukti: noBukti,
						posted: posted
					},
					success: function(response) {
						if (response.data && response.data.length > 0) {
							generatePrintWindow(response.data, noBukti);
							resolve();
						} else {
							reject('No data');
						}
					},
					error: function() {
						reject('Error');
					}
				});
			});
		}

		function generatePrintWindow(data, noBukti) {
			var printWindow = window.open('', '_blank');
			var pageTitle = '{{ $pageTitle }}';
			var tipe = '{{ $tipe }}';

			printWindow.document.write('<html><head><title>Print Pengembalian ke Gudang - ' + noBukti + '</title>');
			printWindow.document.write(
				'<style>body{font-family:Arial;font-size:11px;} table{width:100%;border-collapse:collapse;margin-top:15px;} th,td{border:1px solid #000;padding:4px;text-align:left;font-size:10px;} th{background-color:#f0f0f0;font-weight:bold;} .text-center{text-align:center;} .text-right{text-align:right;} .header{margin-bottom:10px;} .header h3{margin:5px 0;} @media print { body{margin:0;padding:10px;} }</style>'
			);
			printWindow.document.write('</head><body>');
			printWindow.document.write('<div class="header">');
			printWindow.document.write('<h3>' + pageTitle + '</h3>');
			printWindow.document.write('<p><strong>Toko:</strong> ' + (data[0].nmtoko || '') + '</p>');
			printWindow.document.write('<p><strong>No. Bukti:</strong> ' + noBukti + '</p>');
			printWindow.document.write('<p><strong>Tanggal Cetak:</strong> ' + new Date().toLocaleString('id-ID') + '</p>');
			printWindow.document.write('</div>');
			printWindow.document.write(
				'<table><thead><tr><th width="3%">No</th><th width="8%">Sub</th><th width="6%">SPL</th><th width="8%">Supp</th><th width="10%">Kode</th><th width="8%">KD</th><th width="20%">Nama Barang</th><th width="10%">Ukuran</th><th width="10%">Kemasan</th><th width="7%" class="text-right">HJ</th><th width="5%" class="text-right">Qty</th><th>Ket</th><th width="8%">Status</th></tr></thead><tbody>'
			);

			var totalQty = 0;

			data.forEach(function(item, index) {
				totalQty += parseFloat(item.qty || 0);

				printWindow.document.write(
					'<tr>' +
					'<td class="text-center">' + (index + 1) + '</td>' +
					'<td>' + (item.SUB || '') + '</td>' +
					'<td class="text-center">' + (item.SPL || '') + '</td>' +
					'<td>' + (item.SUPP || '') + '</td>' +
					'<td>' + (item.KD_BRG || '') + '</td>' +
					'<td>' + (item.KD || '') + '</td>' +
					'<td>' + (item.NA_BRG || '') + '</td>' +
					'<td>' + (item.KET_UK || '') + '</td>' +
					'<td>' + (item.KET_KEM || '') + '</td>' +
					'<td class="text-right">' + parseFloat(item.HJ || 0).toFixed(0) + '</td>' +
					'<td class="text-right">' + parseFloat(item.qty || 0).toFixed(2) + '</td>' +
					'<td>' + (item.KET || '') + '</td>' +
					'<td class="text-center">' + (item.KETX || '') + '</td>' +
					'</tr>'
				);
			});

			printWindow.document.write(
				'<tr style="font-weight:bold;">' +
				'<td colspan="10" class="text-right">TOTAL:</td>' +
				'<td class="text-right">' + totalQty.toFixed(2) + '</td>' +
				'<td colspan="2"></td>' +
				'</tr>'
			);

			printWindow.document.write('</tbody></table>');
			printWindow.document.write('<div style="margin-top:30px;"><p style="font-size:9px;"><em>Form: ' + pageTitle + '</em></p></div>');
			printWindow.document.write('</body></html>');
			printWindow.document.close();

			// Auto print setelah load
			setTimeout(function() {
				printWindow.print();
			}, 250);
		}
	</script>
@endsection
