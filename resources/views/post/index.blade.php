<!DOCTYPE html>
<html lang="en">

<head>
    <title>Danh sách bài viết</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <b class="text-uppercase">Danh sách bài viết</b>
            <a href="{{ route('post.create') }}" class="btn btn-outline-primary">Thêm mới</a>
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
                    <th>Tiêu đề</th>
                    <th style="width: 10%">Tác giả</th>
                    <th style="width: 10%">Hình ảnh</th>
                    <th style="width: 15%">Ngày tạo</th>
                    <th style="width: 15%">Loại tin tức</th>
                    <th style="width: 15%">Chức năng</th>
                </tr>
            </thead>
            <tbody align="center">
                @foreach ($posts as $item)
                    <tr>
                        {{-- <td>{{ $item->id }}</td> --}}
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->author }}</td>
                        <td>
                            <img width="100" src="{{ asset('images/' .$item->image) }}" alt="image" class="img-thumbnail" />
                        </td>
                        <td>{{ $item->created_at->format('d M Y') }}</td>
                        <td>{{ $item->type }}</td>
                        <td>
                            <a href="{{ route('post.edit', $item->id) }}" class="btn btn-outline-warning">
                                Edit</a>
                            <a href="{{ route('post.delete', $item->id) }}" class="btn btn-outline-danger"
                                onclick="return confirm('Bạn thật sự muốn xóa ?')">
                                Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
