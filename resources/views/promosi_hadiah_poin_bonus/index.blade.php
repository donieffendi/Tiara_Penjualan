@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Poin Bonus</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Poin Bonus</li>
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
								<div class="table-responsive">
									<table class="table-striped table-bordered table" id="poinBonusTable">
										<thead>
											<tr>
												<th>No</th>
												<th>Card</th>
												<th>Member</th>
												<th>Minimal Belanja</th>
												<th>Max Poin</th>
												<th>Persen Poin</th>
												<th>Tgl Mulai</th>
												<th>Tgl Akhir</th>
												<th>Aktif</th>
												<th>Aksi</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Edit Poin Bonus</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<form id="formEdit">
					<div class="modal-body">
						<input type="hidden" id="NO_ID" name="NO_ID">

						<div class="form-group">
							<label>Card Type</label>
							<input type="text" class="form-control" id="TYPE" readonly>
						</div>

						<div class="form-group">
							<label>Member</label>
							<input type="text" class="form-control" id="KET" readonly>
						</div>

						<div class="form-group">
							<label>Minimal Belanja</label>
							<input type="number" class="form-control" id="MIN_BELANJA" name="MIN_BELANJA" required>
						</div>

						<div class="form-group">
							<label>Max Poin</label>
							<input type="number" class="form-control" id="MAX_POIN" name="MAX_POIN" required>
						</div>

						<div class="form-group">
							<label>Persen Poin (%)</label>
							<input type="number" class="form-control" id="PERSEN" name="PERSEN" required>
						</div>

						<div class="form-group">
							<label>Tanggal Mulai</label>
							<input type="date" class="form-control" id="TGL1" name="TGL1" required>
						</div>

						<div class="form-group">
							<label>Tanggal Akhir</label>
							<input type="date" class="form-control" id="TGL2" name="TGL2" required>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		var poinBonusTable;

		$(document).ready(function() {
			poinBonusTable = $('#poinBonusTable').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: "{{ route('get-phpoinbonus') }}",
					type: "GET"
				},
				columns: [{
						data: 'DT_RowIndex',
						name: 'DT_RowIndex',
						orderable: false,
						searchable: false
					},
					{
						data: 'TYPE',
						name: 'TYPE'
					},
					{
						data: 'KET',
						name: 'KET'
					},
					{
						data: 'MIN_BELANJA',
						name: 'MIN_BELANJA',
						className: 'text-right'
					},
					{
						data: 'MAX_POIN',
						name: 'MAX_POIN',
						className: 'text-right'
					},
					{
						data: 'PERSEN',
						name: 'PERSEN',
						className: 'text-center'
					},
					{
						data: 'TGL1',
						name: 'TGL1',
						className: 'text-center'
					},
					{
						data: 'TGL2',
						name: 'TGL2',
						className: 'text-center'
					},
					{
						data: 'CEK',
						name: 'CEK',
						className: 'text-center',
						render: function(data, type, row) {
							if (data == 'Aktif') {
								return '<span class="badge badge-success">Aktif</span>';
							} else {
								return '<span class="badge badge-secondary">Tidak Aktif</span>';
							}
						}
					},
					{
						data: 'action',
						name: 'action',
						orderable: false,
						searchable: false
					}
				],
				order: [
					[1, 'asc']
				]
			});

			$(document).on('click', '.btn-edit', function() {
				var id = $(this).data('id');

				$.ajax({
					url: "{{ route('phpoinbonus.edit') }}",
					method: 'GET',
					data: {
						id: id
					},
					success: function(response) {
						if (response.success) {
							$('#NO_ID').val(response.data.NO_ID);
							$('#TYPE').val(response.data.TYPE);
							$('#KET').val(response.data.KET);
							$('#MIN_BELANJA').val(response.data.MIN_BELANJA);
							$('#MAX_POIN').val(response.data.MAX_POIN);
							$('#PERSEN').val(response.data.PERSEN);
							$('#TGL1').val(response.data.TGL1);
							$('#TGL2').val(response.data.TGL2);
							$('#modalEdit').modal('show');
						} else {
							alert('Error: ' + response.message);
						}
					},
					error: function(xhr) {
						alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
					}
				});
			});

			$('#formEdit').on('submit', function(e) {
				e.preventDefault();

				var formData = {
					_token: '{{ csrf_token() }}',
					NO_ID: $('#NO_ID').val(),
					MIN_BELANJA: $('#MIN_BELANJA').val(),
					MAX_POIN: $('#MAX_POIN').val(),
					PERSEN: $('#PERSEN').val(),
					TGL1: $('#TGL1').val(),
					TGL2: $('#TGL2').val()
				};

				$.ajax({
					url: "{{ route('phpoinbonus.store') }}",
					method: 'POST',
					data: formData,
					success: function(response) {
						if (response.success) {
							alert(response.message);
							$('#modalEdit').modal('hide');
							poinBonusTable.ajax.reload();
						} else {
							alert('Error: ' + response.message);
						}
					},
					error: function(xhr) {
						alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
					}
				});
			});
		});
	</script>
@endsection
