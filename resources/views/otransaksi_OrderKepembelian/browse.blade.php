<!DOCTYPE html>
<html>

<head>
	<title>Browse Supplier</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
</head>

<body>
	<div class="container-fluid mt-3">
		<h5>Pilih Supplier</h5>
		<hr>
		<table id="table-supplier" class="table-bordered table-striped table-sm table">
			<thead>
				<tr>
					<th width="15%">Kode</th>
					<th width="35%">Nama</th>
					<th width="30%">Alamat</th>
					<th width="15%">Kota</th>
					<th width="5%">Pilih</th>
				</tr>
			</thead>
		</table>
	</div>

	<script>
		$(document).ready(function() {
			$('#table-supplier').DataTable({
				processing: true,
				serverSide: false,
				ajax: {
					url: '/TOrderKepembelian/{{ request()->segment(2) }}/browse',
					type: 'GET'
				},
				columns: [{
						data: 'KODES',
						name: 'KODES'
					},
					{
						data: 'NAMAS',
						name: 'NAMAS'
					},
					{
						data: 'ALAMAT',
						name: 'ALAMAT'
					},
					{
						data: 'KOTA',
						name: 'KOTA'
					},
					{
						data: null,
						orderable: false,
						searchable: false,
						render: function(data, type, row) {
							return '<button class="btn btn-sm btn-primary btn-pilih" data-kodes="' + row.KODES + '" data-namas="' +
								row.NAMAS + '">Pilih</button>';
						}
					}
				],
				pageLength: 10
			});

			$(document).on('click', '.btn-pilih', function() {
				let kodes = $(this).data('kodes');
				let namas = $(this).data('namas');

				if (window.opener && !window.opener.closed) {
					window.opener.pilihSupplier(kodes, namas);
					window.close();
				}
			});
		});
	</script>
</body>

</html>
