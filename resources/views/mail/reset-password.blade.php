<x-mail::message>
# Hello {{ $name }},

Someone asked to set a new password for your KP Wear account. If that was you, use the button below.

<x-mail::button :url="$url">
Set a new password
</x-mail::button>

The link works once and expires in {{ $ttlMinutes }} minutes. If you didn't request it, just ignore this email — your current password stays unchanged.

Thanks,<br>
Team KP Wear
</x-mail::message>
