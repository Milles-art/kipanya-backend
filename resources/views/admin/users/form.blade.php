<div class="space-y-6">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="admin-name">Full name</label>
            <input id="admin-name" name="name" value="{{ old('name', $user->name ?? '') }}" required class="kp-admin-input">
        </div>

        <div>
            <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="admin-phone">Phone</label>
            <input id="admin-phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" required placeholder="+2557..." class="kp-admin-input">
        </div>

        <div>
            <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="admin-email">Email <span class="normal-case font-normal text-slate-500">(optional)</span></label>
            <input id="admin-email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="kp-admin-input">
        </div>

        <div>
            <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="admin-role">Role</label>
            <select id="admin-role" name="role_id" required class="kp-admin-input">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) old('role_id', $user->roles->first()?->id ?? '') === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-slate-400">Permissions are inherited from the selected role.</p>
        </div>

        <div>
            <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="admin-status">Status</label>
            <select id="admin-status" name="status" required class="kp-admin-input">
                <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $user->status ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="admin-password">Sign-in password @if($user->exists)<span class="normal-case font-normal text-slate-500">(leave blank to keep)</span>@endif</label>
            <input id="admin-password" type="password" name="password" @if(!$user->exists)required @endif autocomplete="new-password" minlength="10" class="kp-admin-input">
            <p class="mt-2 text-xs text-slate-400">Min 10 characters. Only a super administrator can set this; the account also needs an email address above.</p>
        </div>
    </div>
</div>
