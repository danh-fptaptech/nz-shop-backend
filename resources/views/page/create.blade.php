<!DOCTYPE html>
<html lang="en">

<head>
    <title>Thêm bài viết mới</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container mt-3">
        <form action="{{ route('page.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            
            <div class="my-3">
                <label for="name">Tên trang:</label>
                <input type="text" class="form-control" id="name" placeholder="Nhập tên trang..."
                    name="name" value="{{ old('name') }}">
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="author">Tác giả:</label>
                <input type="text" class="form-control" id="author" placeholder="Nhập tác giả..."
                    name="author" value="{{ old('author') }}">
                @error('author')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="content">Nội dung:</label>
                <textarea name="content" id="content" class="form-control" value="">{{ old('content') }}</textarea>
                @error('content')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="d-flex mr-1">
                <button type="submit" class="btn btn-primary">Hoàn tất</button>
                <button type="reset" class="btn btn-outline-secondary mx-2">Hủy bỏ</button>
            </div>
        </form>
    </div>
</body>
</html>
