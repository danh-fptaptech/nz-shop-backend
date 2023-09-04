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
        <form action="{{ route('page.update', $page) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="my-3">
                <label for="page">Tiêu đề:</label>
                <input type="text" class="form-control" id="page" placeholder="Nhập tên trang..." name="page"
                    value="{{ $page->name }}">
                @error('page')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="author">Tác giả:</label>
                <input type="text" class="form-control" id="author" placeholder="Nhập tác giả..." name="author"
                    value="{{ $page->author }}">
                @error('author')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="content">Nội dung:</label>
                <textarea name="content" id="content" class="form-control">{{ $page->content }}</textarea>
                @error('content')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="d-flex mr-1">
                <button type="submit" class="btn btn-primary">Hoàn tất</button>
                <button type="submit" href="{{route('page.index')}}" class="btn btn-outline-secondary mx-2">Hủy bỏ</button>
            </div>
        </form>
    </div>
</body>

</html>
