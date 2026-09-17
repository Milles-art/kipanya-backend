<div class="space-y-6">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2"><label class="text-sm font-bold text-gray-800">Full name</label><input name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-100"></div>
        <div><label class="text-sm font-bold text-gray-800">Phone</label><input name="phone" value="{{ old('phone', $user->phone ?? '') }}" required placeholder="+2557..." class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-100"></div>
        <div><label class="text-sm font-bold text-gray-800">Email <span class="font-normal text-gray-400">(optional)</span></label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-100"></div>
        <div><label class="text-sm font-bold text-gray-800">Role</label><select name="role_id" required class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            @foreach($roles as $role)<option value="{{ $role->id }}" @selected((string) old('role_id', $user->roles->first()?->id ?? '') === (string) $role->id)>{{ $role->name }}</option>@endforeach
        </select><p class="mt-2 text-xs leading-5 text-gray-400">Permissions are inherited from the selected role.</p></div>
        <div><label class="text-sm font-bold text-gray-800">Status</label><select name="status" required class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $user->status ?? '') === 'inactive')>Inactive</option>
        </select></div>
    </div>
</div>
