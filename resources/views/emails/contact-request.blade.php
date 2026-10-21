<h2>New contact request</h2>
<p><strong>Name:</strong> {{ $data['name'] }}</p>
<p><strong>Email:</strong> {{ $data['email'] }}</p>
@if (!empty($data['phone']))<p><strong>Phone:</strong> {{ $data['phone'] }}</p>@endif
<p><strong>Topic:</strong> {{ $data['topic'] }}</p>
@if (!empty($data['order_number']))<p><strong>Order number:</strong> {{ $data['order_number'] }}</p>@endif
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($data['message'])) !!}</p>
