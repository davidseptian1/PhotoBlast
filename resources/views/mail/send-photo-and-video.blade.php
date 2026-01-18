<x-mail::message>

<p>Terima kasih telah memberikan pose terbaik anda dengan menggunakan Photo Studio Chika.</p>
<p>Nyalakan semangatmu dengan memberikan kenangan berharga disini.</p>
<p>Akses link berikut agar kamu dapat melihat rekaman kenangan yang kamu dapatkan dari Photo Studio Chika</p>
<a href="{{ route('video', ['code' => $redeemCode]) }}">VIDEO URL</a>
<br><br><br>

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
