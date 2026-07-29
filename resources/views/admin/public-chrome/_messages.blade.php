@if(session('status'))<x-admin.alert type="success" title="Saved">{{ session('status') }}</x-admin.alert>@endif
<x-admin.validation-summary :errors="$errors->all()" />
