{{--
  Fixed frame around a BPS template. $bodyBefore/$bodyAfter are HTML built by
  App\Support\ReminderTemplate (escaped, one <p> per line, no indentation) —
  they must stay at column 0 so the Markdown pass leaves them alone.
--}}
<x-mail::message>
{!! $bodyBefore !!}

@if(filled($extraMessage))
<x-mail::panel>
{!! nl2br(e($extraMessage)) !!}
</x-mail::panel>
@endif

<x-mail::button :url="$url">
Isi Survei SIBSTR
</x-mail::button>

{!! $bodyAfter !!}

<x-mail::subcopy>
@if($isTest)
**Ini email uji coba** dari halaman Pengingat SIBSTR — tidak dikirim ke responden.<br>
@endif
Email ini dikirim otomatis ke alamat email yang tercantum pada Blok I kuesioner SIBSTR.
@if($replyToAddress)
Jika ada pertanyaan, Bapak/Ibu dapat membalas email ini.
@endif
Jika pengisian sudah diselesaikan, mohon abaikan email ini.
</x-mail::subcopy>
</x-mail::message>
