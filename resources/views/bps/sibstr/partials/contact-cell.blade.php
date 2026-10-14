{{--
  Blok I contact (105 Penghubung, 104 Telepon/Fax, 106 Email) as a list cell.
  Shared by the BPS and Mitra SIBSTR lists. These are the company's contact
  answers, not the respondent account's email shown in the Pengguna column.

  Expects: $response; the host page styles a.contact-link.
--}}
@if($response->penghubung || $response->telepon_fax || $response->email)
    <div class="font-medium text-gray-900 dark:text-white">{{ $response->penghubung ?: '-' }}</div>
    @if($response->telepon_fax)
    <div class="text-xs mt-1"><span class="font-medium">Telp/HP:</span> {{ $response->telepon_fax }}</div>
    @endif
    @if($response->email)
    <div class="text-xs break-all"><span class="font-medium">Email:</span> <a href="mailto:{{ $response->email }}" class="contact-link">{{ $response->email }}</a></div>
    @endif
@else
    <span class="text-gray-400">-</span>
@endif
