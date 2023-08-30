<!DOCTYPE html>
<html lang="en">

<head>
    <title>Cập nhật bài viết</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <div class="container mt-3">
        <form action="{{ route('post.update', $post) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="my-3">
                <label for="title">Tiêu đề:</label>
                <input type="text" class="form-control" id="title" placeholder="Nhập tiêu đề bài viết..."
                    name="title" value="{{ $post->title }}">
                @error('title')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="author">Tác giả:</label>
                <input type="text" class="form-control" id="author" placeholder="Nhập tên tác giả..."
                    name="author" value="{{ $post->author }}">
                @error('author')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="file">Ảnh hiện tại:</label>
                <img src="{{ asset('images/' . $post->image) }}" alt="" width="100" class="img-thumbnail">
            </div>

            <div class="my-3">
                <label for="file">Upload ảnh mới:</label>
                <input type="file" class="form-control" id="image" name="image" value="{{ old('image') }}">
                @error('image')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="type">Loại tin tức:</label>
                <select name="type" id="type" class="form-control">{{ $post->type }}
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

            <div class="my-3">
                <label for="content">Nội dung:</label>
                <textarea name="content" id="content" class="form-control">{{ $post->content }}</textarea>
                @error('content')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="d-flex mr-1">
                <button type="submit" class="btn btn-primary">Hoàn tất</button>
                <button type="submit" href="{{ route('post.index') }}" class="btn btn-outline-secondary mx-2">Hủy
                    bỏ</button>
            </div>
        </form>
    </div>

</body>

</html>
