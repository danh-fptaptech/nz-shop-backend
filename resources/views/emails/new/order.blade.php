@component('mail::message')
# Cảm ơn bạn!

Cảm ơn bạn đã tin tưởng lựa chọn cửa hàng chúng tôi.<br>
Thông tin chi tiết đơn hàng được nêu rõ trong liên kết bên dưới:

@component('mail::button', ['url' => $linkKey, 'color' => 'red'])
Kiểm tra đơn hàng
@endcomponent

Vui lòng thanh toán để có thể trải nghiệm sản phẩm trong thời gian sớm nhất.

Trân trọng,
{{ config('app.name') }}
@endcomponent
