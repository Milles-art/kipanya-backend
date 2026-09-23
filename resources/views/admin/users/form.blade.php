<div class="space-y-6">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2"><label class="text-sm font-bold text-black">Full name</label><input name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-2 w-full rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black"></div>
        <div><label class="text-sm font-bold text-black">Phone</label><input name="phone" value="{{ old('phone', $user->phone ?? '') }}" required placeholder="+2557..." class="mt-2 w-full rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black"></div>
        <div><label class="text-sm font-bold text-black">Email <span class="font-normal text-black">(optional)</span></label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="mt-2 w-full rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black"></div>
        <div><label class="text-sm font-bold text-black">Role</label><select name="role_id" required class="mt-2 w-full rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black">
            @foreach($roles as $role)<option value="{{ $role->id }}" @selected((string) old('role_id', $user->roles->first()?->id ?? '') === (string) $role->id)>{{ $role->name }}</option>@endforeach
        </select><p class="mt-2 text-xs leading-5 text-black">Permissions are inherited from the selected role.</p></div>
        <div><label class="text-sm font-bold text-black">Status</label><select name="status" required class="mt-2 w-full rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black">
            <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $user->status ?? '') === 'inactive')>Inactive</option>
        </select></div>
    </div>
</div>
