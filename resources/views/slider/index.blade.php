<!DOCTYPE html>
<html lang="en">

<head>
    <title>Danh sách slider</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="text-uppercase">Danh sách slider</h5>
            <a href="{{ route('slider.create') }}" class="btn btn-outline-primary">Thêm mới</a>
        </div>
        
        @if (session('success'))
            <div class="alert alert-success my-3">
                <strong>Success! </strong>{{ session('success') }}
            </div>
        @endif

        <table class="table table-hover my-3">
            <thead align="center">
                <tr>
                    {{-- <th>ID</th> --}}
                    <th>Tên</th>
                    <th>Tiêu đề</th>
                    <th>Hình ảnh</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th>Chức năng</th>
                </tr>
            </thead>
            <tbody align="center">
                @foreach ($sliders as $item)
                    <tr>
                        {{-- <td>{{ $item->id }}</td> --}}
                        <td class="text-uppercase">{{ $item->name }}</td>
                        <td>{{ $item->title }}</td>
                        <td>
                          <img width="100" src="{{ asset('images/' .$item->image) }}" alt="slider"
                            class="img-thumbnail" />
                        </td>
                        <td>{{ $item->date_created }}</td>
                        <td>{{ $item->status }}</td>
                        <td>
                            <a href="{{ route('slider.edit', $item->id) }}" class="btn btn-outline-warning">
                                Edit</a>
                            <a href="{{ route('slider.delete', $item->id) }}" class="btn btn-outline-danger">
                                Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
