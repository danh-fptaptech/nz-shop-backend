<!DOCTYPE html>
<html lang="en">

<head>
    <title>Cập nhật Slider</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container mt-3">
        <h2>Create Form</h2>
        <form action="{{ route('slider.update', $slider) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('put')

            <div class="my-3">
                <label for="name">Tên:</label>
                <input type="text" class="form-control" id="name" placeholder="Nhập tên..." name="name"
                    value="{{ $slider->name }}">
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="title">Tiêu đề:</label>
                <input type="text" class="form-control" id="title" placeholder="Nhập tiêu đề..." name="title"
                    value="{{ $slider->title }}">
                @error('title')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="file">Ảnh hiện tại:</label>
                <img src="{{ asset('images/' . $slider->image) }}" alt="" width="100" class="img-thumbnail">
            </div>

            <div class="my-3">
                <label for="file">Upload ảnh mới:</label>
                <input type="file" class="form-control" id="image" name="image" value="{{ old('image') }}">
                @error('image')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3">
                <label for="date_created">Ngày tạo:</label>
                <input type="date" name="date_created" id="date_created" class="form-control"
                    value="{{ $slider->date_created }}">
                @error('date_created')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="my-3 mt-3">
                <label for="status">Trạng thái:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Vui lòng lựa chọn</option>
                    <option value="Hoạt động" <?php if (old('status') == 'Hoạt động') echo 'selected';?>>Hoạt động</option>
                    <option value="Tạm dừng" <?php if (old('status') == 'Tạm dừng')  echo 'selected';?>>Tạm dừng</option>
                </select>
                @error('status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="d-flex mr-1">
                <button type="submit" class="btn btn-outline-primary">Hoàn tất</button>
                <button type="submit" href="{{route('slider.index')}}" class="btn btn-outline-secondary mx-2">Hủy bỏ</button>
            </div>
        </form>
    </div>
</body>

</html>
