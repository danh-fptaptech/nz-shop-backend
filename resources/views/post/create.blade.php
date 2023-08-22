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
        <form action="{{ route('post.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="my-3">
                <label for="title">Tiêu đề:</label>
                <input type="text" class="form-control" id="title" placeholder="Nhập tiêu đề bài viết..."
                    name="title" value="{{ old('title') }}">
                @error('title')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="author">Tác giả:</label>
                <input type="text" class="form-control" id="author" placeholder="Nhập tên tác giả..."
                    name="author" value="{{ old('author') }}">
                @error('author')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="file">Hình ảnh:</label>
                <input type="file" class="form-control" id="image" name="image" value="{{ old('image') }}">
                @error('image')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="type">Loại tin tức:</label>
                <select name="type" id="type" class="form-control">
                    <option value="">Vui lòng lựa chọn</option>
                    <option value="Tin sản phẩm" <?php if (old('type') == 'Tin sản phẩm') {
                        echo 'selected';
                    } ?>>Tin sản phẩm</option>
                    <option value="Tin thị trường" <?php if (old('type') == 'Tin thị trường') {
                        echo 'selected';
                    } ?>>Tin thị trường</option>
                </select>
                @error('type')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- <div class="my-3">
                <label for="type">Loại tin tức</label>
                <input name="type" id="type" class="form-control" />
                @error('type')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div> --}}

            <div class="my-3">
                <label for="content">Nội dung:</label>
                <textarea name="content" id="content" class="form-control">{{ old('content') }}</textarea>
                @error('content')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="d-flex mr-1">
                <button type="submit" class="btn btn-primary">Đăng bài</button>
                <button type="reset" class="btn btn-outline-secondary mx-2">Hủy bỏ</button>
            </div>
        </form>
    </div>

</body>

</html>
