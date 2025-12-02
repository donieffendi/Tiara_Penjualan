@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">{{ $title }}</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="/">Home</a></li>
							<li class="breadcrumb-item active">{{ $title }}</li>
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
								<a href="{{ route('TOrderKepembelian.edit', ['jns_trans' => $jns_trans, 'idx' => '', 'tipx' => 'new']) }}" class="btn btn-primary">
									<i class="fas fa-plus"></i> New
								</a>
							</div>
							<div class="card-body">
								<table id="datatable" class="table-bordered table-striped table-sm table">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="15%">No Bukti</th>
											<th width="12%">Tgl</th>
											<th width="12%">Total Qty</th>
											<th width="35%">Notes</th>
											<th width="8%">PBL</th>
											<th width="13%">Action</th>
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
				ajax: "{{ route('get-TOrderKepembelian', ['jns_trans' => $jns_trans]) }}",
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
						data: 'POSTED',
						name: 'POSTED',
						className: 'text-center',
						render: function(data) {
							return data == 1 ? '<span class="badge badge-success">Y</span>' :
								'<span class="badge badge-secondary">N</span>';
						}
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

			$(document).on('click', '.btn-delete', function(e) {
				e.preventDefault();
				var id = $(this).data('id');
				var jns = $(this).data('jns');

				Swal.fire({
					title: 'Apakah Anda yakin?',
					text: "Data akan dihapus permanen!",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Hapus!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						window.location.href = '/TOrderKepembelian/' + jns + '/delete/' + id;
					}
				});
			});

			$(document).on('click', '.btn-posting', function(e) {
				e.preventDefault();
				var id = $(this).data('id');
				var jns = $(this).data('jns');

				Swal.fire({
					title: 'Posting Data?',
					text: "Data akan diposting ke SPO!",
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Posting!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: '/TOrderKepembelian/' + jns + '/posting',
							type: 'POST',
							data: {
								_token: '{{ csrf_token() }}',
								no_bukti: id
							},
							success: function(response) {
								Swal.fire('Berhasil!', response.success, 'success');
								table.ajax.reload();
							},
							error: function(xhr) {
								Swal.fire('Error!', xhr.responseJSON.error, 'error');
							}
						});
					}
				});
			});
		});
	</script>
@endsection
