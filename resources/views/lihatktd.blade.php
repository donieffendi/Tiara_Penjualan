<!DOCTYPE html>
<html>
<head>
    <title>Folder: {{ $folder }}</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">Folder: <strong>{{ $folder }}</strong></h3>

    @if(session('status'))
        <div class="alert alert-danger">{{ session('status') }}</div>
    @endif

    @if(count($files) > 0)
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($files as $index => $file)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $file }}</td>
                        <td>
                            <a href="{{ route('download.file', [$folder, $file]) }}" class="btn btn-sm btn-warning">
                                Download
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-info">Folder ini kosong.</div>
    @endif
</div>

<!-- Bootstrap JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>