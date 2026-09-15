<aside class="w-full shrink-0 border-b border-gray-200 bg-white lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r">
    <div class="flex items-center justify-between px-5 py-5 lg:block">
        <a href="{{ route('admin.dashboard') }}" class="text-xl font-black tracking-tight">KIPANYA <span class="text-emerald-600">CONTROL</span></a>
    </div>
    <nav class="overflow-x-auto px-3 pb-3 lg:overflow-visible lg:px-4">
        <p class="px-2 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">Apps</p>
        <div class="grid min-w-[680px] grid-cols-5 gap-1 lg:block lg:min-w-0">
            @foreach ([['cartoon','Cartoon','Content'],['wear','Kipanya Wear','Commerce'],['book','Kipanya Book','Books'],['motors','Kaypee Motors','Motors'],['tv','Kipanya TV','TV']] as [$slug,$label,$meta])
                <a href="{{ route('admin.module', $slug) }}" class="group rounded-xl px-3 py-3 hover:bg-gray-50 lg:mb-1">
                    <span class="block text-sm font-semibold group-hover:text-emerald-700">{{ $label }}</span>
                    <span class="text-xs text-gray-400">{{ $meta }}</span>
                </a>
            @endforeach
        </div>
        <p class="mt-5 px-2 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">Operations</p>
        <div class="space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="block rounded-xl bg-gray-950 px-3 py-3 text-sm font-semibold text-white">Dashboard</a>
            @if(auth()->user()->hasPermission('users.manage'))<span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Users</span>@endif
            @if(auth()->user()->hasPermission('analytics.view'))<span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Analytics</span>@endif
            @if(auth()->user()->hasPermission('settings.manage'))<span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Settings</span>@endif
            @if(auth()->user()->hasPermission('admin.dashboard.view'))<span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Audit Log</span>@endif
        </div>
    </nav>
</aside>
