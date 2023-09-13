@component('mail::message')
# Chào bạn ,

Dưới đây là thông tin tài khoản mới:

- Tên đăng nhập: {{ $email }}
- Mật khẩu: {{ $password }}

Hãy đăng nhập vào hệ thống bằng thông tin trên.

Cảm ơn,
{{ config('app.name') }}
@endcomponent
